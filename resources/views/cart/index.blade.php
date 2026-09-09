@extends('layouts.storefront')

@section('title', 'Shopping Cart - Daraz Bangladesh')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Header & Item Counter -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <span>Shopping Cart</span>
            <span class="text-xs font-normal text-gray-500">({{ $cart->items->count() }} items)</span>
        </h1>
        @if($cart->items->count() > 0)
            <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Clear all items from your cart?');">
                @csrf
                <button type="submit" class="text-xs text-red-600 hover:underline">
                    Clear Entire Cart
                </button>
            </form>
        @endif
    </div>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-xs px-4 py-3 rounded-lg flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3 rounded-lg flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($cart->items->count() > 0)
        <!-- Two Column Layout (Items Left, Summary Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT: Cart Items Table (8 cols) -->
            <div class="lg:col-span-8 space-y-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b text-xs font-bold text-gray-600 grid grid-cols-12">
                        <span class="col-span-6">Item Description</span>
                        <span class="col-span-2 text-center">Unit Price</span>
                        <span class="col-span-2 text-center">Quantity</span>
                        <span class="col-span-2 text-right">Subtotal</span>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($cart->items as $item)
                            <div class="p-4 grid grid-cols-12 items-center gap-2 text-xs">
                                <!-- Product details -->
                                <div class="col-span-6 flex items-start space-x-3">
                                    <div class="w-16 h-16 rounded bg-gray-50 border border-gray-100 overflow-hidden shrink-0">
                                        @if($item->product->primaryImage && file_exists(public_path('storage/' . $item->product->primaryImage->image_path)))
                                            <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" alt="{{ $item->product->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-orange-50 text-daraz font-bold text-lg">
                                                {{ substr($item->product->title, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('catalog.show', $item->product->slug) }}" class="font-semibold text-gray-800 hover:text-daraz line-clamp-2 leading-tight">
                                            {{ $item->product->title }}
                                        </a>

                                        @if($item->variant)
                                            <div class="text-[11px] text-gray-500 mt-1 flex flex-wrap gap-1">
                                                <span class="bg-gray-100 px-1.5 py-0.5 rounded">
                                                    Variant:
                                                    @if(is_array($item->variant->attributes))
                                                        {{ implode(', ', $item->variant->attributes) }}
                                                    @else
                                                        {{ $item->variant->sku }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endif

                                        <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="mt-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[11px] text-gray-400 hover:text-red-600 flex items-center space-x-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Unit Price -->
                                <div class="col-span-2 text-center font-bold text-gray-800">
                                    ৳{{ number_format($item->price) }}
                                </div>

                                <!-- Quantity Stepper -->
                                <div class="col-span-2 flex justify-center">
                                    <div class="flex items-center border border-gray-300 rounded overflow-hidden">
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ $item->quantity - 1 }}">
                                            <button type="submit" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold transition-colors">
                                                -
                                            </button>
                                        </form>

                                        <span class="px-3 py-1 font-bold text-gray-900 bg-white">
                                            {{ $item->quantity }}
                                        </span>

                                        <form action="{{ route('cart.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                            <button type="submit" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold transition-colors">
                                                +
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Subtotal -->
                                <div class="col-span-2 text-right font-black text-daraz text-sm">
                                    ৳{{ number_format($item->subtotal()) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-gray-500 pt-2">
                    <a href="{{ route('catalog.index') }}" class="flex items-center font-bold text-daraz hover:underline">
                        &larr; Continue Shopping
                    </a>
                </div>
            </div>

            <!-- RIGHT: Order Summary Box (4 cols) -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 sticky top-4">
                    <h3 class="font-bold text-gray-900 text-sm pb-4 border-b border-gray-100 uppercase tracking-wider">
                        Order Summary
                    </h3>

                    <div class="py-4 space-y-3 text-xs border-b border-gray-100">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal ({{ $cart->totalItems() }} items)</span>
                            <span class="font-bold text-gray-900">৳{{ number_format($subtotal) }}</span>
                        </div>

                        <div class="flex justify-between text-gray-600">
                            <span>Shipping Fee</span>
                            @if($shipping > 0)
                                <span class="font-bold text-gray-900">৳{{ number_format($shipping) }}</span>
                            @else
                                <span class="font-bold text-green-600 uppercase">FREE</span>
                            @endif
                        </div>

                        @if($shipping > 0)
                            <p class="text-[11px] text-green-600 bg-green-50 p-2 rounded">
                                Add ৳{{ number_format(max(0, 1500 - $subtotal)) }} more to qualify for <strong>Free Shipping!</strong>
                            </p>
                        @endif
                    </div>

                    <!-- Total -->
                    <div class="py-4 flex justify-between items-baseline">
                        <span class="text-sm font-bold text-gray-800">Total</span>
                        <span class="text-2xl font-black text-daraz">
                            ৳{{ number_format($grandTotal) }}
                        </span>
                    </div>

                    <!-- Checkout Button -->
                    <a href="{{ url('/checkout') }}" class="block w-full py-3.5 bg-daraz hover:bg-daraz-hover text-white text-center font-black text-sm uppercase tracking-wider rounded shadow-md transition-colors">
                        Proceed to Checkout
                    </a>

                    <!-- Guarantee badges -->
                    <div class="mt-6 pt-4 border-t border-gray-100 space-y-2 text-[11px] text-gray-500">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Safe and Secure Payments</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>100% Authentic Products</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty Cart State -->
        <div class="bg-white rounded-lg p-12 text-center shadow-sm border border-gray-100 max-w-xl mx-auto my-8">
            <div class="w-20 h-20 mx-auto rounded-full bg-orange-50 text-daraz flex items-center justify-center mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">There are no items in this cart</h3>
            <p class="text-xs text-gray-500 mb-6">Explore our latest deals and add your favorite items to cart.</p>
            <a href="{{ route('catalog.index') }}" class="inline-block px-8 py-3 bg-daraz hover:bg-daraz-hover text-white text-xs font-extrabold uppercase tracking-wider rounded shadow transition-colors">
                Continue Shopping
            </a>
        </div>
    @endif
</div>
@endsection
