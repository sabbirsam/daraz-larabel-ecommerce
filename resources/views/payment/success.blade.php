@extends('layouts.storefront')

@section('title', 'Payment Successful - Daraz Online Shopping')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 text-center">
        <!-- Success Icon -->
        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-black text-gray-900 mb-1">Payment Successful!</h1>
        <p class="text-xs text-gray-500 mb-6">
            Your transaction has been approved and processed through SSLCommerz.
        </p>

        <!-- Transaction Details Receipt Box -->
        <div class="bg-gray-50 border border-gray-100 rounded-lg p-5 text-xs text-left space-y-3 mb-6">
            <div class="flex justify-between pb-2 border-b border-gray-200">
                <span class="text-gray-500">Order Number:</span>
                <span class="font-bold text-gray-900 font-mono">#{{ $order->order_number }}</span>
            </div>

            <div class="flex justify-between pb-2 border-b border-gray-200">
                <span class="text-gray-500">Transaction ID:</span>
                <span class="font-bold text-gray-900 font-mono">{{ $payment->transaction_id }}</span>
            </div>

            <div class="flex justify-between pb-2 border-b border-gray-200">
                <span class="text-gray-500">Payment Gateway:</span>
                <span class="font-bold text-gray-900 uppercase">SSLCommerz (Sandbox)</span>
            </div>

            <div class="flex justify-between pb-2 border-b border-gray-200">
                <span class="text-gray-500">Payment Channel:</span>
                <span class="font-bold text-gray-900">{{ $verification['card_type'] ?? 'Mobile Banking / Card' }}</span>
            </div>

            <div class="flex justify-between items-baseline pt-1">
                <span class="text-gray-700 font-bold">Total Amount Paid:</span>
                <span class="text-xl font-black text-daraz">৳{{ number_format($payment->amount) }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ route('checkout.success', $order->order_number) }}" 
               class="w-full sm:w-auto px-8 py-3.5 bg-daraz hover:bg-daraz-hover text-white text-xs font-black uppercase tracking-wider rounded shadow transition-colors text-center">
                View Order Details
            </a>
            <a href="{{ route('catalog.index') }}" 
               class="w-full sm:w-auto px-8 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold uppercase tracking-wider rounded transition-colors text-center">
                Continue Shopping
            </a>
        </div>
    </div>

</div>
@endsection
