<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Place an order with concurrency-safe row-level locking and atomic inventory decrements.
     *
     * @throws Exception
     */
    public function placeOrder(User $user, array $data): Order
    {
        $cart = $this->cartService->getCart();

        if ($cart->items->isEmpty()) {
            throw new Exception("Your shopping cart is empty.");
        }

        // 1. Resolve Shipping Address
        $shippingAddress = null;
        if (! empty($data['address_id'])) {
            $address = Address::where('user_id', $user->id)->findOrFail($data['address_id']);
            $shippingAddress = [
                'name' => $address->name,
                'phone' => $address->phone,
                'division' => $address->division,
                'district' => $address->district,
                'upazila' => $address->upazila,
                'address_line' => $address->address_line,
                'type' => $address->type,
            ];
        } elseif (! empty($data['shipping_address'])) {
            $shippingAddress = $data['shipping_address'];
        } else {
            throw new Exception("Please provide a valid delivery address.");
        }

        // 2. Resolve Billing Address
        $billingAddress = null;
        if (! empty($data['billing_address_same'])) {
            $billingAddress = $shippingAddress;
        } elseif (! empty($data['billing_address'])) {
            $billingAddress = $data['billing_address'];
        } else {
            $billingAddress = $shippingAddress;
        }

        // 3. Delivery Method & Shipping Fee
        $deliveryMethod = $data['delivery_method'] ?? 'standard';
        $cartSubtotal = $this->cartService->getSubtotal();
        $freeShippingThreshold = (float) (Setting::get('free_shipping_threshold', 1500.00));
        
        if ($deliveryMethod === 'express') {
            $shippingFee = 120.00;
        } else {
            $shippingFee = $cartSubtotal >= $freeShippingThreshold ? 0.00 : (float) (Setting::get('shipping_fee_standard', 60.00));
        }

        // 4. Coupon Calculation
        $couponCode = $data['coupon_code'] ?? session('applied_coupon');
        $discount = 0.00;
        $appliedCoupon = null;

        if ($couponCode) {
            $appliedCoupon = Coupon::where('code', $couponCode)->first();
            if ($appliedCoupon && $appliedCoupon->isValidFor($cartSubtotal)) {
                $discount = $appliedCoupon->calculateDiscount($cartSubtotal);
            } else {
                $couponCode = null;
            }
        }

        $grandTotal = max(0.00, ($cartSubtotal - $discount) + $shippingFee);
        $paymentMethod = $data['payment_method'] ?? 'cod';
        $customerNotes = $data['customer_notes'] ?? null;

        // 5. Atomic Transaction with lockForUpdate to prevent race-condition overselling
        return DB::transaction(function () use (
            $user,
            $cart,
            $shippingAddress,
            $billingAddress,
            $deliveryMethod,
            $cartSubtotal,
            $shippingFee,
            $discount,
            $grandTotal,
            $couponCode,
            $appliedCoupon,
            $paymentMethod,
            $customerNotes
        ) {
            // A. Concurrency Safety: Lock & Verify Stock for every cart item
            $itemsToProcess = [];

            foreach ($cart->items as $cartItem) {
                // Lock product row
                $lockedProduct = Product::where('id', $cartItem->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedProduct || ! $lockedProduct->is_active) {
                    throw new Exception("Product '{$cartItem->product->title}' is no longer available.");
                }

                $lockedVariant = null;
                $availableStock = (int) $lockedProduct->stock;
                $unitPrice = (float) $lockedProduct->effective_price;
                $sku = $lockedProduct->sku;
                $variantTitle = null;

                if ($cartItem->product_variant_id) {
                    $lockedVariant = ProductVariant::where('id', $cartItem->product_variant_id)
                        ->where('product_id', $lockedProduct->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $lockedVariant) {
                        throw new Exception("The selected variant for '{$lockedProduct->title}' is no longer available.");
                    }

                    $availableStock = (int) $lockedVariant->stock;
                    $unitPrice = (float) $lockedVariant->effective_price;
                    $sku = $lockedVariant->sku;
                    $variantTitle = $lockedVariant->title;
                }

                if ($availableStock < $cartItem->quantity) {
                    throw new Exception(
                        "Sorry! Only {$availableStock} unit(s) remaining for '{$lockedProduct->title}'" .
                        ($variantTitle ? " ({$variantTitle})" : "") .
                        ". Please adjust your cart quantity."
                    );
                }

                $itemsToProcess[] = [
                    'product' => $lockedProduct,
                    'variant' => $lockedVariant,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'sku' => $sku,
                    'product_title' => $lockedProduct->title,
                    'variant_title' => $variantTitle,
                    'item_total' => round($unitPrice * $cartItem->quantity, 2),
                ];
            }

            // B. Recompute actual subtotal from locked database prices for absolute financial integrity
            $verifiedSubtotal = array_sum(array_column($itemsToProcess, 'item_total'));
            $verifiedDiscount = 0.00;

            if ($appliedCoupon && $appliedCoupon->isValidFor($verifiedSubtotal)) {
                // Lock coupon row for concurrency safety
                $lockedCoupon = Coupon::where('id', $appliedCoupon->id)->lockForUpdate()->first();
                if ($lockedCoupon && $lockedCoupon->isValidFor($verifiedSubtotal)) {
                    $verifiedDiscount = $lockedCoupon->calculateDiscount($verifiedSubtotal);
                    $lockedCoupon->increment('used_count');
                }
            }

            $verifiedTotal = max(0.00, ($verifiedSubtotal - $verifiedDiscount) + $shippingFee);

            // C. Generate Unique Order Number
            $orderNumber = $this->generateOrderNumber();

            // D. Create Order Record
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => $verifiedSubtotal,
                'discount' => $verifiedDiscount,
                'shipping_fee' => $shippingFee,
                'total' => $verifiedTotal,
                'coupon_code' => $couponCode,
                'payment_method' => $paymentMethod,
                'payment_status' => 'unpaid',
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
                'customer_notes' => $customerNotes,
            ]);

            // E. Deduct inventory and insert Order Items
            foreach ($itemsToProcess as $item) {
                // Atomic conditional stock decrements
                if ($item['variant']) {
                    $item['variant']->decrement('stock', $item['quantity']);
                }
                $item['product']->decrement('stock', $item['quantity']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_variant_id' => $item['variant']?->id,
                    'product_title' => $item['product_title'],
                    'variant_title' => $item['variant_title'],
                    'sku' => $item['sku'],
                    'price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['item_total'],
                ]);
            }

            // F. Clear Cart and Session Coupon
            $this->cartService->clear();
            session()->forget('applied_coupon');

            return $order;
        });
    }

    /**
     * Generate an authentic Daraz-style unique order number: ORD-YYYYMMDD-XXXXXX
     */
    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
