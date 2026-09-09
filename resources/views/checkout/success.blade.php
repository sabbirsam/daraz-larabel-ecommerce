@extends('layouts.storefront')

@section('title', 'Order Confirmation - Daraz Online Shopping')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">

    <!-- Celebration Header Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 text-center mb-6">
        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-black text-gray-900 mb-2">Thank you! Your order has been placed.</h1>
        <p class="text-xs text-gray-500 max-w-md mx-auto">
            Order <span class="font-bold text-gray-900">#{{ $order->order_number }}</span> has been confirmed and is being prepared for dispatch.
        </p>

        <div class="mt-6 inline-flex items-center space-x-2 bg-orange-50 border border-orange-200 text-daraz px-4 py-2 rounded-full text-xs font-bold">
            <span>📦 Estimated Delivery:</span>
            <span>{{ now()->addDays(3)->format('D, M d') }} - {{ now()->addDays(5)->format('D, M d') }}</span>
        </div>
    </div>

    <!-- Order Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        
        <!-- Delivery Address Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold text-gray-900 text-xs uppercase tracking-wider pb-3 border-b border-gray-100 mb-3 flex items-center space-x-2">
                <svg class="w-4 h-4 text-daraz" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Delivery Address</span>
            </h2>

            <div class="text-xs space-y-1.5 text-gray-600">
                <div class="font-bold text-gray-900 text-sm">
                    {{ $order->shipping_address['name'] ?? 'Customer' }}
                </div>
                <div>
                    Phone: <span class="font-semibold text-gray-800">{{ $order->shipping_address['phone'] ?? 'N/A' }}</span>
                </div>
                <div class="leading-relaxed">
                    {{ $order->shipping_address['address_line'] ?? '' }}
                    @if(!empty($order->shipping_address['upazila'])), {{ $order->shipping_address['upazila'] }} @endif
                    @if(!empty($order->shipping_address['district'])), {{ $order->shipping_address['district'] }} @endif
                    @if(!empty($order->shipping_address['division'])), {{ $order->shipping_address['division'] }} @endif
                </div>
            </div>
        </div>

        <!-- Payment Details Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold text-gray-900 text-xs uppercase tracking-wider pb-3 border-b border-gray-100 mb-3 flex items-center space-x-2">
                <svg class="w-4 h-4 text-daraz" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <span>Payment Information</span>
            </h2>

            <div class="text-xs space-y-2 text-gray-600">
                <div class="flex justify-between">
                    <span>Payment Method:</span>
                    <span class="font-bold text-gray-900 uppercase">
                        {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online Payment (SSLCommerz)' }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span>Payment Status:</span>
                    @if($order->payment_status === 'paid')
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 font-bold rounded uppercase text-[10px]">
                            Paid
                        </span>
                    @else
                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 font-bold rounded uppercase text-[10px]">
                            Payment on Delivery
                        </span>
                    @endif
                </div>

                <div class="flex justify-between">
                    <span>Order Date:</span>
                    <span class="font-semibold text-gray-800">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Purchased Items Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="font-bold text-gray-900 text-xs uppercase tracking-wider pb-3 border-b border-gray-100 mb-4">
            Order Items ({{ $order->items->sum('quantity') }} items)
        </h2>

        <div class="divide-y divide-gray-100">
            @foreach($order->items as $item)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-50 rounded border border-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center">
                            @if($item->product && $item->product->primaryImage)
                                <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" 
                                     alt="{{ $item->product_title }}" 
                                     class="w-full h-full object-cover">
                            @else
                                <span class="text-[10px] text-gray-400 font-bold">DARAZ</span>
                            @endif
                        </div>

                        <div>
                            <div class="font-bold text-gray-900">{{ $item->product_title }}</div>
                            @if($item->variant_title)
                                <div class="text-[11px] text-gray-500">Variant: {{ $item->variant_title }}</div>
                            @endif
                            <div class="text-[11px] text-gray-500">SKU: {{ $item->sku }}</div>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-gray-500 text-[11px]">
                            {{ $item->quantity }} × ৳{{ number_format($item->price) }}
                        </div>
                        <div class="font-black text-gray-900 text-sm">
                            ৳{{ number_format($item->total) }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Total Breakdown -->
        <div class="mt-4 pt-4 border-t border-gray-100 max-w-xs ml-auto space-y-2 text-xs">
            <div class="flex justify-between text-gray-600">
                <span>Subtotal:</span>
                <span class="font-semibold text-gray-900">৳{{ number_format($order->subtotal) }}</span>
            </div>

            <div class="flex justify-between text-gray-600">
                <span>Shipping Fee:</span>
                <span class="font-semibold text-gray-900">
                    {{ $order->shipping_fee > 0 ? '৳' . number_format($order->shipping_fee) : 'FREE' }}
                </span>
            </div>

            @if($order->discount > 0)
                <div class="flex justify-between text-green-600 font-semibold">
                    <span>Discount ({{ $order->coupon_code }}):</span>
                    <span>-৳{{ number_format($order->discount) }}</span>
                </div>
            @endif

            <div class="flex justify-between text-base font-black text-gray-900 pt-2 border-t border-gray-100">
                <span>Total:</span>
                <span class="text-daraz text-lg">৳{{ number_format($order->total) }}</span>
            </div>
        </div>
    </div>

    <!-- Next Actions -->
    <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
        <a href="{{ route('catalog.index') }}" 
           class="w-full sm:w-auto px-8 py-3 bg-daraz hover:bg-daraz-hover text-white text-xs font-black uppercase tracking-wider rounded shadow transition-colors text-center">
            Continue Shopping
        </a>
        <a href="{{ route('dashboard') }}" 
           class="w-full sm:w-auto px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold uppercase tracking-wider rounded transition-colors text-center">
            Go to My Account
        </a>
    </div>

</div>
@endsection
