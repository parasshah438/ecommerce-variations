<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('save_for_later', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('guest_uuid')->nullable();
            $table->unsignedBigInteger('product_variation_id');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2); // Save price at time of saving
            $table->text('notes')->nullable(); // Optional notes
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_variation_id')->references('id')->on('product_variations')->onDelete('cascade');
            
            // Ensure unique combinations
            $table->unique(['user_id', 'product_variation_id'], 'unique_user_variation');
            $table->index(['guest_uuid', 'product_variation_id'], 'guest_variation_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('save_for_later');
    }
};