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
        Schema::table('orders', function (Blueprint $table) {
            // Add tax-related fields
            $table->decimal('subtotal', 10, 2)->default(0)->after('total')->comment('Order subtotal before tax and shipping');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('subtotal')->comment('Total tax amount applied');
            $table->decimal('tax_rate', 5, 4)->default(0.18)->after('tax_amount')->comment('Tax rate applied (e.g., 0.18 for 18%)');
            $table->string('tax_name', 50)->default('GST')->after('tax_rate')->comment('Tax name (GST, VAT, etc.)');
            $table->decimal('shipping_cost', 8, 2)->default(0)->after('tax_name')->comment('Shipping cost applied');
            
            // Update existing total column comment
            $table->decimal('total', 10, 2)->change()->comment('Final total including tax and shipping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'tax_amount', 
                'tax_rate',
                'tax_name',
                'shipping_cost'
            ]);
        });
    }
};