<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Diwali Sale", "Black Friday"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('type')->default('percentage'); // percentage, fixed, bogo
            $table->decimal('discount_value', 8, 2); // 20 for 20% or 500 for ₹500
            $table->decimal('max_discount', 8, 2)->nullable(); // Max discount cap
            $table->decimal('min_order_value', 8, 2)->nullable(); // Minimum order for sale
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->boolean('is_active')->default(true);
            $table->json('applicable_categories')->nullable(); // Category restrictions
            $table->json('applicable_brands')->nullable(); // Brand restrictions
            $table->integer('usage_limit')->nullable(); // Total usage limit
            $table->integer('usage_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
