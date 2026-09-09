<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\Payment\PaymentManager;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
        protected PaymentManager $paymentManager
    ) {}

    public function index(): View|RedirectResponse
    {
        $cart = $this->cartService->getCart();

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty. Please add products before checking out.');
        }

        $user = Auth::user();

        // Mobile Phone Verification Gate
        if ($user->phone && ! $user->phone_verified_at) {
            return redirect()->route('verification.phone')
                ->with('warning', 'Please verify your mobile phone number before proceeding to checkout.');
        }

        $addresses = $user->addresses()->orderByDesc('is_default_shipping')->get();
        $selectedAddress = $addresses->firstWhere('is_default_shipping', true) ?? $addresses->first();

        $subtotal = $this->cartService->getSubtotal();
        $freeShippingThreshold = (float) Setting::get('free_shipping_threshold', 1500.00);
        $standardShippingFee = $subtotal >= $freeShippingThreshold ? 0.00 : (float) Setting::get('shipping_fee_standard', 60.00);
        $expressShippingFee = 120.00;

        // Coupon verification
        $appliedCoupon = null;
        $discount = 0.00;
        $couponCode = session('applied_coupon');

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->isValidFor($subtotal)) {
                $appliedCoupon = $coupon;
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                session()->forget('applied_coupon');
                $couponCode = null;
            }
        }

        $grandTotal = max(0.00, ($subtotal - $discount) + $standardShippingFee);
        $divisions = config('bangladesh.divisions', []);

        return view('checkout.index', compact(
            'cart',
            'user',
            'addresses',
            'selectedAddress',
            'subtotal',
            'standardShippingFee',
            'expressShippingFee',
            'appliedCoupon',
            'discount',
            'grandTotal',
            'divisions'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'name' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'phone' => ['required_without:address_id', 'nullable', 'string', 'max:20'],
            'division' => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'district' => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'upazila' => ['nullable', 'string', 'max:100'],
            'address_line' => ['required_without:address_id', 'nullable', 'string', 'max:500'],
            'type' => ['nullable', 'in:home,office'],
            'save_address' => ['nullable', 'boolean'],
            'delivery_method' => ['required', 'in:standard,express'],
            'payment_method' => ['required', 'in:cod,sslcommerz'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        // Mobile Phone Verification Gate
        if ($user->phone && ! $user->phone_verified_at) {
            return redirect()->route('verification.phone')
                ->with('warning', 'Please verify your mobile phone number before placing an order.');
        }

        $addressId = $request->input('address_id');

        // If user submitted new inline address
        if (! $addressId && $request->filled('name')) {
            $isFirst = $user->addresses()->count() === 0;
            $newAddress = $user->addresses()->create([
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'division' => $request->input('division'),
                'district' => $request->input('district'),
                'upazila' => $request->input('upazila'),
                'address_line' => $request->input('address_line'),
                'type' => $request->input('type', 'home'),
                'is_default_shipping' => $isFirst,
                'is_default_billing' => $isFirst,
            ]);
            $addressId = $newAddress->id;
        }

        try {
            $paymentMethod = $request->input('payment_method');

            $order = $this->orderService->placeOrder($user, [
                'address_id' => $addressId,
                'delivery_method' => $request->input('delivery_method'),
                'payment_method' => $paymentMethod,
                'customer_notes' => $request->input('customer_notes'),
                'billing_address_same' => true,
            ]);

            if ($paymentMethod === 'sslcommerz') {
                $gateway = $this->paymentManager->resolve('sslcommerz');
                $init = $gateway->initiate($order);

                Payment::create([
                    'order_id' => $order->id,
                    'gateway' => 'sslcommerz',
                    'transaction_id' => $init['tran_id'],
                    'amount' => $order->total,
                    'currency' => 'BDT',
                    'status' => 'initiated',
                    'payload' => $init['payload'] ?? null,
                ]);

                return redirect()->away($init['redirect_url']);
            }

            // Cash on Delivery (COD) payment tracking
            Payment::create([
                'order_id' => $order->id,
                'gateway' => 'cod',
                'transaction_id' => null,
                'amount' => $order->total,
                'currency' => 'BDT',
                'status' => 'pending',
            ]);

            return redirect()->route('checkout.success', $order->order_number)
                ->with('success', 'Order #' . $order->order_number . ' placed successfully!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
        ]);

        $code = strtoupper(trim($request->input('coupon_code')));
        $subtotal = $this->cartService->getSubtotal();

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon || ! $coupon->is_active) {
            return back()->with('coupon_error', 'Invalid voucher code or voucher has expired.');
        }

        if (! $coupon->isValidFor($subtotal)) {
            if ($coupon->min_spend && $subtotal < $coupon->min_spend) {
                return back()->with('coupon_error', 'Minimum spend of ৳' . number_format($coupon->min_spend) . ' required to use this voucher.');
            }
            return back()->with('coupon_error', 'This voucher is no longer valid or has reached its usage limit.');
        }

        session(['applied_coupon' => $coupon->code]);
        $discount = $coupon->calculateDiscount($subtotal);

        return back()->with('coupon_success', "Voucher '{$coupon->code}' applied successfully! Discount: ৳" . number_format($discount));
    }

    public function removeCoupon(): RedirectResponse
    {
        session()->forget('applied_coupon');
        return back()->with('coupon_success', 'Voucher removed successfully.');
    }

    public function success(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with(['items.product.primaryImage', 'items.variant'])
            ->firstOrFail();

        return view('checkout.success', compact('order'));
    }
}
