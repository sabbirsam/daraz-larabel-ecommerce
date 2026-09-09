<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(CartService $cartService): View
    {
        $cart = $cartService->getCart();
        $subtotal = $cartService->getSubtotal();
        $shipping = $cartService->getShippingEstimate();
        $grandTotal = $cartService->getGrandTotal();

        return view('cart.index', compact('cart', 'subtotal', 'shipping', 'grandTotal'));
    }

    public function add(Request $request, CartService $cartService): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = (int) $request->input('quantity', 1);
        $productId = (int) $request->input('product_id');
        $variantId = $request->filled('variant_id') ? (int) $request->input('variant_id') : null;

        try {
            $cartService->addItem($productId, $variantId, $quantity);

            if ($request->boolean('buy_now')) {
                return redirect()->route('cart.index')->with('success', 'Proceed to checkout with your selected item.');
            }

            return back()->with('success', 'Item successfully added to your cart!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, int $id, CartService $cartService): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $quantity = (int) $request->input('quantity');
        $cartService->updateQuantity($id, $quantity);

        return back()->with('success', 'Cart updated successfully.');
    }

    public function remove(int $id, CartService $cartService): RedirectResponse
    {
        $cartService->removeItem($id);

        return back()->with('success', 'Item removed from your cart.');
    }

    public function clear(CartService $cartService): RedirectResponse
    {
        $cartService->clear();

        return back()->with('success', 'Your cart has been cleared.');
    }
}
