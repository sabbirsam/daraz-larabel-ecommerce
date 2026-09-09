<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending'); // pending, processing, shipped, delivered, cancelled, returned
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('shipping_fee', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->string('coupon_code', 50)->nullable();
            $table->string('payment_method', 50)->default('cod'); // cod, sslcommerz, stripe
            $table->string('payment_status', 30)->default('unpaid'); // unpaid, paid, refunded, failed
            $table->json('shipping_address');
            $table->json('billing_address')->nullable();
            $table->text('customer_notes')->nullable();
            $table->timestamps();

            // High-concurrency composite indexes for order lookups & dashboard analytics
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
