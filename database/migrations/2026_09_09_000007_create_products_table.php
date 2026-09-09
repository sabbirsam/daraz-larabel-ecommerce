<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->json('specifications')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->timestamps();

            // High-concurrency composite indexes for lightning fast filtering & sorting
            $table->index(['is_active', 'category_id', 'created_at']);
            $table->index(['is_active', 'brand_id']);
            $table->index(['is_active', 'price']);
            $table->index(['is_active', 'rating_avg']);
            $table->index(['is_featured', 'is_active']);
            $table->index(['is_active', 'sold_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
