<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariation;
use App\Services\CartService;
use App\Services\ShippingCalculatorService;
use Illuminate\Support\Str;

try {
    echo "=== CART & CHECKOUT SHIPPING VERIFICATION ===\n\n";
    
    // Get our test product variations
    $variations = ProductVariation::where('product_id', 1002)->get();
    if ($variations->isEmpty()) {
        echo "❌ No variations found for product 1002\n";
        exit;
    }
    
    echo "Found {$variations->count()} variations for testing\n";
    
    // Create a test cart
    $cartService = new CartService(new ShippingCalculatorService());
    $cart = Cart::create(['uuid' => Str::uuid()->toString()]);
    
    echo "Created test cart: {$cart->uuid}\n\n";
    
    // Test 1: Add 1 item
    echo "=== TEST 1: Single Item (1x Shirt) ===\n";
    $cartService->addItem($cart, $variations[0]->id, 1);
    $summary1 = $cartService->cartSummary($cart, '400001'); // Mumbai pincode
    
    echo "Items: {$summary1['items']}\n";
    echo "Subtotal: ₹{$summary1['subtotal']}\n";
    echo "Total Weight: {$summary1['total_weight']}g ({$summary1['total_weight_kg']}kg)\n";
    echo "Shipping Cost: ₹{$summary1['shipping_cost']}\n";
    echo "Shipping Zone: {$summary1['shipping_zone']}\n";
    echo "Weight Slab: {$summary1['weight_slab']}\n";
    echo "Total: ₹{$summary1['total']}\n\n";
    
    // Test 2: Add more items
    echo "=== TEST 2: Multiple Items (3x Shirts) ===\n";
    $cartService->addItem($cart, $variations[1]->id, 2); // Add 2 more different variations
    $summary2 = $cartService->cartSummary($cart, '400001');
    
    echo "Items: {$summary2['items']}\n";
    echo "Subtotal: ₹{$summary2['subtotal']}\n";
    echo "Total Weight: {$summary2['total_weight']}g ({$summary2['total_weight_kg']}kg)\n";
    echo "Shipping Cost: ₹{$summary2['shipping_cost']}\n";
    echo "Shipping Zone: {$summary2['shipping_zone']}\n";
    echo "Weight Slab: {$summary2['weight_slab']}\n";
    echo "Total: ₹{$summary2['total']}\n\n";
    
    // Test 3: Different pincode
    echo "=== TEST 3: Different Location (Delhi) ===\n";
    $summary3 = $cartService->cartSummary($cart, '110001'); // Delhi pincode
    
    echo "Items: {$summary3['items']}\n";
    echo "Subtotal: ₹{$summary3['subtotal']}\n";
    echo "Total Weight: {$summary3['total_weight']}g ({$summary3['total_weight_kg']}kg)\n";
    echo "Shipping Cost: ₹{$summary3['shipping_cost']}\n";
    echo "Shipping Zone: {$summary3['shipping_zone']}\n";
    echo "Weight Slab: {$summary3['weight_slab']}\n";
    echo "Total: ₹{$summary3['total']}\n\n";
    
    // Compare with old fixed system
    echo "=== COMPARISON WITH OLD FIXED SYSTEM ===\n";
    echo "Old System (Fixed): ₹50 for ALL orders\n";
    echo "New System (1 item): ₹{$summary1['shipping_cost']}\n";
    echo "New System (3 items): ₹{$summary2['shipping_cost']}\n";
    
    if ($summary1['shipping_cost'] == 50 && $summary2['shipping_cost'] == 50) {
        echo "❌ STILL USING FIXED Rs 50 SHIPPING!\n";
        echo "The weight-based system is not active.\n";
    } else {
        echo "✅ WEIGHT-BASED SHIPPING IS ACTIVE!\n";
        echo "Shipping cost varies based on weight and location.\n";
    }
    
    // Show weight suggestions
    if (!empty($summary2['shipping_suggestions'])) {
        echo "\nWeight Optimization Suggestions:\n";
        foreach ($summary2['shipping_suggestions'] as $suggestion) {
            echo "- {$suggestion}\n";
        }
    }
    
    // Clean up test cart
    $cart->items()->delete();
    $cart->delete();
    echo "\n✅ Test cart cleaned up\n";
    
    echo "\n=== FINAL VERIFICATION ===\n";
    echo "Cart Service: " . (class_exists('App\\Services\\CartService') ? "✅ Exists" : "❌ Missing") . "\n";
    echo "Shipping Calculator: " . (class_exists('App\\Services\\ShippingCalculatorService') ? "✅ Exists" : "❌ Missing") . "\n";
    echo "Weight-based Logic: " . ($summary1['shipping_cost'] != 50 || $summary2['shipping_cost'] != 50 ? "✅ Active" : "❌ Not Active") . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}