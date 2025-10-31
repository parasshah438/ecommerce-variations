<?php
/**
 * Cart Cleanup Script
 * This script helps clean up orphaned cart items where the product variation is missing
 */

require_once 'vendor/autoload.php';

use App\Models\Cart;
use App\Models\CartItem;

// Clean up orphaned cart items
function cleanupOrphanedCartItems() {
    // Find cart items where product variation doesn't exist
    $orphanedItems = CartItem::whereDoesntHave('productVariation')->get();
    
    echo "Found " . $orphanedItems->count() . " orphaned cart items\n";
    
    foreach ($orphanedItems as $item) {
        echo "Removing cart item ID: {$item->id} (variation_id: {$item->product_variation_id})\n";
        $item->delete();
    }
    
    // Find cart items where product variation exists but product doesn't
    $orphanedProductItems = CartItem::whereHas('productVariation', function($query) {
        $query->whereDoesntHave('product');
    })->get();
    
    echo "Found " . $orphanedProductItems->count() . " cart items with missing products\n";
    
    foreach ($orphanedProductItems as $item) {
        echo "Removing cart item ID: {$item->id} (product missing)\n";
        $item->delete();
    }
    
    echo "Cleanup completed!\n";
}

// Run the cleanup
cleanupOrphanedCartItems();