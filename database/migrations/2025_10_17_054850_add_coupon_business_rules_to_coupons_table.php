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
        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('minimum_cart_value', 10, 2)->default(0)->after('type');
            $table->decimal('maximum_discount_limit', 10, 2)->nullable()->after('minimum_cart_value');
            $table->integer('usage_limit')->nullable()->after('maximum_discount_limit');
            $table->integer('used_count')->default(0)->after('usage_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['minimum_cart_value', 'maximum_discount_limit', 'usage_limit', 'used_count']);
        });
    }
};
