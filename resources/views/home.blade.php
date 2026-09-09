@extends('layouts.storefront')

@section('title', 'Daraz Bangladesh - Online Shopping Mall')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">

    <!-- 1. Hero Banner & Category Menu Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-6">
        <!-- Left: Category Tree Sidebar (Desktop) -->
        <div class="hidden lg:block lg:col-span-3 bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden text-xs">
            <div class="bg-gray-50 px-4 py-2.5 font-bold text-gray-700 border-b flex items-center">
                <svg class="w-4 h-4 mr-2 text-daraz" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                Categories
            </div>
            <ul class="divide-y divide-gray-50 py-1">
                @foreach($categories as $category)
                    <li class="group relative px-4 py-2 hover:bg-orange-50 hover:text-daraz transition-colors">
                        <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" class="flex items-center justify-between font-medium">
                            <span>{{ $category->name }}</span>
                            @if($category->children->count() > 0)
                                <svg class="w-3 h-3 text-gray-400 group-hover:text-daraz" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            @endif
                        </a>
                        <!-- Subcategories Flyout Menu -->
                        @if($category->children->count() > 0)
                            <div class="hidden group-hover:block absolute left-full top-0 w-56 bg-white shadow-xl border border-gray-100 rounded-r-lg py-2 z-50">
                                @foreach($category->children as $child)
                                    <a href="{{ route('catalog.index', ['category' => $child->slug]) }}" class="block px-4 py-1.5 text-xs text-gray-700 hover:bg-orange-50 hover:text-daraz font-medium">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Center: Hero Promotional Banner -->
        <div class="lg:col-span-6 bg-gradient-to-r from-[#F85606] via-[#FF7733] to-[#E04C04] rounded-lg shadow-sm p-8 text-white flex flex-col justify-between relative overflow-hidden min-h-[300px]">
            <div class="relative z-10">
                <span class="inline-block bg-yellow-400 text-gray-900 font-extrabold text-[10px] tracking-wider uppercase px-2.5 py-1 rounded mb-3">
                    Exclusive Marketplace Deals
                </span>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight leading-tight">
                    BIG SAVINGS<br>EVERY DAY
                </h1>
                <p class="mt-2 text-sm text-orange-100 max-w-sm">
                    Shop genuine smartphones, electronics, fashion, and bags with guaranteed fastest delivery across Bangladesh.
                </p>
            </div>
            <div class="relative z-10 pt-4">
                <a href="{{ route('catalog.index') }}" class="inline-flex items-center px-6 py-2.5 bg-white text-[#F85606] hover:bg-yellow-50 font-black text-sm rounded shadow-md transition-transform transform active:scale-95">
                    SHOP ALL DEALS
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
            <!-- Decorative circles -->
            <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute -top-10 right-16 w-32 h-32 bg-yellow-300/20 rounded-full blur-lg"></div>
        </div>

        <!-- Right: Quick Perks & Promo Card -->
        <div class="hidden lg:flex lg:col-span-3 flex-col justify-between space-y-4">
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-orange-100 text-daraz flex items-center justify-center font-bold">
                        ৳
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-900">Free Shipping</h4>
                        <p class="text-[11px] text-gray-500">On orders over ৳1,500</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-900">100% Authentic</h4>
                        <p class="text-[11px] text-gray-500">Verified brand warranty</p>
                    </div>
                </div>
            </div>

            <!-- Download App Promo Card -->
            <div class="bg-gradient-to-br from-gray-900 to-gray-800 p-4 rounded-lg text-white shadow-sm flex flex-col justify-between flex-1">
                <div>
                    <span class="text-[10px] uppercase font-bold text-yellow-400">Mobile Experience</span>
                    <h3 class="font-bold text-sm mt-1">Daraz App</h3>
                    <p class="text-xs text-gray-400 mt-1">Enjoy exclusive app-only vouchers and flash sale countdowns.</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="mt-3 block text-center py-2 bg-daraz hover:bg-orange-600 rounded text-xs font-bold text-white transition-colors">
                    Explore Storefront
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Flash Sale Section with Live Countdown -->
    <div class="mb-8" x-data="{
        hours: 12,
        minutes: 45,
        seconds: 30,
        startTimer() {
            setInterval(() => {
                if (this.seconds > 0) {
                    this.seconds--;
                } else if (this.minutes > 0) {
                    this.minutes--;
                    this.seconds = 59;
                } else if (this.hours > 0) {
                    this.hours--;
                    this.minutes = 59;
                    this.seconds = 59;
                }
            }, 1000);
        }
    }" x-init="startTimer()">
        <div class="bg-white rounded-t-lg border-b border-gray-100 px-4 py-3 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <span class="text-[#F85606] font-black text-lg tracking-tight uppercase flex items-center">
                    <svg class="w-5 h-5 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path></svg>
                    Flash Sale
                </span>
                <div class="flex items-center space-x-1 text-xs text-gray-500 font-semibold">
                    <span>Ending in:</span>
                    <span class="bg-gray-900 text-white font-mono font-bold px-1.5 py-0.5 rounded text-xs" x-text="String(hours).padStart(2, '0')">12</span>:
                    <span class="bg-gray-900 text-white font-mono font-bold px-1.5 py-0.5 rounded text-xs" x-text="String(minutes).padStart(2, '0')">45</span>:
                    <span class="bg-gray-900 text-white font-mono font-bold px-1.5 py-0.5 rounded text-xs" x-text="String(seconds).padStart(2, '0')">30</span>
                </div>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-daraz hover:underline uppercase">
                Shop More &rarr;
            </a>
        </div>

        <!-- Flash Sale Products Grid -->
        <div class="bg-white rounded-b-lg p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 border border-gray-100 shadow-sm">
            @forelse($flashSales as $product)
                <a href="{{ route('catalog.show', $product->slug) }}" class="group flex flex-col justify-between p-2 rounded hover:shadow-md transition-shadow duration-200 border border-transparent hover:border-gray-100">
                    <div>
                        <div class="relative w-full aspect-square bg-gray-50 rounded overflow-hidden mb-2">
                            @if($product->primaryImage && file_exists(public_path('storage/' . $product->primaryImage->image_path)))
                                <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-orange-50 text-daraz font-black text-2xl">
                                    {{ substr($product->title, 0, 1) }}
                                </div>
                            @endif
                            @if($product->discount_percentage > 0)
                                <span class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-black px-1.5 py-0.5 rounded">
                                    -{{ $product->discount_percentage }}%
                                </span>
                            @endif
                        </div>
                        <h4 class="text-xs text-gray-800 line-clamp-2 group-hover:text-daraz font-medium leading-snug">
                            {{ $product->title }}
                        </h4>
                    </div>
                    <div class="mt-2">
                        <div class="text-sm font-extrabold text-[#F85606]">
                            ৳{{ number_format($product->effective_price) }}
                        </div>
                        @if($product->sale_price && $product->sale_price < $product->price)
                            <div class="text-[11px] text-gray-400 line-through">
                                ৳{{ number_format($product->price) }}
                            </div>
                        @endif
                        <!-- Stock Status Progress -->
                        <div class="mt-1.5 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-400 to-[#F85606] h-1.5 rounded-full" style="width: {{ min(100, max(20, $product->stock)) }}%"></div>
                        </div>
                        <span class="text-[10px] text-gray-500 mt-0.5 block">{{ $product->stock }} left</span>
                    </div>
                </a>
            @empty
                <p class="text-xs text-gray-500 py-4 col-span-6 text-center">No flash sale items currently active.</p>
            @endforelse
        </div>
    </div>

    <!-- 3. Categories Icon Grid -->
    <div class="mb-8">
        <h3 class="text-base font-bold text-gray-800 mb-3">Categories</h3>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
            @foreach($categories as $category)
                <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" class="bg-white rounded-lg p-3 text-center shadow-sm border border-gray-100 hover:shadow-md hover:border-orange-200 transition-all flex flex-col items-center justify-center group">
                    <div class="w-12 h-12 rounded-full bg-orange-50 text-daraz flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <span class="text-xs font-semibold text-gray-700 group-hover:text-daraz line-clamp-1">
                        {{ $category->name }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- 4. "Just For You" Product Feed (Responsive 4-Column Grid) -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-bold text-gray-800">Just For You</h3>
            <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-daraz hover:underline">
                View All Products &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach($justForYou as $product)
                <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-200 flex flex-col justify-between group">
                    <a href="{{ route('catalog.show', $product->slug) }}">
                        <div class="relative w-full aspect-square bg-gray-50 overflow-hidden">
                            @if($product->primaryImage && file_exists(public_path('storage/' . $product->primaryImage->image_path)))
                                <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-orange-50 text-daraz font-black text-3xl">
                                    {{ substr($product->title, 0, 1) }}
                                </div>
                            @endif

                            @if($product->is_featured)
                                <span class="absolute top-2 left-2 bg-[#F85606] text-white text-[10px] font-black px-2 py-0.5 rounded shadow">
                                    CHOICE
                                </span>
                            @endif

                            @if($product->discount_percentage > 0)
                                <span class="absolute top-2 right-2 bg-red-600 text-white text-[10px] font-black px-1.5 py-0.5 rounded">
                                    -{{ $product->discount_percentage }}%
                                </span>
                            @endif
                        </div>

                        <div class="p-3">
                            <h4 class="text-xs text-gray-800 line-clamp-2 font-semibold group-hover:text-daraz mb-2 leading-relaxed">
                                {{ $product->title }}
                            </h4>

                            <div class="flex items-baseline space-x-2">
                                <span class="text-base font-black text-[#F85606]">
                                    ৳{{ number_format($product->effective_price) }}
                                </span>
                                @if($product->sale_price && $product->sale_price < $product->price)
                                    <span class="text-xs text-gray-400 line-through">
                                        ৳{{ number_format($product->price) }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 flex items-center justify-between text-[11px] text-gray-500">
                                <div class="flex items-center text-yellow-500">
                                    <span>★</span>
                                    <span class="ml-1 text-gray-700 font-medium">{{ $product->rating_avg }}</span>
                                    <span class="ml-0.5 text-gray-400">({{ $product->reviews_count }})</span>
                                </div>
                                <span>{{ $product->sold_count }} sold</span>
                            </div>
                        </div>
                    </a>

                    <div class="p-3 pt-0">
                        <a href="{{ route('catalog.show', $product->slug) }}" class="block w-full text-center py-1.5 bg-orange-50 hover:bg-daraz text-daraz hover:text-white font-bold text-xs rounded transition-colors">
                            View Details
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex justify-center">
            {{ $justForYou->links() }}
        </div>
    </div>
</div>
@endsection
