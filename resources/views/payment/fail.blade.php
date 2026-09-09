@extends('layouts.storefront')

@section('title', 'Payment Failed - Daraz Online Shopping')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 text-center">
        <!-- Failure Icon -->
        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-black text-gray-900 mb-1">Payment Unsuccessful</h1>
        <p class="text-xs text-gray-500 mb-6 max-w-md mx-auto">
            Your payment attempt could not be processed by SSLCommerz or was declined by the issuing bank/wallet.
        </p>

        <!-- Transaction Details Box -->
        <div class="bg-red-50 border border-red-100 rounded-lg p-5 text-xs text-left space-y-3 mb-6">
            @if($order)
                <div class="flex justify-between pb-2 border-b border-red-100">
                    <span class="text-gray-500">Order Number:</span>
                    <span class="font-bold text-gray-900 font-mono">#{{ $order->order_number }}</span>
                </div>

                <div class="flex justify-between pb-2 border-b border-red-100">
                    <span class="text-gray-500">Amount Due:</span>
                    <span class="font-bold text-gray-900">৳{{ number_format($order->total) }}</span>
                </div>
            @endif

            <div class="flex justify-between pb-2 border-b border-red-100">
                <span class="text-gray-500">Gateway Status:</span>
                <span class="font-bold text-red-600 uppercase">FAILED</span>
            </div>

            <p class="text-[11px] text-gray-600 pt-1">
                Tip: Please ensure you have sufficient balance, or try paying with an alternate method such as <strong>Cash on Delivery (COD)</strong>.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
            @if($order)
                <a href="{{ route('payment.sslcommerz.simulator', $order->order_number) }}" 
                   class="w-full sm:w-auto px-8 py-3.5 bg-daraz hover:bg-daraz-hover text-white text-xs font-black uppercase tracking-wider rounded shadow transition-colors text-center">
                    Retry Payment
                </a>
            @endif
            <a href="{{ route('catalog.index') }}" 
               class="w-full sm:w-auto px-8 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold uppercase tracking-wider rounded transition-colors text-center">
                Return to Shop
            </a>
        </div>
    </div>

</div>
@endsection
