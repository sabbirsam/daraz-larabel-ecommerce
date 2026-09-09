@extends('layouts.storefront')

@section('title', 'Payment Cancelled - Daraz Online Shopping')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 text-center">
        <!-- Cancel Icon -->
        <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-black text-gray-900 mb-1">Payment Cancelled</h1>
        <p class="text-xs text-gray-500 mb-6 max-w-md mx-auto">
            You cancelled the transaction before completion. No deductions were made from your wallet or bank card.
        </p>

        <!-- Transaction Details Box -->
        <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-5 text-xs text-left space-y-3 mb-6">
            @if($order)
                <div class="flex justify-between pb-2 border-b border-yellow-100">
                    <span class="text-gray-500">Order Number:</span>
                    <span class="font-bold text-gray-900 font-mono">#{{ $order->order_number }}</span>
                </div>

                <div class="flex justify-between pb-2 border-b border-yellow-100">
                    <span class="text-gray-500">Amount Due:</span>
                    <span class="font-bold text-gray-900">৳{{ number_format($order->total) }}</span>
                </div>
            @endif

            <div class="flex justify-between pb-2 border-b border-yellow-100">
                <span class="text-gray-500">Transaction Status:</span>
                <span class="font-bold text-yellow-700 uppercase">CANCELLED</span>
            </div>

            <p class="text-[11px] text-gray-600 pt-1">
                Your order remains saved in your account. You can complete payment at any time before shipment.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
            @if($order)
                <a href="{{ route('payment.sslcommerz.simulator', $order->order_number) }}" 
                   class="w-full sm:w-auto px-8 py-3.5 bg-daraz hover:bg-daraz-hover text-white text-xs font-black uppercase tracking-wider rounded shadow transition-colors text-center">
                    Resume Payment
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
