<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variation_id')->nullable()->after('product_id');
            $table->foreign('product_variation_id')->references('id')->on('product_variations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_variation_id']);
            $table->dropColumn('product_variation_id');
        });
    }
};
