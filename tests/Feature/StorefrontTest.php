<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create([
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'is_active' => true,
        ]);

        $brand = Brand::create([
            'name' => 'Samsung',
            'slug' => 'samsung',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'title' => 'Samsung Galaxy S24 Ultra',
            'slug' => 'samsung-galaxy-s24-ultra',
            'sku' => 'SAM-S24U',
            'price' => 150000.00,
            'sale_price' => 140000.00,
            'stock' => 20,
            'is_active' => true,
            'is_featured' => true,
        ]);
    }

    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('BIG SAVINGS');
        $response->assertSee('Samsung Galaxy S24 Ultra');
    }

    public function test_catalog_listing_page_loads_successfully(): void
    {
        $response = $this->get('/products');
        $response->assertStatus(200);
        $response->assertSee('Samsung Galaxy S24 Ultra');
    }

    public function test_catalog_category_filter_works(): void
    {
        $response = $this->get('/products?category=smartphones');
        $response->assertStatus(200);
        $response->assertSee('Samsung Galaxy S24 Ultra');
    }

    public function test_catalog_brand_filter_works(): void
    {
        $response = $this->get('/products?brands[]=samsung');
        $response->assertStatus(200);
        $response->assertSee('Samsung Galaxy S24 Ultra');
    }

    public function test_catalog_search_works(): void
    {
        $response = $this->get('/products?q=Galaxy');
        $response->assertStatus(200);
        $response->assertSee('Samsung Galaxy S24 Ultra');
    }

    public function test_single_product_page_loads_successfully(): void
    {
        $response = $this->get('/product/samsung-galaxy-s24-ultra');
        $response->assertStatus(200);
        $response->assertSee('Samsung Galaxy S24 Ultra');
        $response->assertSee('Standard Delivery');
        $response->assertSee('Buy Now');
        $response->assertSee('Add to Cart');
    }

    public function test_single_product_404_on_invalid_slug(): void
    {
        $response = $this->get('/product/non-existent-product-slug');
        $response->assertStatus(404);
    }
}
