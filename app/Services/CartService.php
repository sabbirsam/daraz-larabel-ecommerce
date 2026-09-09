<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getCart(): Cart
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['session_id' => null]
            );
        } else {
            $sessionId = session()->getId();
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId, 'user_id' => null]
            );
        }

        $cart->load(['items.product.primaryImage', 'items.variant']);

        session(['cart_count' => $cart->items->sum('quantity')]);

        return $cart;
    }

    public function addItem(int $productId, ?int $variantId = null, int $quantity = 1): CartItem
    {
        $product = Product::active()->findOrFail($productId);
        $variant = null;
        $unitPrice = (float) $product->effective_price;
        $maxStock = (int) $product->stock;

        if ($variantId) {
            $variant = ProductVariant::where('product_id', $productId)->findOrFail($variantId);
            $unitPrice = (float) $variant->effective_price;
            $maxStock = (int) $variant->stock;
        }

        if ($maxStock <= 0) {
            throw new Exception("This product is currently out of stock.");
        }

        $cart = $this->getCart();

        return DB::transaction(function () use ($cart, $productId, $variantId, $quantity, $unitPrice, $maxStock) {
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->where('product_variant_id', $variantId)
                ->first();

            if ($cartItem) {
                $newQuantity = min($cartItem->quantity + $quantity, $maxStock);
                $cartItem->update([
                    'quantity' => $newQuantity,
                    'price' => $unitPrice,
                ]);
            } else {
                $quantityToAdd = min($quantity, $maxStock);
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'quantity' => $quantityToAdd,
                    'price' => $unitPrice,
                ]);
            }

            session(['cart_count' => $cart->items()->sum('quantity')]);

            return $cartItem;
        });
    }

    public function updateQuantity(int $cartItemId, int $quantity): bool
    {
        $cart = $this->getCart();
        $cartItem = $cart->items()->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $cartItem->delete();
            session(['cart_count' => $cart->items()->sum('quantity')]);
            return true;
        }

        $maxStock = $cartItem->variant ? (int) $cartItem->variant->stock : (int) $cartItem->product->stock;
        $newQty = min($quantity, $maxStock);

        $cartItem->update(['quantity' => $newQty]);

        session(['cart_count' => $cart->items()->sum('quantity')]);

        return true;
    }

    public function removeItem(int $cartItemId): bool
    {
        $cart = $this->getCart();
        $cartItem = $cart->items()->findOrFail($cartItemId);
        $cartItem->delete();

        session(['cart_count' => $cart->items()->sum('quantity')]);

        return true;
    }

    public function clear(): void
    {
        $cart = $this->getCart();
        $cart->items()->delete();
        session(['cart_count' => 0]);
    }

    public function mergeGuestCart(User $user, string $sessionId): void
    {
        $guestCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
        if (! $guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['session_id' => null]
        );

        DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $item) {
                $existingItem = CartItem::where('cart_id', $userCart->id)
                    ->where('product_id', $item->product_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->first();

                if ($existingItem) {
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + $item->quantity,
                        'price' => $item->price,
                    ]);
                } else {
                    $item->update(['cart_id' => $userCart->id]);
                }
            }

            $guestCart->delete();
        });

        session(['cart_count' => $userCart->items()->sum('quantity')]);
    }

    public function getSubtotal(): float
    {
        return $this->getCart()->total();
    }

    public function getShippingEstimate(): float
    {
        $subtotal = $this->getSubtotal();
        if ($subtotal <= 0) {
            return 0.00;
        }

        // Free shipping on orders over ৳1500
        return $subtotal >= 1500 ? 0.00 : 60.00;
    }

    public function getGrandTotal(): float
    {
        return $this->getSubtotal() + $this->getShippingEstimate();
    }
}
