<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Daraz Bangladesh - Online Shopping Mall')</title>
    <meta name="description" content="@yield('meta_description', 'Daraz Bangladesh - Online Shopping for Electronics, Fashion, Home Appliances, Mobiles, Tablets and more at best prices.')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .daraz-orange { background-color: #F85606; }
        .text-daraz { color: #F85606; }
        .border-daraz { border-color: #F85606; }
    </style>
    @livewireStyles
</head>
<body class="bg-[#F5F5F5] text-[#212121] antialiased flex flex-col min-h-screen">

    <!-- 1. Top Mini Navigation Bar -->
    <header class="bg-[#F85606] text-white text-xs border-b border-[#E04C04]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-1.5 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <span class="hover:underline cursor-pointer hidden md:inline">Save More on App</span>
                <span class="hover:underline cursor-pointer hidden md:inline">Become a Seller</span>
                <span class="hover:underline cursor-pointer">Help & Support</span>
            </div>
            <div class="flex items-center space-x-4">
                @auth
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center space-x-1 font-semibold hover:underline focus:outline-none">
                            <span>Hello, {{ Auth::user()->name }}</span>
                            <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded shadow-lg py-2 z-50">
                            <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-orange-50 hover:text-daraz">My Account</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-orange-50 hover:text-daraz">Profile Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-orange-50 hover:text-daraz">Log Out</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hover:underline font-semibold">Login</a>
                    <span class="text-white/60">|</span>
                    <a href="{{ route('register') }}" class="hover:underline font-semibold">Sign Up</a>
                @endauth
            </div>
        </div>

        <!-- 2. Main Header Bar (Logo, Search, Cart) -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between gap-4 md:gap-8">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <div class="w-9 h-9 rounded-lg bg-white flex items-center justify-center text-[#F85606] font-black text-2xl shadow-sm">
                    d
                </div>
                <span class="text-2xl font-black tracking-tight text-white hidden sm:inline">
                    daraz<span class="text-yellow-300">.</span>com.bd
                </span>
            </a>

            <!-- Central Search Bar -->
            <div class="flex-1 max-w-2xl">
                <form action="{{ route('catalog.index') }}" method="GET" class="flex items-center">
                    <div class="relative w-full">
                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Search in Daraz (e.g. Smartphone, Camera, Shoes...)"
                            class="w-full py-2.5 pl-4 pr-12 text-sm text-gray-900 bg-white rounded-l focus:outline-none focus:ring-0 border-0 shadow-inner"
                        >
                    </div>
                    <button type="submit" class="bg-[#D03B00] hover:bg-[#B33300] text-white px-6 py-2.5 rounded-r flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
            </div>

            <!-- Header Action Badges (Wishlist & Cart) -->
            <div class="flex items-center space-x-6 shrink-0">
                <!-- Wishlist Icon -->
                @auth
                    <a href="{{ route('wishlist.index') }}" class="relative flex items-center text-white hover:text-yellow-200 transition-colors" title="My Wishlist">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        <span class="absolute -top-1.5 -right-2 bg-yellow-400 text-gray-900 text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center shadow">
                            {{ Auth::user()->wishlists()->count() }}
                        </span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="relative flex items-center text-white hover:text-yellow-200 transition-colors" title="My Wishlist">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    </a>
                @endauth

                <!-- Cart Icon -->
                <a href="{{ route('cart.index') }}" class="relative flex items-center text-white hover:text-yellow-200 transition-colors" title="Shopping Cart">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="absolute -top-1.5 -right-2.5 bg-yellow-400 text-gray-900 text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center shadow">
                        {{ session('cart_count', 0) }}
                    </span>
                </a>
            </div>
        </div>
    </header>

    <!-- 3. Category Strip Navigation -->
    <nav class="bg-white border-b border-gray-200 shadow-sm text-xs font-semibold">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center space-x-8 h-10 overflow-x-auto">
            <a href="{{ route('catalog.index') }}" class="flex items-center text-daraz hover:text-[#E04C04] whitespace-nowrap">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                All Categories
            </a>
            <a href="{{ route('catalog.index', ['category' => 'electronic-devices']) }}" class="text-gray-700 hover:text-daraz whitespace-nowrap">Electronic Devices</a>
            <a href="{{ route('catalog.index', ['category' => 'smartphones']) }}" class="text-gray-700 hover:text-daraz whitespace-nowrap">Smartphones</a>
            <a href="{{ route('catalog.index', ['category' => 'laptops-computers']) }}" class="text-gray-700 hover:text-daraz whitespace-nowrap">Laptops</a>
            <a href="{{ route('catalog.index', ['category' => 'cameras-optics']) }}" class="text-gray-700 hover:text-daraz whitespace-nowrap">Cameras</a>
            <a href="{{ route('catalog.index', ['category' => 'fashion']) }}" class="text-gray-700 hover:text-daraz whitespace-nowrap">Fashion</a>
            <a href="{{ route('catalog.index', ['category' => 'bags-luggage']) }}" class="text-gray-700 hover:text-daraz whitespace-nowrap">Bags & Travel</a>
            <a href="{{ route('catalog.index', ['category' => 'shoes-footwear']) }}" class="text-gray-700 hover:text-daraz whitespace-nowrap">Shoes</a>
        </div>
    </nav>

    <!-- Main Content Slot -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#2E2E2E] text-gray-300 text-xs mt-12 pt-10 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 pb-8 border-b border-gray-700">
                <div>
                    <h4 class="text-white font-bold text-sm mb-3">Customer Care</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white">Help Center</a></li>
                        <li><a href="#" class="hover:text-white">How to Buy</a></li>
                        <li><a href="#" class="hover:text-white">Returns & Refunds</a></li>
                        <li><a href="#" class="hover:text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-sm mb-3">Daraz Bangladesh</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white">About Daraz</a></li>
                        <li><a href="#" class="hover:text-white">Careers</a></li>
                        <li><a href="#" class="hover:text-white">Daraz Blog</a></li>
                        <li><a href="#" class="hover:text-white">Terms & Conditions</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-sm mb-3">Payment Methods</h4>
                    <div class="flex flex-wrap gap-2 text-[11px]">
                        <span class="bg-gray-800 text-pink-400 font-bold px-2 py-1 rounded border border-gray-700">bKash</span>
                        <span class="bg-gray-800 text-orange-400 font-bold px-2 py-1 rounded border border-gray-700">Nagad</span>
                        <span class="bg-gray-800 text-purple-400 font-bold px-2 py-1 rounded border border-gray-700">Rocket</span>
                        <span class="bg-gray-800 text-blue-400 font-bold px-2 py-1 rounded border border-gray-700">VISA / MasterCard</span>
                        <span class="bg-gray-800 text-green-400 font-bold px-2 py-1 rounded border border-gray-700">Cash on Delivery</span>
                    </div>
                </div>
                <div>
                    <h4 class="text-white font-bold text-sm mb-3">Verified Safe Shopping</h4>
                    <p class="text-gray-400 leading-relaxed">
                        100% Authentic Products with 7-Day Easy Returns and 24/7 Customer Care.
                    </p>
                </div>
            </div>
            <div class="mt-6 text-center text-gray-500">
                <p>&copy; {{ date('Y') }} Daraz Bangladesh Clone. High-Concurrency Laravel 12 Architecture.</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
