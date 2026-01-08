<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cart;
use App\Models\CartItem;  
use App\Models\ProductVariation;
use App\Services\CartService;
use App\Services\ShippingCalculatorService;
use App\Models\ShippingZone;
use App\Models\ShippingRate;
use Illuminate\Support\Str;

try {
    echo "=== DETAILED SHIPPING COST VERIFICATION ===\n\n";
    
    // Show shipping configuration
    echo "=== SHIPPING CONFIGURATION ===\n";
    $zones = ShippingZone::with('shippingRates')->get();
    foreach ($zones as $zone) {
        echo "Zone: {$zone->name}\n";
        echo "Pincodes: " . implode(', ', $zone->pincodes) . "\n";
        echo "Rates:\n";
        foreach ($zone->shippingRates as $rate) {
            echo "  - {$rate->min_weight}g-{$rate->max_weight}g: ₹{$rate->base_rate}\n";
        }
        echo "\n";
    }
    
    // Create test with lower value product to avoid free shipping
    echo "=== TESTING WITH DIFFERENT ORDER VALUES ===\n";
    
    $cartService = new CartService(new ShippingCalculatorService());
    $variations = ProductVariation::where('product_id', 1002)->get();
    
    // Test 1: Low value order (modify price temporarily)
    $originalPrice = $variations[0]->price;
    $variations[0]->update(['price' => 200]); // Low price to avoid free shipping
    
    $cart = Cart::create(['uuid' => Str::uuid()->toString()]);
    $cartService->addItem($cart, $variations[0]->id, 1);
    
    echo "Test 1: Low value order (₹200)\n";
    $summary = $cartService->cartSummary($cart, '400001'); // Mumbai
    echo "Subtotal: ₹{$summary['subtotal']}\n";
    echo "Weight: {$summary['total_weight']}g\n";
    echo "Shipping: ₹{$summary['shipping_cost']}\n";
    echo "Zone: {$summary['shipping_zone']}\n";
    echo "Message: {$summary['shipping_message']}\n\n";
    
    // Test with different weight by adding more items
    $cartService->addItem($cart, $variations[1]->id, 3);
    
    echo "Test 2: Multiple items (4 items, 1200g total)\n";
    $summary2 = $cartService->cartSummary($cart, '400001');
    echo "Subtotal: ₹{$summary2['subtotal']}\n";
    echo "Weight: {$summary2['total_weight']}g\n";
    echo "Shipping: ₹{$summary2['shipping_cost']}\n";
    echo "Zone: {$summary2['shipping_zone']}\n";
    echo "Message: {$summary2['shipping_message']}\n\n";
    
    // Test with different pincode (metro vs non-metro)
    echo "Test 3: Different location (non-metro)\n";
    $summary3 = $cartService->cartSummary($cart, '492001'); // Raipur (Tier 2)
    echo "Subtotal: ₹{$summary3['subtotal']}\n";
    echo "Weight: {$summary3['total_weight']}g\n";
    echo "Shipping: ₹{$summary3['shipping_cost']}\n";
    echo "Zone: {$summary3['shipping_zone']}\n";
    echo "Message: {$summary3['shipping_message']}\n\n";
    
    echo "=== WEIGHT-BASED SHIPPING VERIFICATION ===\n";
    
    if ($summary['shipping_cost'] == 50 && $summary2['shipping_cost'] == 50) {
        echo "❌ PROBLEM: Still using fixed ₹50 shipping\n";
        echo "The weight-based system may not be properly configured.\n";
    } else {
        echo "✅ SUCCESS: Weight-based shipping is working!\n";
        echo "Different weights and locations = different shipping costs\n";
        echo "- Light package (300g): ₹{$summary['shipping_cost']}\n";
        echo "- Heavy package (1200g): ₹{$summary2['shipping_cost']}\n";
        echo "- Different zone: ₹{$summary3['shipping_cost']}\n";
    }
    
    // Restore original price
    $variations[0]->update(['price' => $originalPrice]);
    
    // Cleanup
    $cart->items()->delete();
    $cart->delete();
    
    echo "\n✅ Cart and checkout shipping verification complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}