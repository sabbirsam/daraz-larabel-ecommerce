<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Payment\PaymentManager;
use App\Services\Payment\SSLCommerzPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Payment Tester',
            'email' => 'paytester@daraz.local',
            'phone' => '01799999999',
        ]);

        $this->order = Order::create([
            'order_number' => 'ORD-20260909-PAY001',
            'user_id' => $this->user->id,
            'status' => 'pending',
            'subtotal' => 2500.00,
            'discount' => 0.00,
            'shipping_fee' => 0.00,
            'total' => 2500.00,
            'payment_method' => 'sslcommerz',
            'payment_status' => 'unpaid',
            'shipping_address' => [
                'name' => 'Payment Tester',
                'phone' => '01799999999',
                'division' => 'Dhaka',
                'district' => 'Dhaka',
                'address_line' => 'Gulshan 2',
            ],
        ]);
    }

    public function test_payment_manager_resolves_sslcommerz_gateway(): void
    {
        $manager = app(PaymentManager::class);
        $gateway = $manager->resolve('sslcommerz');

        $this->assertInstanceOf(PaymentGatewayInterface::class, $gateway);
        $this->assertInstanceOf(SSLCommerzPaymentGateway::class, $gateway);
    }

    public function test_sslcommerz_initiate_returns_redirect_url_for_order(): void
    {
        $gateway = app(SSLCommerzPaymentGateway::class);
        $result = $gateway->initiate($this->order);

        $this->assertEquals('success', $result['status']);
        $this->assertNotEmpty($result['redirect_url']);
        $this->assertEquals('ORD-20260909-PAY001', $result['tran_id']);
    }

    public function test_checkout_with_sslcommerz_creates_initiated_payment_and_redirects(): void
    {
        $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Wireless Headphones',
            'slug' => 'wireless-headphones',
            'sku' => 'WH-001',
            'price' => 2500.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 2500.00,
        ]);

        $address = Address::create([
            'user_id' => $this->user->id,
            'name' => 'Payment Tester',
            'phone' => '01799999999',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'address_line' => 'Gulshan 2',
            'is_default_shipping' => true,
        ]);

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'delivery_method' => 'standard',
            'payment_method' => 'sslcommerz',
        ]);

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('sslcommerz', $order->payment_method);
        $this->assertEquals('unpaid', $order->payment_status);

        // Verify Payment record was created
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'gateway' => 'sslcommerz',
            'status' => 'initiated',
            'amount' => $order->total,
        ]);

        // Redirects to gateway or sandbox simulator
        $response->assertRedirect();
    }

    public function test_sslcommerz_simulator_page_renders_for_order(): void
    {
        $response = $this->get(route('payment.sslcommerz.simulator', $this->order->order_number));

        $response->assertStatus(200);
        $response->assertSee('SSLCOMMERZ');
        $response->assertSee('Sandbox Mode');
        $response->assertSee('ORD-20260909-PAY001');
        $response->assertSee('Simulate Successful Payment via bKash');
    }

    public function test_sslcommerz_success_callback_marks_order_as_paid_and_payment_as_completed(): void
    {
        $response = $this->post(route('payment.sslcommerz.success'), [
            'tran_id' => $this->order->order_number,
            'val_id' => 'VAL-TEST-SUCCESS-1234',
            'amount' => '2500.00',
            'card_type' => 'bKash-MobileBanking',
            'status' => 'VALID',
            'currency' => 'BDT',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Payment Successful!');
        $response->assertSee('VAL-TEST-SUCCESS-1234');

        $this->order->refresh();
        $this->assertEquals('paid', $this->order->payment_status);
        $this->assertEquals('processing', $this->order->status);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'gateway' => 'sslcommerz',
            'transaction_id' => 'VAL-TEST-SUCCESS-1234',
            'status' => 'completed',
        ]);
    }

    public function test_sslcommerz_fail_callback_marks_order_and_payment_as_failed(): void
    {
        $response = $this->post(route('payment.sslcommerz.fail'), [
            'tran_id' => $this->order->order_number,
            'status' => 'FAILED',
            'error' => 'Bank server timed out',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Payment Unsuccessful');

        $this->order->refresh();
        $this->assertEquals('failed', $this->order->payment_status);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'gateway' => 'sslcommerz',
            'status' => 'failed',
        ]);
    }

    public function test_sslcommerz_cancel_callback_marks_order_and_payment_as_cancelled(): void
    {
        $response = $this->post(route('payment.sslcommerz.cancel'), [
            'tran_id' => $this->order->order_number,
            'status' => 'CANCELLED',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Payment Cancelled');

        $this->order->refresh();
        $this->assertEquals('cancelled', $this->order->payment_status);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'gateway' => 'sslcommerz',
            'status' => 'cancelled',
        ]);
    }

    public function test_sslcommerz_ipn_webhook_updates_order_and_payment_asynchronously(): void
    {
        $response = $this->post(route('payment.sslcommerz.ipn'), [
            'tran_id' => $this->order->order_number,
            'val_id' => 'VAL-IPN-5678',
            'amount' => '2500.00',
            'card_type' => 'VISA',
            'status' => 'VALID',
        ]);

        $response->assertStatus(200);
        $response->assertSee('IPN Processed Successfully');

        $this->order->refresh();
        $this->assertEquals('paid', $this->order->payment_status);
        $this->assertEquals('processing', $this->order->status);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'gateway' => 'sslcommerz',
            'transaction_id' => 'VAL-IPN-5678',
            'status' => 'completed',
        ]);
    }
}
