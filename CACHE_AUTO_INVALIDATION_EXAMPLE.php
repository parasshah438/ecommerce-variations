<?php

// Example: In your Admin ProductController update method

public function update(Request $request, Product $product)
{
    // Validate and update product
    // ... your existing update logic ...
    
    // Get old values for cache clearing
    $oldCategoryId = $product->category_id;
    $oldBrandId = $product->brand_id;
    
    // Update the product
    $product->update($request->validated());
    
    // Clear relevant caches automatically
    \App\Services\ProductCacheService::clearProductCaches(
        $product->id,
        $oldCategoryId,
        $oldBrandId
    );
    
    // Also clear for new category if changed
    if ($product->category_id != $oldCategoryId) {
        \App\Services\ProductCacheService::clearProductCaches(
            $product->id,
            $product->category_id,
            $product->brand_id
        );
    }
    
    return redirect()->back()->with('success', 'Product updated and caches refreshed!');
}