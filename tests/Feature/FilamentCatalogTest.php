<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_catalog(): void
    {
        $response = $this->get('/admin/products');
        $response->assertRedirect('/admin/login');
    }

    public function test_customer_cannot_access_admin_catalog(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($customer, 'web')->get('/admin/products');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_admin_catalog_pages(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@daraz.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')->get('/admin/products')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/categories')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/brands')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/attributes')->assertStatus(200);
    }
}
