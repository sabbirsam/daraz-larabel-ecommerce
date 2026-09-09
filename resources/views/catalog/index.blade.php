@extends('layouts.storefront')

@section('title', ($currentCategory ? $currentCategory->name . ' - ' : ($search ? 'Search: ' . $search . ' - ' : '')) . 'Daraz Online Shopping')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">

    <!-- Breadcrumb Navigation -->
    <nav class="flex text-xs text-gray-500 mb-4 items-center space-x-2">
        <a href="{{ route('home') }}" class="hover:text-daraz">Home</a>
        <span>/</span>
        @if($currentCategory)
            @if($currentCategory->parent)
                <a href="{{ route('catalog.index', ['category' => $currentCategory->parent->slug]) }}" class="hover:text-daraz">
                    {{ $currentCategory->parent->name }}
                </a>
                <span>/</span>
            @endif
            <span class="text-gray-800 font-semibold">{{ $currentCategory->name }}</span>
        @elseif($search)
            <span class="text-gray-800 font-semibold">Search results for "{{ $search }}"</span>
        @else
            <span class="text-gray-800 font-semibold">All Products</span>
        @endif
    </nav>

    <!-- Main Two-Column Layout (Content Left: Filters, Right: Product Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT SIDEBAR: FILTERS -->
        <aside class="lg:col-span-3">
            <form action="{{ route('catalog.index') }}" method="GET" id="filter-form" class="space-y-4">
                @if($search)
                    <input type="hidden" name="q" value="{{ $search }}">
                @endif
                @if($currentCategory)
                    <input type="hidden" name="category" value="{{ $currentCategory->slug }}">
                @endif

                <!-- 1. Category Tree Filter -->
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 text-xs">
                    <h3 class="font-bold text-gray-900 text-sm mb-3 border-b pb-2">Category</h3>
                    <ul class="space-y-1.5 max-h-60 overflow-y-auto">
                        <li class="{{ !$currentCategory ? 'font-bold text-daraz' : 'text-gray-700' }}">
                            <a href="{{ route('catalog.index', array_merge(request()->except('category'), ['category' => null])) }}" class="hover:text-daraz block">
                                All Categories
                            </a>
                        </li>
                        @foreach($categories as $category)
                            <li class="{{ $currentCategory && is_object($currentCategory) && $currentCategory->id === $category->id ? 'font-bold text-daraz' : 'text-gray-700' }}">
                                <a href="{{ route('catalog.index', array_merge(request()->except('category'), ['category' => $category->slug])) }}" class="hover:text-daraz flex items-center justify-between">
                                    <span>{{ $category->name }}</span>
                                    <span class="text-[10px] text-gray-400">({{ $category->products_count ?? $category->products()->count() }})</span>
                                </a>
                                @if($category->children->count() > 0)
                                    <ul class="pl-3 mt-1 space-y-1 border-l border-gray-100">
                                        @foreach($category->children as $child)
                                            <li class="{{ $currentCategory && is_object($currentCategory) && $currentCategory->id === $child->id ? 'font-bold text-daraz' : 'text-gray-600' }}">
                                                <a href="{{ route('catalog.index', array_merge(request()->except('category'), ['category' => $child->slug])) }}" class="hover:text-daraz flex items-center justify-between text-[11px]">
                                                    <span>{{ $child->name }}</span>
                                                    <span class="text-[10px] text-gray-400">({{ $child->products()->count() }})</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- 2. Brand Filter -->
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 text-xs">
                    <h3 class="font-bold text-gray-900 text-sm mb-3 border-b pb-2">Brand</h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($brands as $brand)
                            <label class="flex items-center space-x-2 text-gray-700 hover:text-daraz cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="brands[]"
                                    value="{{ $brand->slug }}"
                                    {{ in_array($brand->slug, (array) request('brands', [])) ? 'checked' : '' }}
                                    onchange="document.getElementById('filter-form').submit()"
                                    class="rounded border-gray-300 text-daraz focus:ring-daraz"
                                >
                                <span>{{ $brand->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- 3. Price Range Filter -->
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 text-xs">
                    <h3 class="font-bold text-gray-900 text-sm mb-3 border-b pb-2">Price Range (BDT)</h3>
                    <div class="flex items-center space-x-2">
                        <input
                            type="number"
                            name="min_price"
                            value="{{ request('min_price') }}"
                            placeholder="Min"
                            class="w-full text-xs p-1.5 border border-gray-300 rounded focus:border-daraz focus:ring-daraz"
                        >
                        <span class="text-gray-400">-</span>
                        <input
                            type="number"
                            name="max_price"
                            value="{{ request('max_price') }}"
                            placeholder="Max"
                            class="w-full text-xs p-1.5 border border-gray-300 rounded focus:border-daraz focus:ring-daraz"
                        >
                        <button type="submit" class="bg-daraz hover:bg-orange-600 text-white px-3 py-1.5 rounded font-bold">
                            &gt;
                        </button>
                    </div>
                </div>

                <!-- 4. Rating Filter -->
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 text-xs">
                    <h3 class="font-bold text-gray-900 text-sm mb-3 border-b pb-2">Rating</h3>
                    <div class="space-y-2">
                        @foreach([5, 4, 3] as $stars)
                            <label class="flex items-center space-x-2 text-gray-700 hover:text-daraz cursor-pointer">
                                <input
                                    type="radio"
                                    name="rating"
                                    value="{{ $stars }}"
                                    {{ request('rating') == $stars ? 'checked' : '' }}
                                    onchange="document.getElementById('filter-form').submit()"
                                    class="border-gray-300 text-daraz focus:ring-daraz"
                                >
                                <div class="flex text-yellow-400">
                                    @for($i = 0; $i < $stars; $i++) ★ @endfor
                                </div>
                                <span class="text-[11px] text-gray-500">& above</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if(request()->hasAny(['brands', 'min_price', 'max_price', 'rating', 'category']))
                    <a href="{{ route('catalog.index') }}" class="block text-center py-2 bg-gray-200 hover:bg-gray-300 rounded text-xs font-semibold text-gray-700 transition-colors">
                        Clear All Filters
                    </a>
                @endif
            </form>
        </aside>

        <!-- RIGHT MAIN COLUMN: PRODUCT GRID -->
        <div class="lg:col-span-9">

            <!-- Filter & Sort Header -->
            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 mb-4 flex flex-wrap items-center justify-between gap-4 text-xs">
                <div>
                    <span class="text-gray-500">{{ $products->total() }} items found for</span>
                    <span class="font-bold text-gray-900 ml-1">
                        "{{ $currentCategory ? $currentCategory->name : ($search ? $search : 'All Products') }}"
                    </span>
                </div>

                <!-- Sorting Dropdown -->
                <div class="flex items-center space-x-2">
                    <label for="sort-select" class="text-gray-500">Sort By:</label>
                    <select
                        id="sort-select"
                        onchange="const url = new URL(window.location.href); url.searchParams.set('sort', this.value); window.location.href = url.toString();"
                        class="text-xs py-1 px-3 border border-gray-300 rounded focus:border-daraz focus:ring-daraz bg-white"
                    >
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest Arrivals</option>
                        <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Best Match / Popularity</option>
                        <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>Price Low to High</option>
                        <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>Price High to Low</option>
                        <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Customer Rating</option>
                    </select>
                </div>
            </div>

            <!-- Product Grid (4 Columns on Desktop) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @forelse($products as $product)
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
                @empty
                    <div class="col-span-4 bg-white p-12 text-center rounded-lg shadow-sm">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h4 class="text-base font-bold text-gray-800 mb-1">No products found</h4>
                        <p class="text-xs text-gray-500">Try adjusting your filters or search keywords.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination Links -->
            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
