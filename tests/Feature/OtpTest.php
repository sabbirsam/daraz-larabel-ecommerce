<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\OtpCode;
use App\Models\Product;
use App\Models\User;
use App\Services\Otp\OtpService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpTest extends TestCase
{
    use RefreshDatabase;

    protected OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->otpService = app(OtpService::class);
    }

    public function test_otp_code_is_generated_with_5_minute_expiry(): void
    {
        $phone = '01712345678';
        $otp = $this->otpService->generate($phone, 'registration');

        $this->assertEquals($phone, $otp->phone);
        $this->assertEquals(6, strlen($otp->code));
        $this->assertEquals('registration', $otp->action);
        $this->assertEquals(0, $otp->attempts);
        $this->assertNull($otp->verified_at);
        $this->assertTrue($otp->expires_at->isFuture());

        $this->assertDatabaseHas('otp_codes', [
            'phone' => $phone,
            'code' => $otp->code,
            'action' => 'registration',
        ]);
    }

    public function test_otp_resend_cooldown_prevents_spamming_within_60_seconds(): void
    {
        $phone = '01712345678';
        $this->otpService->generate($phone, 'registration');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please wait');

        // Immediate subsequent request should throw cooldown exception
        $this->otpService->generate($phone, 'registration');
    }

    public function test_user_can_verify_valid_otp_code(): void
    {
        $user = User::factory()->create([
            'phone' => '01712345678',
            'phone_verified_at' => null,
        ]);

        $otp = $this->otpService->generate($user->phone, 'registration');

        $result = $this->actingAs($user)->otpService->verify($user->phone, $otp->code, 'registration');

        $this->assertTrue($result);
        $this->assertNotNull($otp->fresh()->verified_at);
        $this->assertNotNull($user->fresh()->phone_verified_at);
    }

    public function test_verification_fails_with_invalid_code_and_tracks_attempts(): void
    {
        $phone = '01712345678';
        $otp = $this->otpService->generate($phone, 'registration');

        try {
            $this->otpService->verify($phone, '000000', 'registration');
            $this->fail('Verification should have thrown an exception');
        } catch (Exception $e) {
            $this->assertStringContainsString('Incorrect verification code', $e->getMessage());
        }

        $this->assertEquals(1, $otp->fresh()->attempts);
    }

    public function test_otp_is_locked_after_5_failed_attempts(): void
    {
        $phone = '01712345678';
        $otp = $this->otpService->generate($phone, 'registration');

        $otp->update(['attempts' => 5]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Too many failed attempts');

        $this->otpService->verify($phone, '123456', 'registration');
    }

    public function test_registration_with_phone_redirects_to_phone_verification_screen(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'OTP New User',
            'email' => 'otpuser@daraz.local',
            'phone' => '01755555555',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'otpuser@daraz.local')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->phone_verified_at);

        $response->assertRedirect(route('verification.phone'));
        $this->assertDatabaseHas('otp_codes', [
            'phone' => '01755555555',
            'action' => 'registration',
        ]);
    }

    public function test_unverified_customer_is_redirected_away_from_checkout_to_phone_verification(): void
    {
        $user = User::factory()->create([
            'phone' => '01766666666',
            'phone_verified_at' => null,
        ]);

        $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'T-Shirt',
            'slug' => 't-shirt',
            'sku' => 'TSHIRT-01',
            'price' => 500.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 500.00,
        ]);

        $response = $this->actingAs($user)->get(route('checkout.index'));

        $response->assertRedirect(route('verification.phone'));
        $response->assertSessionHas('warning');
    }

    public function test_verified_customer_can_access_checkout_without_interruption(): void
    {
        $user = User::factory()->create([
            'phone' => '01777777777',
            'phone_verified_at' => now(),
        ]);

        $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion-2', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Jeans',
            'slug' => 'jeans',
            'sku' => 'JEANS-01',
            'price' => 1200.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1200.00,
        ]);

        $response = $this->actingAs($user)->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Delivery Address');
    }

    public function test_customer_can_verify_otp_via_controller(): void
    {
        $user = User::factory()->create([
            'phone' => '01788888888',
            'phone_verified_at' => null,
        ]);

        $otp = $this->otpService->generate($user->phone, 'registration');

        $response = $this->actingAs($user)->post(route('verification.phone.verify'), [
            'code' => $otp->code,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertNotNull($user->fresh()->phone_verified_at);
    }
}
