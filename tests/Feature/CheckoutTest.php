<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;
    protected Product $product;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'phone' => '01711111111',
        ]);

        $this->category = Category::create([
            'name' => 'Shoes',
            'slug' => 'shoes',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'title' => 'Converse Chuck Taylor',
            'slug' => 'converse-chuck-taylor',
            'sku' => 'CONV-001',
            'price' => 2000.00,
            'sale_price' => 1800.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'CONV-001-42-BLK',
            'attributes' => ['size' => '42', 'color' => 'Black'],
            'price' => 1800.00,
            'stock' => 5,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_checkout(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_customer_with_empty_cart_is_redirected_to_cart(): void
    {
        $response = $this->actingAs($this->user)->get(route('checkout.index'));
        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('warning');
    }

    public function test_authenticated_customer_with_cart_can_view_checkout_page(): void
    {
        // Add item to cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
            'price' => 1800.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Delivery Address');
        $response->assertSee('Converse Chuck Taylor');
        $response->assertSee('42, Black');
        $response->assertSee('Order Summary');
        $response->assertSee('Place Order Now');
    }

    public function test_customer_can_save_new_shipping_address(): void
    {
        $response = $this->actingAs($this->user)->post(route('addresses.store'), [
            'name' => 'Rahim Chowdhury',
            'phone' => '01822222222',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'upazila' => 'Mirpur',
            'address_line' => 'Section 10, Block C, House 12',
            'type' => 'home',
            'is_default_shipping' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'name' => 'Rahim Chowdhury',
            'district' => 'Dhaka',
            'is_default_shipping' => true,
        ]);
    }

    public function test_customer_can_apply_valid_coupon(): void
    {
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 1800.00,
        ]);

        Coupon::create([
            'code' => 'PROMO10',
            'type' => 'percentage',
            'value' => 10.00,
            'min_spend' => 1000.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->post(route('checkout.coupon.apply'), [
            'coupon_code' => 'PROMO10',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('applied_coupon', 'PROMO10');
        $response->assertSessionHas('coupon_success');
    }

    public function test_customer_cannot_apply_invalid_coupon(): void
    {
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 500.00,
        ]);

        Coupon::create([
            'code' => 'HIGHSPEND',
            'type' => 'fixed',
            'value' => 200.00,
            'min_spend' => 3000.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->post(route('checkout.coupon.apply'), [
            'coupon_code' => 'HIGHSPEND',
        ]);

        $response->assertRedirect();
        $response->assertSessionMissing('applied_coupon');
        $response->assertSessionHas('coupon_error');
    }

    public function test_customer_can_place_order_and_atomic_stock_is_deducted(): void
    {
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
            'price' => 1800.00,
        ]);

        $address = Address::create([
            'user_id' => $this->user->id,
            'name' => 'Karim Ali',
            'phone' => '01733333333',
            'division' => 'Chattogram',
            'district' => 'Chattogram',
            'address_line' => 'Agrabad C/A',
            'is_default_shipping' => true,
            'type' => 'office',
        ]);

        $initialProductStock = $this->product->stock;
        $initialVariantStock = $this->variant->stock;

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'delivery_method' => 'standard',
            'payment_method' => 'cod',
            'customer_notes' => 'Call before delivery',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(3600.00, $order->subtotal);
        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals('pending', $order->status);

        // Verify order items
        $this->assertCount(1, $order->items);
        $this->assertEquals('Converse Chuck Taylor', $order->items->first()->product_title);
        $this->assertEquals(2, $order->items->first()->quantity);

        // Verify atomic inventory decrements
        $this->assertEquals($initialProductStock - 2, $this->product->fresh()->stock);
        $this->assertEquals($initialVariantStock - 2, $this->variant->fresh()->stock);

        // Verify cart is cleared
        $this->assertEmpty($cart->fresh()->items);

        // Verify redirection to success page
        $response->assertRedirect(route('checkout.success', $order->order_number));
    }

    public function test_order_placement_fails_atomically_if_stock_is_insufficient(): void
    {
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 10, // Variant only has 5 in stock
            'price' => 1800.00,
        ]);

        $address = Address::create([
            'user_id' => $this->user->id,
            'name' => 'Karim Ali',
            'phone' => '01733333333',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'address_line' => 'Banani',
        ]);

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'delivery_method' => 'standard',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify NO order created
        $this->assertDatabaseCount('orders', 0);
        // Verify stock is untouched
        $this->assertEquals(5, $this->variant->fresh()->stock);
        $this->assertEquals(10, $this->product->fresh()->stock);
    }

    public function test_customer_can_view_order_success_page(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-20260909-ABC123',
            'user_id' => $this->user->id,
            'status' => 'pending',
            'subtotal' => 1800.00,
            'discount' => 0.00,
            'shipping_fee' => 60.00,
            'total' => 1860.00,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'shipping_address' => [
                'name' => 'Test Customer',
                'phone' => '01711111111',
                'division' => 'Dhaka',
                'district' => 'Dhaka',
                'address_line' => 'Dhanmondi',
            ],
        ]);

        $response = $this->actingAs($this->user)->get(route('checkout.success', $order->order_number));

        $response->assertStatus(200);
        $response->assertSee('ORD-20260909-ABC123');
        $response->assertSee('Thank you! Your order has been placed.');
        $response->assertSee('Cash on Delivery');
    }

    public function test_customer_cannot_view_another_customers_order_success_page(): void
    {
        $otherUser = User::factory()->create();

        $order = Order::create([
            'order_number' => 'ORD-20260909-XYZ999',
            'user_id' => $otherUser->id,
            'status' => 'pending',
            'subtotal' => 1000.00,
            'discount' => 0.00,
            'shipping_fee' => 60.00,
            'total' => 1060.00,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'shipping_address' => ['name' => 'Other Person'],
        ]);

        $response = $this->actingAs($this->user)->get(route('checkout.success', $order->order_number));

        $response->assertStatus(404);
    }
}
