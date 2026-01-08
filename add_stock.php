<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\VariationStock;
use App\Models\ProductVariation;

try {
    echo "Adding stock to product 1002 variations...\n";
    
    $variations = ProductVariation::where('product_id', 1002)->get();
    
    foreach ($variations as $variation) {
        VariationStock::updateOrCreate(
            ['product_variation_id' => $variation->id],
            ['quantity' => 10, 'in_stock' => true]
        );
        echo "Updated stock for variation {$variation->id}: 10 units\n";
    }
    
    echo "✅ Stock updated successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}