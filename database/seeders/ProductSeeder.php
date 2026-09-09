<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $smartphoneCat = Category::where('slug', 'smartphones')->first() ?? Category::first();
        $laptopCat = Category::where('slug', 'laptops-computers')->first() ?? Category::first();
        $camerasCat = Category::where('slug', 'cameras-optics')->first() ?? Category::first();
        $bagsCat = Category::where('slug', 'bags-luggage')->first() ?? Category::first();
        $shoesCat = Category::where('slug', 'shoes-footwear')->first() ?? Category::first();

        $samsung = Brand::where('slug', 'samsung')->first();
        $apple = Brand::where('slug', 'apple')->first();
        $xiaomi = Brand::where('slug', 'xiaomi')->first();
        $bata = Brand::where('slug', 'bata')->first();
        $apex = Brand::where('slug', 'apex')->first();

        $productsData = [
            [
                'title' => 'Samsung Galaxy S24 Ultra 5G Smartphone',
                'category_id' => $smartphoneCat->id,
                'brand_id' => $samsung?->id,
                'sku' => 'SAM-S24U-TITAN',
                'price' => 159999.00,
                'sale_price' => 149999.00,
                'stock' => 45,
                'short_description' => 'Snapdragon 8 Gen 3 processor, 200MP Quad Telephoto camera with AI zoom, Titanium frame, and built-in S Pen.',
                'description' => '<p><strong>Welcome to the era of mobile AI.</strong> With Galaxy S24 Ultra in your hands, you can unleash whole new levels of creativity, productivity, and possibility.</p><ul><li>6.8-inch Dynamic AMOLED 2X Display (120Hz)</li><li>Armor Aluminum frame with Corning Gorilla Armor</li><li>IP68 dust and water resistance</li><li>5,000 mAh battery with 45W fast charging</li></ul>',
                'specifications' => [
                    'Display' => '6.8 inch QHD+ Dynamic AMOLED 2X 120Hz',
                    'Processor' => 'Snapdragon 8 Gen 3 for Galaxy',
                    'RAM & Storage' => '12GB RAM, 256GB / 512GB ROM',
                    'Rear Camera' => '200MP + 50MP + 12MP + 10MP',
                    'Battery' => '5000 mAh with 45W Fast Charging',
                    'Operating System' => 'Android 14, One UI 6.1 with Galaxy AI',
                ],
                'is_featured' => true,
                'sold_count' => 142,
                'rating_avg' => 4.90,
                'reviews_count' => 38,
                'variants' => [
                    [
                        'sku' => 'SAM-S24U-256-BLK',
                        'price' => 159999.00,
                        'sale_price' => 149999.00,
                        'stock' => 20,
                        'attributes' => ['color' => 'Titanium Black', 'storage' => '256GB'],
                    ],
                    [
                        'sku' => 'SAM-S24U-512-GRY',
                        'price' => 179999.00,
                        'sale_price' => 169999.00,
                        'stock' => 15,
                        'attributes' => ['color' => 'Titanium Gray', 'storage' => '512GB'],
                    ],
                    [
                        'sku' => 'SAM-S24U-256-YLW',
                        'price' => 159999.00,
                        'sale_price' => 149999.00,
                        'stock' => 10,
                        'attributes' => ['color' => 'Titanium Yellow', 'storage' => '256GB'],
                    ],
                ],
            ],
            [
                'title' => 'Apple MacBook Air 13.6" M3 Chip (16GB / 512GB)',
                'category_id' => $laptopCat->id,
                'brand_id' => $apple?->id,
                'sku' => 'APP-MBA-M3-SLV',
                'price' => 175000.00,
                'sale_price' => 164500.00,
                'stock' => 25,
                'short_description' => 'Blazingly fast M3 chip, striking 13.6-inch Liquid Retina display, all-day 18 hours battery life, in an impossibly thin aluminum enclosure.',
                'description' => '<p>The world\'s most popular laptop is better than ever with even more performance. Built for Apple Intelligence with up to 18 hours of battery life.</p>',
                'specifications' => [
                    'Chip' => 'Apple M3 chip (8-core CPU, 10-core GPU)',
                    'Memory' => '16GB Unified Memory',
                    'Storage' => '512GB SSD Storage',
                    'Display' => '13.6-inch Liquid Retina with True Tone',
                    'Battery' => 'Up to 18 hours battery life',
                    'Weight' => '1.24 kg',
                ],
                'is_featured' => true,
                'sold_count' => 87,
                'rating_avg' => 4.85,
                'reviews_count' => 24,
                'variants' => [
                    [
                        'sku' => 'APP-MBA-M3-MID',
                        'price' => 175000.00,
                        'sale_price' => 164500.00,
                        'stock' => 15,
                        'attributes' => ['color' => 'Midnight', 'memory' => '16GB'],
                    ],
                    [
                        'sku' => 'APP-MBA-M3-SLV',
                        'price' => 175000.00,
                        'sale_price' => 164500.00,
                        'stock' => 10,
                        'attributes' => ['color' => 'Silver', 'memory' => '16GB'],
                    ],
                ],
            ],
            [
                'title' => 'Imou Ranger 2 4MP WiFi Smart Security Camera 360°',
                'category_id' => $camerasCat->id,
                'brand_id' => $xiaomi?->id,
                'sku' => 'IMOU-RANG2-4MP',
                'price' => 3800.00,
                'sale_price' => 2850.00,
                'stock' => 120,
                'short_description' => 'Human detection, 1080P/4MP QHD video, 360-degree pan & tilt coverage, built-in siren, two-way talk, smart tracking.',
                'description' => '<p>With 4MP QHD live monitoring and 0~355° pan & -5~80° tilt features, Ranger 2 ensures every corner of your home completely covered. Human Detection quickly finds human targets in images.</p>',
                'specifications' => [
                    'Resolution' => '4MP QHD (2560 x 1440)',
                    'Coverage' => '355° Pan, -5~80° Tilt, 16x Digital Zoom',
                    'Night Vision' => '10m (33ft) Distance',
                    'Audio' => 'Two-way Talk (Built-in Mic & Speaker)',
                    'Storage' => 'Micro SD Card up to 256GB / Cloud Storage',
                ],
                'is_featured' => true,
                'sold_count' => 520,
                'rating_avg' => 4.75,
                'reviews_count' => 112,
                'variants' => [],
            ],
            [
                'title' => 'Anti-Theft Waterproof Business Travel Backpack with USB Charging Port',
                'category_id' => $bagsCat->id,
                'brand_id' => null,
                'sku' => 'BAG-ANTITH-001',
                'price' => 2200.00,
                'sale_price' => 1450.00,
                'stock' => 80,
                'short_description' => 'Premium Oxford fabric, water-repellent, hidden zipper anti-theft design, laptop compartment up to 15.6 inches.',
                'description' => '<p>Equipped with a built-in USB charging port outside and built-in charging cable inside. Multi-compartment design keeps your digital accessories organized.</p>',
                'specifications' => [
                    'Material' => 'Waterproof High-density Oxford Fabric',
                    'Capacity' => '20-35 Litres',
                    'Laptop Fit' => 'Up to 15.6 inch laptop',
                    'Features' => 'Anti-theft hidden pockets, USB Port, Ergonomic strap',
                ],
                'is_featured' => true,
                'sold_count' => 340,
                'rating_avg' => 4.65,
                'reviews_count' => 67,
                'variants' => [
                    [
                        'sku' => 'BAG-ANTITH-BLK',
                        'price' => 2200.00,
                        'sale_price' => 1450.00,
                        'stock' => 50,
                        'attributes' => ['color' => 'Classic Black'],
                    ],
                    [
                        'sku' => 'BAG-ANTITH-GRY',
                        'price' => 2200.00,
                        'sale_price' => 1450.00,
                        'stock' => 30,
                        'attributes' => ['color' => 'Heather Gray'],
                    ],
                ],
            ],
            [
                'title' => 'Bata Comfit Lightweight Breathable Men Casual Walking Sneakers',
                'category_id' => $shoesCat->id,
                'brand_id' => $bata?->id,
                'sku' => 'BATA-COMF-821',
                'price' => 3499.00,
                'sale_price' => 2799.00,
                'stock' => 60,
                'short_description' => 'Super flexible cushioned sole, breathable mesh upper, shock absorption technology for all-day comfort.',
                'description' => '<p>Crafted for supreme comfort and daily walking. The lightweight EVA sole absorbs walking impact while keeping feet relaxed.</p>',
                'specifications' => [
                    'Upper' => 'Breathable Air Mesh',
                    'Sole' => 'Anti-skid Cushion EVA Sole',
                    'Closure' => 'Lace-up',
                    'Style' => 'Athleisure & Casual',
                ],
                'is_featured' => true,
                'sold_count' => 210,
                'rating_avg' => 4.80,
                'reviews_count' => 54,
                'variants' => [
                    [
                        'sku' => 'BATA-COMF-41-NVY',
                        'price' => 3499.00,
                        'sale_price' => 2799.00,
                        'stock' => 20,
                        'attributes' => ['color' => 'Navy Blue', 'size' => '41'],
                    ],
                    [
                        'sku' => 'BATA-COMF-42-NVY',
                        'price' => 3499.00,
                        'sale_price' => 2799.00,
                        'stock' => 25,
                        'attributes' => ['color' => 'Navy Blue', 'size' => '42'],
                    ],
                    [
                        'sku' => 'BATA-COMF-43-NVY',
                        'price' => 3499.00,
                        'sale_price' => 2799.00,
                        'stock' => 15,
                        'attributes' => ['color' => 'Navy Blue', 'size' => '43'],
                    ],
                ],
            ],
        ];

        foreach ($productsData as $data) {
            $variants = $data['variants'] ?? [];
            unset($data['variants']);

            $data['slug'] = Str::slug($data['title']);

            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                $data
            );

            // Add placeholder product image
            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'is_primary' => true],
                ['image_path' => 'products/' . $product->slug . '.jpg', 'sort_order' => 1]
            );

            // Create variants
            foreach ($variants as $variantData) {
                ProductVariant::updateOrCreate(
                    ['sku' => $variantData['sku']],
                    array_merge($variantData, ['product_id' => $product->id])
                );
            }
        }
    }
}
