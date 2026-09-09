<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;
    protected ProductVariant $variant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create([
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'title' => 'Test Smartphone',
            'slug' => 'test-smartphone',
            'sku' => 'TEST-PHONE',
            'price' => 20000.00,
            'sale_price' => 18000.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'TEST-PHONE-BLK',
            'price' => 22000.00,
            'sale_price' => 19500.00,
            'stock' => 5,
            'attributes' => ['color' => 'Black'],
        ]);

        $this->user = User::factory()->create();
    }

    public function test_cart_page_renders_empty_state_initially(): void
    {
        $response = $this->actingAs($this->user)->get('/cart');
        $response->assertStatus(200);
        $response->assertSee('There are no items in this cart');
    }

    public function test_user_can_add_item_to_cart(): void
    {
        $response = $this->actingAs($this->user)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 18000.00,
        ]);

        $cartResponse = $this->actingAs($this->user)->get('/cart');
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee('Test Smartphone');
    }

    public function test_user_can_add_variant_to_cart(): void
    {
        $response = $this->actingAs($this->user)->post('/cart/add', [
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
            'price' => 19500.00,
        ]);
    }

    public function test_cannot_exceed_available_inventory_in_cart(): void
    {
        $cartService = app(CartService::class);
        $cartItem = $cartService->addItem($this->product->id, null, 15);

        // Product stock is 10, so quantity must be capped at 10
        $this->assertEquals(10, $cartItem->quantity);
    }

    public function test_can_update_cart_quantity(): void
    {
        $this->actingAs($this->user)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $cartService = app(CartService::class);
        $cart = $cartService->getCart();
        $item = $cart->items->first();

        $response = $this->actingAs($this->user)->patch("/cart/item/{$item->id}", [
            'quantity' => 3,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(3, $item->fresh()->quantity);
    }

    public function test_can_remove_cart_item(): void
    {
        $this->actingAs($this->user)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $cartService = app(CartService::class);
        $cart = $cartService->getCart();
        $item = $cart->items->first();

        $response = $this->actingAs($this->user)->delete("/cart/item/{$item->id}");
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_guest_cart_merges_into_user_cart_on_login(): void
    {
        $guestSessionId = 'guest-test-session-123';
        session()->setId($guestSessionId);

        $cartService = app(CartService::class);
        $cartService->addItem($this->product->id, null, 2);

        $newUser = User::factory()->create();

        // Trigger Login Event
        event(new Login('web', $newUser, false));

        // User cart must now contain the merged item
        $userCart = $newUser->cart;
        $this->assertNotNull($userCart);
        $this->assertCount(1, $userCart->items);
        $this->assertEquals(2, $userCart->items->first()->quantity);
    }

    public function test_customer_can_toggle_wishlist(): void
    {
        // 1. Add to wishlist
        $response = $this->actingAs($this->user)->post('/wishlist/toggle', [
            'product_id' => $this->product->id,
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        // 2. View wishlist
        $wishResponse = $this->actingAs($this->user)->get('/wishlist');
        $wishResponse->assertStatus(200);
        $wishResponse->assertSee('Test Smartphone');

        // 3. Toggle off (remove)
        $toggleOffResponse = $this->actingAs($this->user)->post('/wishlist/toggle', [
            'product_id' => $this->product->id,
        ]);
        $toggleOffResponse->assertSessionHas('info');
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }
}
