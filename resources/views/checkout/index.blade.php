@extends('layouts.storefront')

@section('title', 'Checkout - Daraz Online Shopping')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6" x-data="{
    selectedAddressId: '{{ $selectedAddress?->id ?? '' }}',
    showNewAddressForm: {{ $addresses->isEmpty() ? 'true' : 'false' }},
    deliveryMethod: 'standard',
    standardFee: {{ $standardShippingFee }},
    expressFee: {{ $expressShippingFee }},
    subtotal: {{ $subtotal }},
    discount: {{ $discount }},
    divisions: {{ json_encode($divisions) }},
    selectedDivision: '',
    selectedDistrict: '',
    get currentShippingFee() {
        return this.deliveryMethod === 'express' ? this.expressFee : this.standardFee;
    },
    get currentGrandTotal() {
        return Math.max(0, (this.subtotal - this.discount) + this.currentShippingFee);
    },
    districts() {
        return this.selectedDivision && this.divisions[this.selectedDivision] ? this.divisions[this.selectedDivision] : [];
    }
}">

    <!-- Checkout Steps Breadcrumb -->
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-center max-w-2xl mx-auto text-xs font-semibold">
            <!-- Step 1 -->
            <a href="{{ route('cart.index') }}" class="flex items-center text-green-600 hover:underline">
                <div class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-2 font-bold text-xs">
                    ✓
                </div>
                <span>1. Shopping Cart</span>
            </a>

            <div class="w-16 h-0.5 bg-green-500 mx-3"></div>

            <!-- Step 2 -->
            <div class="flex items-center text-daraz font-bold">
                <div class="w-6 h-6 rounded-full bg-orange-100 text-daraz flex items-center justify-center mr-2 text-xs">
                    2
                </div>
                <span>2. Delivery & Payment</span>
            </div>

            <div class="w-16 h-0.5 bg-gray-200 mx-3"></div>

            <!-- Step 3 -->
            <div class="flex items-center text-gray-400">
                <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mr-2 text-xs">
                    3
                </div>
                <span>3. Order Complete</span>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('error'))
        <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded text-xs flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded text-xs flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('coupon_error'))
        <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded text-xs">
            {{ session('coupon_error') }}
        </div>
    @endif

    @if(session('coupon_success'))
        <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded text-xs">
            {{ session('coupon_success') }}
        </div>
    @endif

    <!-- Main Checkout Form -->
    <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT COLUMN: Delivery & Payment Details (8 cols) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. Delivery Address Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                        <div class="flex items-center space-x-2">
                            <span class="w-7 h-7 rounded-full bg-orange-50 text-daraz flex items-center justify-center font-bold text-sm">
                                1
                            </span>
                            <h2 class="font-bold text-gray-900 text-base">Delivery Address</h2>
                        </div>
                        
                        @if($addresses->isNotEmpty())
                            <button type="button" 
                                    @click="showNewAddressForm = !showNewAddressForm" 
                                    class="text-xs font-bold text-daraz hover:underline flex items-center">
                                <span x-show="!showNewAddressForm">+ Add New Address</span>
                                <span x-show="showNewAddressForm">Select Saved Address</span>
                            </button>
                        @endif
                    </div>

                    <!-- Saved Addresses List -->
                    @if($addresses->isNotEmpty())
                        <div x-show="!showNewAddressForm" class="space-y-3">
                            @foreach($addresses as $address)
                                <label class="relative block border rounded-lg p-4 cursor-pointer transition-all"
                                       :class="selectedAddressId == '{{ $address->id }}' ? 'border-daraz bg-orange-50/20 ring-1 ring-daraz' : 'border-gray-200 hover:border-gray-300'">
                                    <div class="flex items-start">
                                        <input type="radio" 
                                               name="address_id" 
                                               value="{{ $address->id }}" 
                                               x-model="selectedAddressId"
                                               class="mt-1 text-daraz focus:ring-daraz h-4 w-4 border-gray-300">
                                        
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-bold text-gray-900 text-sm">{{ $address->name }}</span>
                                                <span class="text-xs text-gray-500 font-medium">{{ $address->phone }}</span>
                                                
                                                <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded {{ $address->type === 'home' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                                    {{ $address->type }}
                                                </span>

                                                @if($address->is_default_shipping)
                                                    <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded bg-green-100 text-green-700">
                                                        Default
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                                {{ $address->full_address }}
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <!-- New Address Form (Toggleable or default if no addresses) -->
                    <div x-show="showNewAddressForm" class="{{ $addresses->isNotEmpty() ? 'mt-4 pt-4 border-t border-gray-100' : '' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <!-- Full Name -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-1">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="e.g. Sabbir Hossain"
                                       class="w-full border-gray-300 rounded text-xs focus:ring-daraz focus:border-daraz px-3 py-2">
                                @error('name') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-1">Phone Number (11 digits) *</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="017XXXXXXXX"
                                       class="w-full border-gray-300 rounded text-xs focus:ring-daraz focus:border-daraz px-3 py-2">
                                @error('phone') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                            </div>

                            <!-- Division -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-1">Division *</label>
                                <select name="division" x-model="selectedDivision"
                                        class="w-full border-gray-300 rounded text-xs focus:ring-daraz focus:border-daraz px-3 py-2">
                                    <option value="">-- Select Division --</option>
                                    @foreach($divisions as $divName => $distList)
                                        <option value="{{ $divName }}">{{ $divName }}</option>
                                    @endforeach
                                </select>
                                @error('division') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                            </div>

                            <!-- District -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-1">District *</label>
                                <select name="district" x-model="selectedDistrict" :disabled="!selectedDivision"
                                        class="w-full border-gray-300 rounded text-xs focus:ring-daraz focus:border-daraz px-3 py-2 disabled:bg-gray-100">
                                    <option value="">-- Select District --</option>
                                    <template x-for="dist in districts()" :key="dist">
                                        <option :value="dist" x-text="dist"></option>
                                    </template>
                                </select>
                                @error('district') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                            </div>

                            <!-- Upazila / Thana / Area -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-1">Thana / Upazila / Area</label>
                                <input type="text" name="upazila" value="{{ old('upazila') }}" placeholder="e.g. Dhanmondi, Gulshan, Mirpur"
                                       class="w-full border-gray-300 rounded text-xs focus:ring-daraz focus:border-daraz px-3 py-2">
                            </div>

                            <!-- Address Type -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-1">Address Label</label>
                                <div class="flex items-center space-x-4 mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="type" value="home" checked class="text-daraz focus:ring-daraz">
                                        <span class="ml-1.5 text-xs text-gray-700">Home</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="type" value="office" class="text-daraz focus:ring-daraz">
                                        <span class="ml-1.5 text-xs text-gray-700">Office</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Street Address Line -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 font-semibold mb-1">Detailed Street Address *</label>
                                <textarea name="address_line" rows="2" placeholder="House number, road number, apartment details, landmark"
                                          class="w-full border-gray-300 rounded text-xs focus:ring-daraz focus:border-daraz px-3 py-2">{{ old('address_line') }}</textarea>
                                @error('address_line') <span class="text-red-500 text-[11px]">{{ $message }}</span> @enderror
                            </div>

                            @if($addresses->isNotEmpty())
                                <div class="md:col-span-2 flex items-center">
                                    <input type="checkbox" name="save_address" value="1" id="save_address" checked
                                           class="rounded border-gray-300 text-daraz focus:ring-daraz">
                                    <label for="save_address" class="ml-2 text-xs text-gray-600">Save this address to my account for future orders</label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 2. Delivery Option Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center space-x-2 pb-4 mb-4 border-b border-gray-100">
                        <span class="w-7 h-7 rounded-full bg-orange-50 text-daraz flex items-center justify-center font-bold text-sm">
                            2
                        </span>
                        <h2 class="font-bold text-gray-900 text-base">Select Delivery Option</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Standard Delivery -->
                        <label class="relative border rounded-lg p-4 cursor-pointer transition-all"
                               :class="deliveryMethod === 'standard' ? 'border-daraz bg-orange-50/20 ring-1 ring-daraz' : 'border-gray-200 hover:border-gray-300'">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start">
                                    <input type="radio" 
                                           name="delivery_method" 
                                           value="standard" 
                                           x-model="deliveryMethod"
                                           class="mt-1 text-daraz focus:ring-daraz h-4 w-4 border-gray-300">
                                    <div class="ml-3">
                                        <div class="font-bold text-gray-900 text-sm">Standard Delivery</div>
                                        <div class="text-xs text-gray-500 mt-0.5">Estimated 2-4 business days</div>
                                        <div class="text-[11px] text-green-600 font-semibold mt-1">
                                            Guaranteed safe delivery by Daraz Express
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($standardShippingFee == 0)
                                        <span class="font-black text-green-600 text-sm uppercase">FREE</span>
                                    @else
                                        <span class="font-bold text-gray-900 text-sm">৳{{ number_format($standardShippingFee) }}</span>
                                    @endif
                                </div>
                            </div>
                        </label>

                        <!-- Express Delivery -->
                        <label class="relative border rounded-lg p-4 cursor-pointer transition-all"
                               :class="deliveryMethod === 'express' ? 'border-daraz bg-orange-50/20 ring-1 ring-daraz' : 'border-gray-200 hover:border-gray-300'">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start">
                                    <input type="radio" 
                                           name="delivery_method" 
                                           value="express" 
                                           x-model="deliveryMethod"
                                           class="mt-1 text-daraz focus:ring-daraz h-4 w-4 border-gray-300">
                                    <div class="ml-3">
                                        <div class="font-bold text-gray-900 text-sm flex items-center">
                                            <span>Express Fast Delivery</span>
                                            <span class="ml-2 px-1.5 py-0.5 bg-yellow-100 text-yellow-800 text-[10px] font-bold rounded">⚡ Fast</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">Estimated within 24-48 hours</div>
                                        <div class="text-[11px] text-blue-600 font-semibold mt-1">
                                            Priority dispatch & handling
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-gray-900 text-sm">৳{{ number_format($expressShippingFee) }}</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 3. Package Items Review -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                        <div class="flex items-center space-x-2">
                            <span class="w-7 h-7 rounded-full bg-orange-50 text-daraz flex items-center justify-center font-bold text-sm">
                                3
                            </span>
                            <h2 class="font-bold text-gray-900 text-base">
                                Package Items ({{ $cart->totalItems() }} items)
                            </h2>
                        </div>
                        <a href="{{ route('cart.index') }}" class="text-xs font-bold text-daraz hover:underline">
                            Edit Cart
                        </a>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($cart->items as $item)
                            <div class="py-3 flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-3">
                                    <div class="w-14 h-14 bg-gray-50 rounded border border-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                        @if($item->product->primaryImage)
                                            <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" 
                                                 alt="{{ $item->product->title }}" 
                                                 class="w-full h-full object-cover">
                                        @else
                                            <div class="text-gray-300 font-bold text-[10px]">DARAZ</div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="font-bold text-gray-900 line-clamp-1 max-w-sm">
                                            {{ $item->product->title }}
                                        </div>
                                        @if($item->variant)
                                            <div class="text-[11px] text-gray-500 mt-0.5">
                                                Variant: <span class="text-gray-700 font-medium">{{ $item->variant->title }}</span>
                                            </div>
                                        @endif
                                        <div class="text-[11px] text-gray-500">
                                            Qty: <span class="font-bold text-gray-900">{{ $item->quantity }}</span> × ৳{{ number_format($item->price) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right font-black text-gray-900 text-sm">
                                    ৳{{ number_format($item->subtotal()) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 4. Payment Method Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center space-x-2 pb-4 mb-4 border-b border-gray-100">
                        <span class="w-7 h-7 rounded-full bg-orange-50 text-daraz flex items-center justify-center font-bold text-sm">
                            4
                        </span>
                        <h2 class="font-bold text-gray-900 text-base">Select Payment Method</h2>
                    </div>

                    <div class="space-y-3">
                        <!-- Cash on Delivery (COD) -->
                        <label class="relative block border rounded-lg p-4 cursor-pointer hover:border-gray-300 transition-all border-daraz bg-orange-50/10">
                            <div class="flex items-start">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="cod" 
                                       checked
                                       class="mt-1 text-daraz focus:ring-daraz h-4 w-4 border-gray-300">
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-900 text-sm">Cash on Delivery (COD)</span>
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold rounded">Recommended</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Pay in cash at your doorstep when your package is delivered. Please prepare the exact amount.
                                    </p>
                                </div>
                            </div>
                        </label>

                        <!-- Online Payment (SSLCommerz) -->
                        <label class="relative block border rounded-lg p-4 cursor-pointer hover:border-gray-300 transition-all">
                            <div class="flex items-start">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="sslcommerz" 
                                       class="mt-1 text-daraz focus:ring-daraz h-4 w-4 border-gray-300">
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-900 text-sm">Online Payment (SSLCommerz)</span>
                                        <div class="flex items-center space-x-1.5">
                                            <span class="px-1.5 py-0.5 bg-pink-100 text-pink-700 text-[10px] font-bold rounded">bKash</span>
                                            <span class="px-1.5 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded">Nagad</span>
                                            <span class="px-1.5 py-0.5 bg-purple-100 text-purple-700 text-[10px] font-bold rounded">Rocket</span>
                                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded">Cards</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Instant payment using Bangladeshi Mobile Banking (bKash, Nagad, Rocket) or Visa / Mastercard.
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Customer Order Notes -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <label class="block text-gray-700 font-semibold text-xs mb-1">
                            Delivery Notes / Special Instructions (Optional)
                        </label>
                        <input type="text" name="customer_notes" placeholder="e.g. Please call before arriving or leave with security"
                               class="w-full border-gray-300 rounded text-xs focus:ring-daraz focus:border-daraz px-3 py-2">
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Order Summary Box (4 cols sticky) -->
            <div class="lg:col-span-4 space-y-4 sticky top-4">
                
                <!-- Voucher Code Box -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider mb-2">
                        Daraz Voucher / Promo Code
                    </h3>

                    @if($appliedCoupon)
                        <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded p-2.5 text-xs text-green-800">
                            <div>
                                <span class="font-bold uppercase tracking-wider">{{ $appliedCoupon->code }}</span>
                                <span class="text-green-600 block text-[11px]">৳{{ number_format($discount) }} discount applied</span>
                            </div>
                            <button type="submit" 
                                    formaction="{{ route('checkout.coupon.remove') }}" 
                                    class="text-xs text-red-600 font-bold hover:underline">
                                Remove
                            </button>
                        </div>
                    @else
                        <div class="flex space-x-2">
                            <input type="text" 
                                   name="coupon_code" 
                                   placeholder="Enter voucher code" 
                                   form="coupon-form"
                                   class="w-full border-gray-300 rounded text-xs focus:ring-daraz focus:border-daraz uppercase px-3 py-2">
                            <button type="submit" 
                                    form="coupon-form"
                                    class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded transition-colors uppercase">
                                Apply
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Order Summary Breakdown -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 text-sm pb-4 border-b border-gray-100 uppercase tracking-wider">
                        Order Summary
                    </h3>

                    <div class="py-4 space-y-3 text-xs border-b border-gray-100">
                        <div class="flex justify-between text-gray-600">
                            <span>Items Subtotal ({{ $cart->totalItems() }})</span>
                            <span class="font-bold text-gray-900">৳{{ number_format($subtotal) }}</span>
                        </div>

                        <div class="flex justify-between text-gray-600">
                            <span>Delivery Fee</span>
                            <span class="font-bold text-gray-900" x-text="currentShippingFee === 0 ? 'FREE' : '৳' + currentShippingFee">
                                {{ $standardShippingFee == 0 ? 'FREE' : '৳' . number_format($standardShippingFee) }}
                            </span>
                        </div>

                        <div x-show="discount > 0" class="flex justify-between text-green-600 font-bold">
                            <span>Voucher Discount</span>
                            <span>-৳{{ number_format($discount) }}</span>
                        </div>
                    </div>

                    <!-- Grand Total -->
                    <div class="py-4 flex justify-between items-baseline">
                        <span class="text-sm font-bold text-gray-800">Total Payable</span>
                        <span class="text-2xl font-black text-daraz" x-text="'৳' + currentGrandTotal.toLocaleString()">
                            ৳{{ number_format($grandTotal) }}
                        </span>
                    </div>

                    <p class="text-[10px] text-gray-400 text-right mb-4">VAT included where applicable</p>

                    <!-- Submit Order Button -->
                    <button type="submit" 
                            id="place-order-btn"
                            class="w-full py-4 bg-daraz hover:bg-daraz-hover text-white text-center font-black text-sm uppercase tracking-wider rounded shadow-md transition-all active:scale-[0.99] flex items-center justify-center space-x-2">
                        <span>Place Order Now</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>

                    <!-- Trust and Guarantees -->
                    <div class="mt-6 pt-4 border-t border-gray-100 space-y-2 text-[11px] text-gray-500">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span>100% Secure & Encrypted Checkout</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>7-Day Return Guarantee</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Verified Authentic Daraz Merchandise</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <!-- Hidden Dedicated Form for Coupon Code Apply to prevent form nesting -->
    <form action="{{ route('checkout.coupon.apply') }}" method="POST" id="coupon-form" class="hidden">
        @csrf
    </form>

</div>
@endsection
