@extends('layouts.storefront')

@section('title', 'My Wishlist - Daraz Bangladesh')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Breadcrumb Navigation -->
    <nav class="flex text-xs text-gray-500 mb-4 items-center space-x-2">
        <a href="{{ route('home') }}" class="hover:text-daraz">Home</a>
        <span>/</span>
        <span class="text-gray-800 font-semibold">My Wishlist</span>
    </nav>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <span>My Wishlist</span>
            <span class="text-xs font-normal text-gray-500">({{ $wishlists->total() }} saved items)</span>
        </h1>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-xs px-4 py-3 rounded-lg flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('info'))
        <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 text-xs px-4 py-3 rounded-lg flex items-center justify-between">
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if($wishlists->count() > 0)
        <!-- Wishlist Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach($wishlists as $wish)
                @if($wish->product)
                    <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-shadow flex flex-col justify-between group relative">
                        <a href="{{ route('catalog.show', $wish->product->slug) }}">
                            <div class="relative w-full aspect-square bg-gray-50 overflow-hidden">
                                @if($wish->product->primaryImage && file_exists(public_path('storage/' . $wish->product->primaryImage->image_path)))
                                    <img src="{{ asset('storage/' . $wish->product->primaryImage->image_path) }}" alt="{{ $wish->product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-orange-50 text-daraz font-black text-3xl">
                                        {{ substr($wish->product->title, 0, 1) }}
                                    </div>
                                @endif

                                @if($wish->product->discount_percentage > 0)
                                    <span class="absolute top-2 right-2 bg-red-600 text-white text-[10px] font-black px-1.5 py-0.5 rounded">
                                        -{{ $wish->product->discount_percentage }}%
                                    </span>
                                @endif
                            </div>

                            <div class="p-3">
                                <h4 class="text-xs text-gray-800 line-clamp-2 font-semibold group-hover:text-daraz mb-2 leading-relaxed">
                                    {{ $wish->product->title }}
                                </h4>

                                <div class="flex items-baseline space-x-2">
                                    <span class="text-base font-black text-[#F85606]">
                                        ৳{{ number_format($wish->product->effective_price) }}
                                    </span>
                                    @if($wish->product->sale_price && $wish->product->sale_price < $wish->product->price)
                                        <span class="text-xs text-gray-400 line-through">
                                            ৳{{ number_format($wish->product->price) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-2 text-[11px] font-semibold {{ $wish->product->stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $wish->product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                </div>
                            </div>
                        </a>

                        <div class="p-3 pt-0 flex gap-2 items-center">
                            @if($wish->product->stock > 0)
                                <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $wish->product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="w-full py-1.5 bg-daraz hover:bg-daraz-hover text-white font-bold text-xs rounded transition-colors text-center">
                                        Add to Cart
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('wishlist.remove', $wish->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 rounded transition-colors" title="Remove from wishlist">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-6 flex justify-center">
            {{ $wishlists->links() }}
        </div>
    @else
        <!-- Empty Wishlist State -->
        <div class="bg-white rounded-lg p-12 text-center shadow-sm border border-gray-100 max-w-xl mx-auto my-8">
            <div class="w-20 h-20 mx-auto rounded-full bg-orange-50 text-daraz flex items-center justify-center mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">There are no favorites saved</h3>
            <p class="text-xs text-gray-500 mb-6">Explore our catalog and tap the heart icon on items you love.</p>
            <a href="{{ route('catalog.index') }}" class="inline-block px-8 py-3 bg-daraz hover:bg-daraz-hover text-white text-xs font-extrabold uppercase tracking-wider rounded shadow transition-colors">
                Explore Marketplace
            </a>
        </div>
    @endif
</div>
@endsection
