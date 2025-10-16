<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debugging Variation Images ===\n";

// Check ProductVariationImage model
$variationImages = \App\Models\ProductVariationImage::take(5)->get();
echo "Found " . $variationImages->count() . " variation images:\n";

foreach ($variationImages as $image) {
    echo "ID: {$image->id}, Path: {$image->path}\n";
    echo "  Product Variation ID: {$image->product_variation_id}\n";
    echo "  Full asset URL: " . asset('storage/' . $image->path) . "\n";
    echo "  Storage URL: " . \Illuminate\Support\Facades\Storage::url($image->path) . "\n";
    echo "  File exists: " . (file_exists(storage_path('app/public/' . $image->path)) ? 'YES' : 'NO') . "\n";
    echo "---\n";
}

// Check Cart Items with variation images
echo "\n=== Cart Items with Variation Images ===\n";
$cartItems = \App\Models\CartItem::with(['productVariation.images', 'productVariation.product.images'])->take(3)->get();

foreach ($cartItems as $item) {
    $product = $item->productVariation->product;
    $variationImage = $item->productVariation->images->first();
    $productImage = $product->images->first();
    
    echo "Cart Item {$item->id} - Product: {$product->name} (Variation: {$item->productVariation->sku})\n";
    
    if ($variationImage) {
        echo "  Variation Image Path: {$variationImage->path}\n";
        echo "  Variation Asset URL: " . asset('storage/' . $variationImage->path) . "\n";
        echo "  Variation File exists: " . (file_exists(storage_path('app/public/' . $variationImage->path)) ? 'YES' : 'NO') . "\n";
    } else {
        echo "  No variation image found\n";
    }
    
    if ($productImage) {
        echo "  Product Image Path: {$productImage->path}\n";
        echo "  Product is External URL: " . (str_starts_with($productImage->path, 'http') ? 'YES' : 'NO') . "\n";
    } else {
        echo "  No product image found\n";
    }
    
    // Test the same logic as in the cart view
    $selectedImage = $variationImage ?? $productImage;
    if ($selectedImage) {
        echo "  Selected Image: " . ($variationImage ? 'VARIATION' : 'PRODUCT') . " - {$selectedImage->path}\n";
        
        $imageSrc = str_starts_with($selectedImage->path, 'http') 
            ? $selectedImage->path 
            : asset('storage/' . $selectedImage->path);
        echo "  Final Image URL: {$imageSrc}\n";
    } else {
        echo "  No image selected\n";
    }
    echo "---\n";
}