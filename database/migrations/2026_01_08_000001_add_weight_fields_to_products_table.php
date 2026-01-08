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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->default(200.00)->after('mrp')->comment('Weight in grams');
            $table->decimal('length', 8, 2)->nullable()->after('weight')->comment('Length in cm');
            $table->decimal('width', 8, 2)->nullable()->after('length')->comment('Width in cm');
            $table->decimal('height', 8, 2)->nullable()->after('width')->comment('Height in cm');
            $table->decimal('volumetric_weight', 8, 2)->nullable()->after('height')->comment('Calculated volumetric weight in grams');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight', 'length', 'width', 'height', 'volumetric_weight']);
        });
    }
};