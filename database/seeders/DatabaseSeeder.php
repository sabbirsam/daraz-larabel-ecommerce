<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Admin;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Super Admin
        Admin::updateOrCreate(
            ['email' => 'admin@daraz.local'],
            [
                'name' => 'Daraz Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // 2. Seed Demo Customer
        $customer = User::updateOrCreate(
            ['email' => 'customer@daraz.local'],
            [
                'name' => 'Demo Customer',
                'phone' => '01700000000',
                'phone_verified_at' => now(),
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        // Seed Customer Default Address
        Address::updateOrCreate(
            ['user_id' => $customer->id, 'phone' => '01700000000'],
            [
                'name' => 'Demo Customer',
                'division' => 'Dhaka',
                'district' => 'Dhaka',
                'upazila' => 'Dhanmondi',
                'address_line' => 'House 42, Road 7, Dhanmondi R/A',
                'is_default_shipping' => true,
                'is_default_billing' => true,
                'type' => 'home',
            ]
        );

        // Seed Sample Daraz Vouchers / Coupons
        $coupons = [
            [
                'code' => 'DARAZ10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_spend' => 500.00,
                'max_discount' => 500.00,
                'usage_limit' => 1000,
                'is_active' => true,
            ],
            [
                'code' => 'SAVE100',
                'type' => 'fixed',
                'value' => 100.00,
                'min_spend' => 1000.00,
                'max_discount' => 100.00,
                'usage_limit' => 500,
                'is_active' => true,
            ],
            [
                'code' => 'DARAZ500',
                'type' => 'fixed',
                'value' => 500.00,
                'min_spend' => 5000.00,
                'max_discount' => 500.00,
                'usage_limit' => 200,
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
        }

        // 3. Seed Default Store Settings
        $settings = [
            ['key' => 'store_name', 'value' => 'Daraz Online Shopping', 'group' => 'general'],
            ['key' => 'store_tagline', 'value' => 'Real Online Shopping Experience', 'group' => 'general'],
            ['key' => 'currency_code', 'value' => 'BDT', 'group' => 'general'],
            ['key' => 'currency_symbol', 'value' => '৳', 'group' => 'general'],
            ['key' => 'support_phone', 'value' => '+880 9612 000000', 'group' => 'contact'],
            ['key' => 'support_email', 'value' => 'support@daraz.local', 'group' => 'contact'],
            ['key' => 'shipping_fee_standard', 'value' => '60.00', 'group' => 'shipping'],
            ['key' => 'free_shipping_threshold', 'value' => '1500.00', 'group' => 'shipping'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // 4. Seed Essential Attributes (Color & Size)
        $colorAttr = Attribute::firstOrCreate(['code' => 'color'], ['name' => 'Color']);
        $colors = [
            ['value' => 'Black', 'color_code' => '#000000', 'sort_order' => 1],
            ['value' => 'White', 'color_code' => '#FFFFFF', 'sort_order' => 2],
            ['value' => 'Red', 'color_code' => '#FF0000', 'sort_order' => 3],
            ['value' => 'Blue', 'color_code' => '#0000FF', 'sort_order' => 4],
            ['value' => 'Daraz Orange', 'color_code' => '#F85606', 'sort_order' => 5],
        ];
        foreach ($colors as $color) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $colorAttr->id, 'value' => $color['value']],
                ['color_code' => $color['color_code'], 'sort_order' => $color['sort_order']]
            );
        }

        $sizeAttr = Attribute::firstOrCreate(['code' => 'size'], ['name' => 'Size']);
        $sizes = [
            ['value' => 'S', 'sort_order' => 1],
            ['value' => 'M', 'sort_order' => 2],
            ['value' => 'L', 'sort_order' => 3],
            ['value' => 'XL', 'sort_order' => 4],
            ['value' => 'XXL', 'sort_order' => 5],
        ];
        foreach ($sizes as $size) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $sizeAttr->id, 'value' => $size['value']],
                ['sort_order' => $size['sort_order']]
            );
        }

        // 5. Seed Core Daraz Categories (Hierarchy)
        $electronics = Category::firstOrCreate(
            ['slug' => 'electronic-devices'],
            ['name' => 'Electronic Devices', 'icon' => 'heroicon-o-cpu-chip', 'sort_order' => 1, 'is_active' => true]
        );

        Category::firstOrCreate(
            ['slug' => 'smartphones'],
            ['parent_id' => $electronics->id, 'name' => 'Smartphones', 'sort_order' => 1, 'is_active' => true]
        );
        Category::firstOrCreate(
            ['slug' => 'laptops-computers'],
            ['parent_id' => $electronics->id, 'name' => 'Laptops & Computers', 'sort_order' => 2, 'is_active' => true]
        );
        Category::firstOrCreate(
            ['slug' => 'cameras-optics'],
            ['parent_id' => $electronics->id, 'name' => 'Cameras & Optics', 'sort_order' => 3, 'is_active' => true]
        );

        $fashion = Category::firstOrCreate(
            ['slug' => 'fashion'],
            ['name' => "Men's & Women's Fashion", 'icon' => 'heroicon-o-sparkles', 'sort_order' => 2, 'is_active' => true]
        );
        Category::firstOrCreate(
            ['slug' => 'bags-luggage'],
            ['parent_id' => $fashion->id, 'name' => 'Bags & Luggage', 'sort_order' => 1, 'is_active' => true]
        );
        Category::firstOrCreate(
            ['slug' => 'shoes-footwear'],
            ['parent_id' => $fashion->id, 'name' => 'Shoes & Footwear', 'sort_order' => 2, 'is_active' => true]
        );

        // 6. Seed Leading Brands
        $brands = [
            ['name' => 'Samsung', 'slug' => 'samsung'],
            ['name' => 'Apple', 'slug' => 'apple'],
            ['name' => 'Xiaomi', 'slug' => 'xiaomi'],
            ['name' => 'Bata', 'slug' => 'bata'],
            ['name' => 'Apex', 'slug' => 'apex'],
        ];
        foreach ($brands as $brand) {
            Brand::firstOrCreate(['slug' => $brand['slug']], ['name' => $brand['name'], 'is_active' => true]);
        }

        // 7. Seed Sample Products with Variants & Specs
        $this->call(ProductSeeder::class);
    }
}
