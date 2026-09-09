@extends('layouts.storefront')

@section('title', 'SSLCommerz Payment Gateway - Sandbox Simulator')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    <!-- SSLCommerz Header Simulator Bar -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-800 text-white rounded-t-lg p-5 shadow-sm flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xl font-black tracking-wider">SSLCOMMERZ</span>
                <span class="px-2 py-0.5 bg-yellow-400 text-gray-900 text-[10px] font-black rounded uppercase">Sandbox Mode</span>
            </div>
            <p class="text-xs text-blue-100 mt-1">Official Bangladesh Payment Gateway Testing Sandbox</p>
        </div>
        <div class="text-right">
            <span class="text-xs text-blue-200 block">Total Payable</span>
            <span class="text-xl font-black text-yellow-300">৳{{ number_format($order->total) }}</span>
        </div>
    </div>

    <!-- Merchant & Order Summary Box -->
    <div class="bg-white border-x border-gray-200 p-6 border-b">
        <div class="flex justify-between items-center text-xs pb-3 border-b border-gray-100">
            <div>
                <span class="text-gray-500">Merchant:</span>
                <span class="font-bold text-gray-900 ml-1">Daraz Online Shopping Bangladesh</span>
            </div>
            <div>
                <span class="text-gray-500">Order ID:</span>
                <span class="font-bold text-daraz ml-1">#{{ $order->order_number }}</span>
            </div>
        </div>

        <div class="pt-4 text-xs text-gray-600">
            <p class="font-semibold text-gray-800 mb-1">Customer Details:</p>
            <p>{{ $order->shipping_address['name'] ?? 'Customer' }} ({{ $order->shipping_address['phone'] ?? '01700000000' }})</p>
            <p class="text-gray-500 text-[11px]">{{ $order->shipping_address['address_line'] ?? '' }}</p>
        </div>
    </div>

    <!-- Payment Simulator Action Panel -->
    <div class="bg-gray-50 rounded-b-lg border border-t-0 border-gray-200 p-6 shadow-sm">
        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-4 text-center">
            Select a Simulated Payment Action:
        </h3>

        <div class="space-y-4">
            
            <!-- 1. Simulate Success via bKash -->
            <form action="{{ route('payment.sslcommerz.success') }}" method="POST">
                @csrf
                <input type="hidden" name="tran_id" value="{{ $order->order_number }}">
                <input type="hidden" name="val_id" value="VAL-BKASH-{{ strtoupper(bin2hex(random_bytes(4))) }}">
                <input type="hidden" name="amount" value="{{ $order->total }}">
                <input type="hidden" name="card_type" value="bKash-MobileBanking">
                <input type="hidden" name="status" value="VALID">
                <input type="hidden" name="currency" value="BDT">
                
                <button type="submit" 
                        class="w-full py-3.5 px-4 bg-pink-600 hover:bg-pink-700 text-white rounded-lg font-bold text-xs flex items-center justify-between shadow transition-colors">
                    <span class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 bg-white text-pink-600 rounded font-black text-[11px]">bKash</span>
                        <span>Simulate Successful Payment via bKash</span>
                    </span>
                    <span class="text-pink-100 font-mono">৳{{ number_format($order->total) }} &rarr;</span>
                </button>
            </form>

            <!-- 2. Simulate Success via Nagad -->
            <form action="{{ route('payment.sslcommerz.success') }}" method="POST">
                @csrf
                <input type="hidden" name="tran_id" value="{{ $order->order_number }}">
                <input type="hidden" name="val_id" value="VAL-NAGAD-{{ strtoupper(bin2hex(random_bytes(4))) }}">
                <input type="hidden" name="amount" value="{{ $order->total }}">
                <input type="hidden" name="card_type" value="Nagad-MobileBanking">
                <input type="hidden" name="status" value="VALID">
                <input type="hidden" name="currency" value="BDT">
                
                <button type="submit" 
                        class="w-full py-3.5 px-4 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-bold text-xs flex items-center justify-between shadow transition-colors">
                    <span class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 bg-white text-orange-600 rounded font-black text-[11px]">Nagad</span>
                        <span>Simulate Successful Payment via Nagad</span>
                    </span>
                    <span class="text-orange-100 font-mono">৳{{ number_format($order->total) }} &rarr;</span>
                </button>
            </form>

            <!-- 3. Simulate Success via VISA / MasterCard -->
            <form action="{{ route('payment.sslcommerz.success') }}" method="POST">
                @csrf
                <input type="hidden" name="tran_id" value="{{ $order->order_number }}">
                <input type="hidden" name="val_id" value="VAL-CARD-{{ strtoupper(bin2hex(random_bytes(4))) }}">
                <input type="hidden" name="amount" value="{{ $order->total }}">
                <input type="hidden" name="card_type" value="VISA-DebitCard">
                <input type="hidden" name="status" value="VALID">
                <input type="hidden" name="currency" value="BDT">
                
                <button type="submit" 
                        class="w-full py-3.5 px-4 bg-blue-700 hover:bg-blue-800 text-white rounded-lg font-bold text-xs flex items-center justify-between shadow transition-colors">
                    <span class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 bg-white text-blue-700 rounded font-black text-[11px]">VISA</span>
                        <span>Simulate Successful Payment via Visa / MasterCard</span>
                    </span>
                    <span class="text-blue-100 font-mono">৳{{ number_format($order->total) }} &rarr;</span>
                </button>
            </form>

            <div class="grid grid-cols-2 gap-4 pt-2">
                <!-- 4. Simulate Failure -->
                <form action="{{ route('payment.sslcommerz.fail') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tran_id" value="{{ $order->order_number }}">
                    <input type="hidden" name="status" value="FAILED">
                    <input type="hidden" name="error" value="Insufficient funds in card/wallet.">
                    
                    <button type="submit" 
                            class="w-full py-3 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg font-bold text-xs border border-red-200 transition-colors">
                        Simulate Payment Failure
                    </button>
                </form>

                <!-- 5. Simulate Cancel -->
                <form action="{{ route('payment.sslcommerz.cancel') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tran_id" value="{{ $order->order_number }}">
                    <input type="hidden" name="status" value="CANCELLED">
                    
                    <button type="submit" 
                            class="w-full py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold text-xs border border-gray-300 transition-colors">
                        Simulate Cancellation
                    </button>
                </form>
            </div>

        </div>

        <p class="text-[11px] text-gray-400 text-center mt-6">
            🔒 This sandbox portal simulates the SSLCommerz gateway response callbacks in local testing mode.
        </p>
    </div>

</div>
@endsection
