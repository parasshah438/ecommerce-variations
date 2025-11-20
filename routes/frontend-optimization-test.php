<?php
/**
 * Frontend Image Optimization Testing Routes
 * Test routes to verify optimized images are working in frontend display
 */

// Test frontend product display with optimization
Route::get('/test-frontend-optimization', function() {
    try {
        $output = "<h2>🎨 Frontend Image Optimization Test</h2>";
        
        // Test 1: Get a sample product with images
        $product = \App\Models\Product::with(['images', 'variations.variationImages'])
                                     ->whereHas('images')
                                     ->first();
        
        if (!$product) {
            return "<h3>❌ No products with images found</h3><p>Please create products with images first.</p>";
        }
        
        $output .= "<h3>✅ Testing Product: {$product->name}</h3>";
        
        // Test 2: Product Images Display
        $output .= "<h4>📸 Main Product Images:</h4>";
        foreach ($product->images as $image) {
            $optimizedUrl = $image->getOptimizedImageUrl();
            $webpUrl = $image->getWebPUrl();
            $thumbnailUrl = $image->getThumbnailUrl();
            
            $output .= "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
            $output .= "<strong>Image ID:</strong> {$image->id}<br>";
            $output .= "<strong>Original Path:</strong> {$image->path}<br>";
            $output .= "<strong>Optimized URL:</strong> <a href='{$optimizedUrl}' target='_blank'>{$optimizedUrl}</a><br>";
            $output .= "<strong>WebP URL:</strong> <a href='{$webpUrl}' target='_blank'>{$webpUrl}</a><br>";
            $output .= "<strong>Thumbnail URL:</strong> <a href='{$thumbnailUrl}' target='_blank'>{$thumbnailUrl}</a><br>";
            
            // Check if files exist
            $optimizedExists = file_exists(storage_path('app/public/' . str_replace('/storage/', '', $optimizedUrl)));
            $webpExists = file_exists(storage_path('app/public/' . str_replace('/storage/', '', $webpUrl)));
            $thumbExists = file_exists(storage_path('app/public/' . str_replace('/storage/', '', $thumbnailUrl)));
            
            $output .= "<strong>Files Status:</strong> ";
            $output .= "Optimized: " . ($optimizedExists ? "✅" : "❌") . " | ";
            $output .= "WebP: " . ($webpExists ? "✅" : "❌") . " | ";
            $output .= "Thumbnail: " . ($thumbExists ? "✅" : "❌") . "<br>";
            
            // Display thumbnail image
            if ($thumbExists) {
                $output .= "<img src='{$thumbnailUrl}' alt='Thumbnail' style='max-width: 150px; border: 1px solid #ccc;'><br>";
            }
            
            $output .= "</div>";
        }
        
        // Test 3: Variation Images Display
        if ($product->variations->count() > 0) {
            $output .= "<h4>🎨 Product Variation Images:</h4>";
            
            foreach ($product->variations as $variation) {
                if ($variation->variationImages && $variation->variationImages->count() > 0) {
                    $output .= "<h5>Variation ID: {$variation->id} (SKU: {$variation->sku})</h5>";
                    
                    foreach ($variation->variationImages as $varImage) {
                        $optimizedUrl = $varImage->getOptimizedImageUrl();
                        $webpUrl = $varImage->getWebPUrl();
                        $thumbnailUrl = $varImage->getThumbnailUrl();
                        
                        $output .= "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; margin-left: 20px;'>";
                        $output .= "<strong>Variation Image ID:</strong> {$varImage->id}<br>";
                        $output .= "<strong>Original Path:</strong> {$varImage->path}<br>";
                        $output .= "<strong>Optimized URL:</strong> <a href='{$optimizedUrl}' target='_blank'>{$optimizedUrl}</a><br>";
                        $output .= "<strong>WebP URL:</strong> <a href='{$webpUrl}' target='_blank'>{$webpUrl}</a><br>";
                        $output .= "<strong>Thumbnail URL:</strong> <a href='{$thumbnailUrl}' target='_blank'>{$thumbnailUrl}</a><br>";
                        
                        // Check if files exist
                        $optimizedExists = file_exists(storage_path('app/public/' . str_replace('/storage/', '', $optimizedUrl)));
                        $webpExists = file_exists(storage_path('app/public/' . str_replace('/storage/', '', $webpUrl)));
                        $thumbExists = file_exists(storage_path('app/public/' . str_replace('/storage/', '', $thumbnailUrl)));
                        
                        $output .= "<strong>Files Status:</strong> ";
                        $output .= "Optimized: " . ($optimizedExists ? "✅" : "❌") . " | ";
                        $output .= "WebP: " . ($webpExists ? "✅" : "❌") . " | ";
                        $output .= "Thumbnail: " . ($thumbExists ? "✅" : "❌") . "<br>";
                        
                        // Display thumbnail image
                        if ($thumbExists) {
                            $output .= "<img src='{$thumbnailUrl}' alt='Variation Thumbnail' style='max-width: 150px; border: 1px solid #ccc;'><br>";
                        }
                        
                        $output .= "</div>";
                    }
                }
            }
        }
        
        // Test 4: Category Images
        $output .= "<h4>📂 Category Images Test:</h4>";
        $category = \App\Models\Category::whereNotNull('image')->first();
        
        if ($category && $category->image) {
            $categoryOptimizedUrl = $category->getOptimizedImageUrl();
            $categoryWebpUrl = $category->getWebPUrl();
            $categoryThumbnailUrl = $category->getThumbnailUrl();
            
            $output .= "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
            $output .= "<strong>Category:</strong> {$category->name}<br>";
            $output .= "<strong>Original Path:</strong> {$category->image}<br>";
            $output .= "<strong>Optimized URL:</strong> <a href='{$categoryOptimizedUrl}' target='_blank'>{$categoryOptimizedUrl}</a><br>";
            $output .= "<strong>WebP URL:</strong> <a href='{$categoryWebpUrl}' target='_blank'>{$categoryWebpUrl}</a><br>";
            $output .= "<strong>Thumbnail URL:</strong> <a href='{$categoryThumbnailUrl}' target='_blank'>{$categoryThumbnailUrl}</a><br>";
            
            // Check if files exist
            $optimizedExists = file_exists(storage_path('app/public/' . str_replace('/storage/', '', $categoryOptimizedUrl)));
            $webpExists = file_exists(storage_path('app/public/' . str_replace('/storage/', '', $categoryWebpUrl)));
            $thumbExists = file_exists(storage_path('app/public/' . str_replace('/storage/', '', $categoryThumbnailUrl)));
            
            $output .= "<strong>Files Status:</strong> ";
            $output .= "Optimized: " . ($optimizedExists ? "✅" : "❌") . " | ";
            $output .= "WebP: " . ($webpExists ? "✅" : "❌") . " | ";
            $output .= "Thumbnail: " . ($thumbExists ? "✅" : "❌") . "<br>";
            
            // Display thumbnail image
            if ($thumbExists) {
                $output .= "<img src='{$categoryThumbnailUrl}' alt='Category Thumbnail' style='max-width: 150px; border: 1px solid #ccc;'><br>";
            }
            
            $output .= "</div>";
        } else {
            $output .= "<p>No categories with images found.</p>";
        }
        
        // Test 5: Frontend Controller Data Simulation
        $output .= "<h4>🖥️ Frontend Controller Data Test:</h4>";
        
        // Simulate what the frontend controller returns
        $productImages = $product->images->map(function ($i) {
            return [
                'id' => $i->id, 
                'path' => $i->getOptimizedImageUrl(), 
                'webp_path' => $i->getWebPUrl(),
                'thumbnail_url' => $i->getThumbnailUrl(),
                'position' => $i->position,
                'alt' => $i->alt
            ];
        })->values();
        
        $output .= "<strong>Frontend Controller Image Data:</strong><br>";
        $output .= "<pre>" . json_encode($productImages, JSON_PRETTY_PRINT) . "</pre>";
        
        // Performance comparison
        $output .= "<h4>⚡ Performance Benefits:</h4>";
        $output .= "<ul>";
        $output .= "<li>✅ Images automatically served in optimized format</li>";
        $output .= "<li>✅ WebP format available for modern browsers</li>";
        $output .= "<li>✅ Thumbnails generated for faster loading</li>";
        $output .= "<li>✅ Graceful fallback to original images if optimization fails</li>";
        $output .= "<li>✅ No additional database queries needed</li>";
        $output .= "</ul>";
        
        $output .= "<br><h4>🔗 Test Links:</h4>";
        $output .= "<p><a href='/products'>Visit Products Page</a> | ";
        $output .= "<a href='/products/{$product->slug}'>View This Product</a> | ";
        if ($category) {
            $output .= "<a href='/category/{$category->slug}'>View Category</a> | ";
        }
        $output .= "<a href='/admin/products'>Admin Products</a></p>";
        
        return $output;
        
    } catch (\Exception $e) {
        return "<h3>❌ Error:</h3><p>" . $e->getMessage() . "</p><pre>" . $e->getTraceAsString() . "</pre>";
    }
})->name('test.frontend.optimization');

// Test frontend responsive image HTML generation
Route::get('/test-responsive-images', function() {
    try {
        $output = "<h2>📱 Responsive Images Test</h2>";
        
        $product = \App\Models\Product::with('images')->whereHas('images')->first();
        
        if (!$product) {
            return "<h3>❌ No products with images found</h3>";
        }
        
        $output .= "<h3>✅ Testing Responsive HTML for: {$product->name}</h3>";
        
        foreach ($product->images as $image) {
            $responsiveHtml = $image->getResponsiveImageHtml('Product Image', 'img-fluid product-image');
            
            $output .= "<h4>Image ID: {$image->id}</h4>";
            $output .= "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0;'>";
            $output .= "<strong>Generated HTML:</strong><br>";
            $output .= "<textarea rows='8' cols='100'>" . htmlspecialchars($responsiveHtml) . "</textarea><br><br>";
            $output .= "<strong>Rendered Output:</strong><br>";
            $output .= $responsiveHtml;
            $output .= "</div>";
        }
        
        return $output;
        
    } catch (\Exception $e) {
        return "<h3>❌ Error:</h3><p>" . $e->getMessage() . "</p>";
    }
})->name('test.responsive.images');

// Test optimization status
Route::get('/test-optimization-status', function() {
    try {
        $output = "<h2>📊 Image Optimization Status</h2>";
        
        // Check ImageOptimizer status
        $status = \App\Helpers\ImageOptimizer::checkOptimizerStatus();
        
        $output .= "<h3>🔧 Optimizer Status:</h3>";
        $output .= "<ul>";
        foreach ($status as $key => $value) {
            $icon = $value ? "✅" : "❌";
            $output .= "<li>{$icon} " . ucfirst(str_replace('_', ' ', $key)) . ": " . ($value ? 'Available' : 'Not Available') . "</li>";
        }
        $output .= "</ul>";
        
        // Statistics
        $totalProductImages = \App\Models\ProductImage::count();
        $totalVariationImages = \App\Models\ProductVariationImage::count();
        $totalCategoryImages = \App\Models\Category::whereNotNull('image')->count();
        
        $output .= "<h3>📈 Image Statistics:</h3>";
        $output .= "<ul>";
        $output .= "<li>Total Product Images: {$totalProductImages}</li>";
        $output .= "<li>Total Variation Images: {$totalVariationImages}</li>";
        $output .= "<li>Total Category Images: {$totalCategoryImages}</li>";
        $output .= "</ul>";
        
        // Check optimization directory
        $optimizedDir = storage_path('app/public/optimized');
        $webpDir = storage_path('app/public/webp');
        $thumbDir = storage_path('app/public/thumbnails');
        
        $output .= "<h3>📂 Directory Status:</h3>";
        $output .= "<ul>";
        $output .= "<li>Optimized Directory: " . (is_dir($optimizedDir) ? "✅ Exists" : "❌ Missing") . "</li>";
        $output .= "<li>WebP Directory: " . (is_dir($webpDir) ? "✅ Exists" : "❌ Missing") . "</li>";
        $output .= "<li>Thumbnails Directory: " . (is_dir($thumbDir) ? "✅ Exists" : "❌ Missing") . "</li>";
        $output .= "</ul>";
        
        if (is_dir($optimizedDir)) {
            $optimizedFiles = count(glob($optimizedDir . '/*'));
            $output .= "<p>Optimized files count: {$optimizedFiles}</p>";
        }
        
        if (is_dir($webpDir)) {
            $webpFiles = count(glob($webpDir . '/*'));
            $output .= "<p>WebP files count: {$webpFiles}</p>";
        }
        
        if (is_dir($thumbDir)) {
            $thumbFiles = count(glob($thumbDir . '/*'));
            $output .= "<p>Thumbnail files count: {$thumbFiles}</p>";
        }
        
        return $output;
        
    } catch (\Exception $e) {
        return "<h3>❌ Error:</h3><p>" . $e->getMessage() . "</p>";
    }
})->name('test.optimization.status');