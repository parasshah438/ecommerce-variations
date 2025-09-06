<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variation_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variation_id')->nullable();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_variation_id')->references('id')->on('product_variations')->onDelete('cascade');
        });

        // Copy existing product_images entries that reference product_variation_id into the new table
        if (Schema::hasTable('product_images')) {
            $rows = DB::table('product_images')->whereNotNull('product_variation_id')->get();
            foreach ($rows as $r) {
                DB::table('product_variation_images')->insert([
                    'product_id' => $r->product_id,
                    'product_variation_id' => $r->product_variation_id,
                    'path' => $r->path,
                    'alt' => $r->alt,
                    'position' => $r->position,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variation_images');
    }
};
