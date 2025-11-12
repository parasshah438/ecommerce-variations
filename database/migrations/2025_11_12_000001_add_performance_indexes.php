<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations for better performance on product filtering
     */
    public function up(): void
    {
        // Add indexes for better query performance
        try {
            DB::statement('CREATE INDEX idx_products_category_created ON products(category_id, created_at)');
        } catch (\Exception $e) {
            // Index might already exist, ignore
        }
        
        try {
            DB::statement('CREATE INDEX idx_products_brand_created ON products(brand_id, created_at)');
        } catch (\Exception $e) {
            // Index might already exist, ignore
        }
        
        try {
            DB::statement('CREATE INDEX idx_products_price ON products(price)');
        } catch (\Exception $e) {
            // Index might already exist, ignore
        }
        
        try {
            DB::statement('CREATE INDEX idx_product_variations_product_id ON product_variations(product_id)');
        } catch (\Exception $e) {
            // Index might already exist, ignore
        }
        
        try {
            DB::statement('CREATE INDEX idx_attribute_values_attribute_id ON attribute_values(attribute_id)');
        } catch (\Exception $e) {
            // Index might already exist, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('DROP INDEX idx_products_category_created ON products');
        } catch (\Exception $e) {
            // Index might not exist, ignore
        }
        
        try {
            DB::statement('DROP INDEX idx_products_brand_created ON products');
        } catch (\Exception $e) {
            // Index might not exist, ignore
        }
        
        try {
            DB::statement('DROP INDEX idx_products_price ON products');
        } catch (\Exception $e) {
            // Index might not exist, ignore
        }
        
        try {
            DB::statement('DROP INDEX idx_product_variations_product_id ON product_variations');
        } catch (\Exception $e) {
            // Index might not exist, ignore
        }
        
        try {
            DB::statement('DROP INDEX idx_attribute_values_attribute_id ON attribute_values');
        } catch (\Exception $e) {
            // Index might not exist, ignore
        }
    }
};