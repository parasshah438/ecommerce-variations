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
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('domestic'); // domestic, international
            $table->json('pincodes')->nullable(); // Array of pincodes for this zone
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->decimal('min_weight', 8, 2)->default(0); // in grams
            $table->decimal('max_weight', 8, 2)->nullable(); // in grams, null for unlimited
            $table->decimal('base_rate', 8, 2); // base shipping cost
            $table->decimal('additional_rate', 8, 2)->default(0); // cost per additional kg
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('zone_id')->references('id')->on('shipping_zones')->onDelete('cascade');
            $table->index(['zone_id', 'min_weight', 'max_weight']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
    }
};