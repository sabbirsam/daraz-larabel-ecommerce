<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::active()
            ->with(['category', 'brand', 'primaryImage', 'variants']);

        $currentCategory = null;
        if ($categorySlug = $request->input('category')) {
            $currentCategory = Category::where('slug', $categorySlug)->first();
            if ($currentCategory) {
                $categoryIds = $currentCategory->children()->pluck('id')->prepend($currentCategory->id);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Filter by Brand
        if ($brandSlugs = $request->input('brands')) {
            $brandIds = Brand::whereIn('slug', (array) $brandSlugs)->pluck('id');
            $query->whereIn('brand_id', $brandIds);
        }

        // Filter by Price
        if ($minPrice = $request->input('min_price')) {
            $query->where('price', '>=', (float) $minPrice);
        }
        if ($maxPrice = $request->input('max_price')) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        // Filter by Minimum Rating
        if ($minRating = $request->input('rating')) {
            $query->where('rating_avg', '>=', (float) $minRating);
        }

        // Search Query
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'rating' => $query->orderBy('rating_avg', 'desc'),
            'popular' => $query->orderBy('sold_count', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $products = $query->paginate(16)->withQueryString();

        // Cached filter tree
        $categories = Cache::remember('storefront_category_filters', 3600, function () {
            return Category::root()
                ->active()
                ->with(['children' => fn ($q) => $q->active()])
                ->orderBy('sort_order')
                ->get();
        });

        $brands = Cache::remember('storefront_brand_filters', 3600, function () {
            return Brand::active()->orderBy('name')->get();
        });

        return view('catalog.index', compact(
            'products',
            'currentCategory',
            'categories',
            'brands',
            'search',
            'sort'
        ));
    }

    public function show(string $slug): View
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->with([
                'category',
                'brand',
                'images',
                'variants',
                'reviews' => fn ($q) => $q->with(['user', 'images'])->latest(),
            ])
            ->firstOrFail();

        // Related Products from same category
        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['primaryImage', 'brand'])
            ->take(4)
            ->get();

        // Rating Breakdown Percentages
        $totalReviews = $product->reviews->count();
        $ratingCounts = [
            5 => $product->reviews->where('rating', 5)->count(),
            4 => $product->reviews->where('rating', 4)->count(),
            3 => $product->reviews->where('rating', 3)->count(),
            2 => $product->reviews->where('rating', 2)->count(),
            1 => $product->reviews->where('rating', 1)->count(),
        ];

        $ratingPercentages = [];
        foreach ($ratingCounts as $stars => $count) {
            $ratingPercentages[$stars] = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
        }

        return view('catalog.show', compact(
            'product',
            'relatedProducts',
            'totalReviews',
            'ratingCounts',
            'ratingPercentages'
        ));
    }
}
