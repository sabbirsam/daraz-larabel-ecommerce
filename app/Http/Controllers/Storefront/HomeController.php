<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        // 1. Cached Category Tree for sidebar & mega-menu (1 hour cache)
        $categories = Cache::remember('storefront_categories', 3600, function () {
            return Category::root()
                ->active()
                ->with(['children' => fn ($q) => $q->active()])
                ->orderBy('sort_order')
                ->get();
        });

        // 2. Flash Sale Products (Featured items with sale prices)
        $flashSales = Product::active()
            ->where('is_featured', true)
            ->with(['category', 'brand', 'primaryImage', 'variants'])
            ->orderBy('sold_count', 'desc')
            ->take(6)
            ->get();

        // 3. Just For You Product Grid (Paginated high-throughput feed)
        $justForYou = Product::active()
            ->with(['category', 'brand', 'primaryImage'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // 4. Featured Brands
        $brands = Cache::remember('storefront_featured_brands', 3600, function () {
            return Brand::active()->take(8)->get();
        });

        return view('home', compact('categories', 'flashSales', 'justForYou', 'brands'));
    }
}
