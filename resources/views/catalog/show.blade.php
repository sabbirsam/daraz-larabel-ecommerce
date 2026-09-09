@extends('layouts.storefront')

@section('title', $product->title . ' - Daraz Bangladesh')
@section('meta_description', Str::limit(strip_tags($product->short_description ?? $product->description), 150))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4" x-data="{
    currentPrice: {{ $product->effective_price }},
    originalPrice: {{ $product->price }},
    currentStock: {{ $product->stock }},
    selectedVariantId: null,
    quantity: 1,
    selectedImage: '{{ $product->primaryImage && file_exists(public_path('storage/' . $product->primaryImage->image_path)) ? asset('storage/' . $product->primaryImage->image_path) : '' }}',
    activeTab: 'details',
    updateVariant(variant) {
        this.selectedVariantId = variant.id;
        this.currentPrice = variant.sale_price ? Number(variant.sale_price) : (variant.price ? Number(variant.price) : {{ $product->effective_price }});
        this.currentStock = variant.stock;
        if (this.quantity > this.currentStock) {
            this.quantity = Math.max(1, this.currentStock);
        }
    }
}">

    <!-- Breadcrumbs -->
    <nav class="flex text-xs text-gray-500 mb-4 items-center space-x-2">
        <a href="{{ route('home') }}" class="hover:text-daraz">Home</a>
        <span>/</span>
        @if($product->category)
            <a href="{{ route('catalog.index', ['category' => $product->category->slug]) }}" class="hover:text-daraz">
                {{ $product->category->name }}
            </a>
            <span>/</span>
        @endif
        <span class="text-gray-800 font-semibold line-clamp-1">{{ $product->title }}</span>
    </nav>

    <!-- Top Product Card: Gallery + Buy Box + Delivery Box -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- 1. LEFT: Interactive Gallery (4 cols) -->
            <div class="lg:col-span-5">
                <div class="sticky top-4">
                    <!-- Main Big Image Preview -->
                    <div class="relative w-full aspect-square bg-gray-50 rounded-lg overflow-hidden border border-gray-100 mb-3 flex items-center justify-center">
                        <template x-if="selectedImage">
                            <img :src="selectedImage" alt="{{ $product->title }}" class="w-full h-full object-contain">
                        </template>
                        <template x-if="!selectedImage">
                            <div class="w-full h-full flex items-center justify-center bg-orange-50 text-daraz font-black text-5xl">
                                {{ substr($product->title, 0, 1) }}
                            </div>
                        </template>

                        @if($product->discount_percentage > 0)
                            <span class="absolute top-3 left-3 bg-red-600 text-white text-xs font-black px-2 py-1 rounded shadow">
                                -{{ $product->discount_percentage }}%
                            </span>
                        @endif
                    </div>

                    <!-- Gallery Thumbnails Carousel -->
                    @if($product->images->count() > 1)
                        <div class="flex items-center space-x-2 overflow-x-auto pb-2">
                            @foreach($product->images as $img)
                                <button
                                    type="button"
                                    @click="selectedImage = '{{ asset('storage/' . $img->image_path) }}'"
                                    class="w-16 h-16 rounded border-2 overflow-hidden shrink-0 hover:border-daraz transition-colors"
                                    :class="selectedImage === '{{ asset('storage/' . $img->image_path) }}' ? 'border-daraz' : 'border-gray-200'"
                                >
                                    <img src="{{ asset('storage/' . $img->image_path) }}" alt="Thumbnail" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. CENTER: Buy Box & Product Info (4 cols) -->
            <div class="lg:col-span-4 flex flex-col justify-between">
                <div>
                    <!-- Title -->
                    <h1 class="text-xl font-bold text-gray-900 leading-snug mb-2">
                        {{ $product->title }}
                    </h1>

                    <!-- Ratings & Brand Bar -->
                    <div class="flex flex-wrap items-center text-xs text-gray-500 gap-3 pb-3 border-b">
                        <div class="flex items-center text-yellow-500 font-bold">
                            <span>★</span>
                            <span class="ml-1 text-gray-800">{{ $product->rating_avg }}</span>
                            <span class="ml-1 text-gray-400 font-normal">({{ $product->reviews_count }} Ratings)</span>
                        </div>
                        @if($product->brand)
                            <span>|</span>
                            <span>Brand: <a href="{{ route('catalog.index', ['brands' => [$product->brand->slug]]) }}" class="text-daraz font-semibold hover:underline">{{ $product->brand->name }}</a></span>
                        @endif
                        <span>|</span>
                        <span>SKU: <strong class="text-gray-700">{{ $product->sku }}</strong></span>
                    </div>

                    <!-- Price Block -->
                    <div class="py-4 border-b">
                        <div class="flex items-baseline space-x-3">
                            <span class="text-3xl font-black text-[#F85606]">
                                ৳<span x-text="Number(currentPrice).toLocaleString()">{{ number_format($product->effective_price) }}</span>
                            </span>
                            <template x-if="originalPrice > currentPrice">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-400 line-through">
                                        ৳<span x-text="Number(originalPrice).toLocaleString()">{{ number_format($product->price) }}</span>
                                    </span>
                                    <span class="bg-red-50 text-red-600 font-bold text-xs px-2 py-0.5 rounded border border-red-200">
                                        Save ৳<span x-text="Number(originalPrice - currentPrice).toLocaleString()"></span>
                                    </span>
                                </div>
                            </template>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Inclusive of all local taxes</p>
                    </div>

                    <!-- Variants Selection -->
                    @if($product->variants->count() > 0)
                        <div class="py-3 border-b space-y-3">
                            <h4 class="text-xs font-bold text-gray-700 uppercase">Available Options</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($product->variants as $variant)
                                    <button
                                        type="button"
                                        @click="updateVariant({{ $variant->toJson() }})"
                                        class="px-3 py-1.5 rounded text-xs font-medium border transition-all flex items-center gap-1.5"
                                        :class="selectedVariantId === {{ $variant->id }} ? 'border-daraz bg-orange-50 text-daraz font-bold ring-1 ring-daraz' : 'border-gray-200 text-gray-700 hover:border-gray-300'"
                                    >
                                        @if(is_array($variant->attributes))
                                            @foreach($variant->attributes as $k => $v)
                                                <span>{{ $v }}</span>
                                            @endforeach
                                        @else
                                            <span>{{ $variant->sku }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Quantity Stepper -->
                    <div class="py-4 border-b flex items-center space-x-6">
                        <span class="text-xs font-bold text-gray-700 uppercase">Quantity</span>
                        <div class="flex items-center border border-gray-300 rounded overflow-hidden">
                            <button
                                type="button"
                                @click="if (quantity > 1) quantity--"
                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold transition-colors"
                            >
                                -
                            </button>
                            <span class="px-4 py-1 text-xs font-bold text-gray-900 bg-white" x-text="quantity">1</span>
                            <button
                                type="button"
                                @click="if (quantity < currentStock) quantity++"
                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold transition-colors"
                            >
                                +
                            </button>
                        </div>
                        <span class="text-xs" :class="currentStock > 0 ? 'text-green-600 font-semibold' : 'text-red-600 font-bold'">
                            <span x-text="currentStock > 0 ? (currentStock + ' in stock') : 'Out of stock'"></span>
                        </span>
                    </div>
                </div>

                <!-- Call to Action Buttons -->
                <div class="pt-6 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="variant_id" :value="selectedVariantId">
                            <input type="hidden" name="quantity" :value="quantity">
                            <input type="hidden" name="buy_now" value="1">
                            <button
                                type="submit"
                                :disabled="currentStock <= 0"
                                class="w-full py-3 bg-[#D03B00] hover:bg-[#B33300] text-white font-extrabold text-sm rounded shadow-md uppercase tracking-wider transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Buy Now
                            </button>
                        </form>

                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="variant_id" :value="selectedVariantId">
                            <input type="hidden" name="quantity" :value="quantity">
                            <button
                                type="submit"
                                :disabled="currentStock <= 0"
                                class="w-full py-3 bg-[#F85606] hover:bg-[#E04C04] text-white font-extrabold text-sm rounded shadow-md uppercase tracking-wider transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Add to Cart
                            </button>
                        </form>
                    </div>

                    <!-- Wishlist Favorite Action -->
                    <div class="pt-2">
                        @auth
                            <form action="{{ route('wishlist.toggle') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="flex items-center justify-center w-full py-2 bg-gray-100 hover:bg-orange-50 text-gray-700 hover:text-daraz text-xs font-semibold rounded border border-gray-200 transition-colors">
                                    <svg class="w-4 h-4 mr-1.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                    Add to My Wishlist
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="flex items-center justify-center w-full py-2 bg-gray-100 hover:bg-orange-50 text-gray-700 hover:text-daraz text-xs font-semibold rounded border border-gray-200 transition-colors">
                                <svg class="w-4 h-4 mr-1.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                Login to Add to Wishlist
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- 3. RIGHT: Delivery & Seller Services (3 cols) -->
            <div class="lg:col-span-3 space-y-4">
                <!-- Delivery Details Card -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-xs">
                    <h3 class="font-bold text-gray-900 mb-3 uppercase tracking-wider text-[11px] text-gray-500">Delivery Options</h3>

                    <div class="space-y-3">
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 text-daraz shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            <div>
                                <span class="font-semibold text-gray-800">Dhaka, Bangladesh</span>
                                <p class="text-[11px] text-gray-500">Standard Nationwide Delivery</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-2 pt-2 border-t border-gray-200">
                            <svg class="w-4 h-4 text-green-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <div>
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-gray-800">Standard Delivery</span>
                                    <strong class="text-daraz">৳60</strong>
                                </div>
                                <p class="text-[11px] text-gray-500">Guaranteed by 3 - 5 business days</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-2 pt-2 border-t border-gray-200">
                            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <span class="font-semibold text-gray-800">Cash on Delivery Available</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Return & Warranty Card -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-xs">
                    <h3 class="font-bold text-gray-900 mb-3 uppercase tracking-wider text-[11px] text-gray-500">Return & Warranty</h3>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>7 Days Easy Return Policy</span>
                        </div>
                        <div class="flex items-center space-x-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span>100% Authentic Guaranteed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Full Width Sections: Details, Specifications, Reviews -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <!-- Tab Controls -->
        <div class="flex border-b border-gray-200 mb-6 text-sm font-bold">
            <button
                type="button"
                @click="activeTab = 'details'"
                class="py-3 px-6 border-b-2 transition-colors uppercase tracking-wider"
                :class="activeTab === 'details' ? 'border-daraz text-daraz' : 'border-transparent text-gray-500 hover:text-gray-700'"
            >
                Product Details
            </button>
            <button
                type="button"
                @click="activeTab = 'specs'"
                class="py-3 px-6 border-b-2 transition-colors uppercase tracking-wider"
                :class="activeTab === 'specs' ? 'border-daraz text-daraz' : 'border-transparent text-gray-500 hover:text-gray-700'"
            >
                Specifications
            </button>
            <button
                type="button"
                @click="activeTab = 'reviews'"
                class="py-3 px-6 border-b-2 transition-colors uppercase tracking-wider flex items-center space-x-2"
                :class="activeTab === 'reviews' ? 'border-daraz text-daraz' : 'border-transparent text-gray-500 hover:text-gray-700'"
            >
                <span>Ratings & Reviews</span>
                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs font-normal">({{ $totalReviews }})</span>
            </button>
        </div>

        <!-- Tab 1: Product Details -->
        <div x-show="activeTab === 'details'" class="prose max-w-none text-sm text-gray-700 leading-relaxed">
            @if($product->short_description)
                <p class="text-base font-medium text-gray-900 mb-4">{{ $product->short_description }}</p>
            @endif
            {!! $product->description !!}
        </div>

        <!-- Tab 2: Specifications Table -->
        <div x-show="activeTab === 'specs'" x-cloak>
            @if(!empty($product->specifications))
                <div class="border border-gray-200 rounded-lg overflow-hidden max-w-3xl">
                    <table class="w-full text-left text-xs divide-y divide-gray-200">
                        <tbody class="divide-y divide-gray-100">
                            @foreach($product->specifications as $key => $val)
                                <tr class="hover:bg-gray-50">
                                    <td class="w-1/3 px-4 py-2.5 font-bold text-gray-600 bg-gray-50">{{ $key }}</td>
                                    <td class="px-4 py-2.5 text-gray-900">{{ $val }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-xs text-gray-500">Standard specifications apply for this product.</p>
            @endif
        </div>

        <!-- Tab 3: Ratings & Customer Reviews -->
        <div x-show="activeTab === 'reviews'" x-cloak>
            <!-- Rating Breakdown Header -->
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-100 mb-8 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                <div class="md:col-span-4 text-center md:border-r border-gray-200 pr-6">
                    <div class="text-5xl font-black text-gray-900">{{ $product->rating_avg }}<span class="text-2xl text-gray-400 font-normal">/5</span></div>
                    <div class="flex justify-center text-yellow-400 text-lg my-1">
                        @for($i = 1; $i <= 5; $i++)
                            <span>{{ $i <= round($product->rating_avg) ? '★' : '☆' }}</span>
                        @endfor
                    </div>
                    <p class="text-xs text-gray-500">{{ $totalReviews }} Verified Reviews</p>
                </div>

                <!-- Star Percentage Progress Bars -->
                <div class="md:col-span-8 space-y-1.5 text-xs">
                    @foreach([5, 4, 3, 2, 1] as $star)
                        <div class="flex items-center space-x-3">
                            <span class="w-10 font-bold text-gray-600">{{ $star }} Star</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ $ratingPercentages[$star] ?? 0 }}%"></div>
                            </div>
                            <span class="w-10 text-right text-gray-500">{{ $ratingCounts[$star] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Individual Customer Reviews Feed -->
            <div class="space-y-4 divide-y divide-gray-100">
                @forelse($product->reviews as $review)
                    <div class="pt-4 first:pt-0">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center space-x-2">
                                <div class="text-yellow-400 text-sm">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                                <span class="text-xs font-bold text-gray-800">{{ $review->user->name }}</span>
                                @if($review->is_verified_purchase)
                                    <span class="bg-green-100 text-green-700 text-[10px] font-semibold px-2 py-0.5 rounded-full flex items-center">
                                        ✓ Verified Purchase
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] text-gray-400">{{ $review->created_at->format('M d, Y') }}</span>
                        </div>
                        <p class="text-xs text-gray-700 leading-relaxed mt-2">{{ $review->comment }}</p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 py-4 text-center">There are no customer reviews yet for this product.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="mt-8">
            <h3 class="text-base font-bold text-gray-800 mb-4">Related Products</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($relatedProducts as $rel)
                    <a href="{{ route('catalog.show', $rel->slug) }}" class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group flex flex-col justify-between">
                        <div>
                            <div class="aspect-square bg-gray-50 rounded overflow-hidden mb-2">
                                @if($rel->primaryImage && file_exists(public_path('storage/' . $rel->primaryImage->image_path)))
                                    <img src="{{ asset('storage/' . $rel->primaryImage->image_path) }}" alt="{{ $rel->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-orange-50 text-daraz font-black text-2xl">
                                        {{ substr($rel->title, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <h4 class="text-xs font-semibold text-gray-800 line-clamp-2 group-hover:text-daraz">{{ $rel->title }}</h4>
                        </div>
                        <div class="mt-2 font-black text-sm text-[#F85606]">
                            ৳{{ number_format($rel->effective_price) }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
