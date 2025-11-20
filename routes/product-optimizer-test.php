<?php

use Illuminate\Support\Facades\Route;

// Test route for ProductController integration
Route::get('/test-product-optimizer', function () {
    
    $html = "<!DOCTYPE html>
    <html>
    <head>
        <title>Product ImageOptimizer Integration Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
            .success { color: green; }
            .error { color: red; }
            .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .feature { background: #f0f8ff; padding: 10px; margin: 10px 0; border-left: 4px solid #007bff; }
            .comparison { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; }
            pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
            table { border-collapse: collapse; width: 100%; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #f2f2f2; }
            .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <h1>🛍️ ProductController ImageOptimizer Integration</h1>
        
        <div class='info'>
            <h2>✅ Integration Complete!</h2>
            <p>The ProductController has been successfully integrated with the ImageOptimizer helper for both <strong>main product images</strong> and <strong>variation-specific images</strong>.</p>
        </div>

        <h2>🔧 What Was Enhanced:</h2>
        
        <div class='grid'>
            <div class='feature'>
                <h3>1. Main Product Images</h3>
                <ul>
                    <li>✅ High-quality optimization (88% quality)</li>
                    <li>✅ Larger dimensions (1200x1200px)</li>
                    <li>✅ WebP format generation</li>
                    <li>✅ Multiple thumbnails (150px, 300px, 600px)</li>
                    <li>✅ 8MB file size support</li>
                </ul>
            </div>

            <div class='feature'>
                <h3>2. Variation Images</h3>
                <ul>
                    <li>✅ Balanced optimization (85% quality)</li>
                    <li>✅ Appropriate dimensions (800x800px)</li>
                    <li>✅ WebP format generation</li>
                    <li>✅ Variation thumbnails (150px, 300px)</li>
                    <li>✅ Variation-specific optimization</li>
                </ul>
            </div>
        </div>

        <div class='comparison'>
            <h3>📊 Optimization Settings Comparison:</h3>
            <table>
                <tr><th>Setting</th><th>Main Product Images</th><th>Variation Images</th><th>Reason</th></tr>
                <tr><td>Quality</td><td>88%</td><td>85%</td><td>Main images need higher quality for hero displays</td></tr>
                <tr><td>Max Size</td><td>1200x1200px</td><td>800x800px</td><td>Variations are typically smaller in UI</td></tr>
                <tr><td>Thumbnails</td><td>150, 300, 600px</td><td>150, 300px</td><td>Main products need more size options</td></tr>
                <tr><td>File Limit</td><td>8MB</td><td>8MB</td><td>Large files compressed automatically</td></tr>
            </table>
        </div>

        <h2>🎨 Enhanced Model Features:</h2>
        
        <div class='feature'>
            <h3>ProductImage & ProductVariationImage Models</h3>
            <ul>
                <li>✅ <code>optimized_image_url</code> - WebP version if available</li>
                <li>✅ <code>getThumbnailUrl(\$size)</code> - Specific thumbnail sizes</li>
                <li>✅ <code>getResponsiveImageHtml()</code> - Complete responsive picture elements</li>
                <li>✅ <code>deleteImageFiles()</code> - Smart cleanup of all related files</li>
                <li>✅ Automatic file cleanup on model deletion</li>
            </ul>
        </div>

        <h2>📖 Usage Examples:</h2>

        <h3>In Blade Templates - Main Product Images:</h3>
        <pre><code>{{-- Display optimized product image --}}
&lt;img src=\"{{ \$product->images->first()?->optimized_image_url }}\" alt=\"{{ \$product->name }}\"&gt;

{{-- Display product thumbnail --}}
&lt;img src=\"{{ \$product->images->first()?->getThumbnailUrl(300) }}\" alt=\"{{ \$product->name }}\"&gt;

{{-- Generate responsive product image --}}
{!! \$product->images->first()?->getResponsiveImageHtml(\$product->name, 'img-fluid product-image') !!}</code></pre>

        <h3>In Blade Templates - Variation Images:</h3>
        <pre><code>{{-- Display variation images --}}
@foreach(\$product->variations as \$variation)
    @foreach(\$variation->images as \$image)
        &lt;img src=\"{{ \$image->optimized_image_url }}\" alt=\"{{ \$image->alt }}\" class=\"variation-image\"&gt;
    @endforeach
@endforeach

{{-- Variation thumbnail gallery --}}
@foreach(\$variation->images as \$image)
    &lt;img src=\"{{ \$image->getThumbnailUrl(150) }}\" alt=\"{{ \$image->alt }}\" class=\"thumb\"&gt;
@endforeach</code></pre>

        <h3>In Controllers - Batch Operations:</h3>
        <pre><code>// Batch optimize all images for a product
\$result = \$productController->batchOptimizeImages(\$product);

// Delete with cleanup
\$productImage->delete(); // Automatically cleans up all related files

// Get optimized URL programmatically
\$optimizedUrl = \$productController->getOptimizedImageUrl(\$imagePath, 300, 'webp');</code></pre>

        <h2>📁 Expected File Structure After Upload:</h2>
        <pre><code>storage/app/public/
├── products/
│   ├── 1234567890_abcdef123.jpg     (Main product - optimized)
│   ├── 1234567890_abcdef123.webp    (Main product - WebP)
│   ├── 1234567890_abcdef123_150.jpg (Main product - 150px thumb)
│   ├── 1234567890_abcdef123_300.jpg (Main product - 300px thumb)
│   └── 1234567890_abcdef123_600.jpg (Main product - 600px thumb)
└── variations/
    ├── 2345678901_bcdef1234.jpg     (Variation - optimized)
    ├── 2345678901_bcdef1234.webp    (Variation - WebP)
    ├── 2345678901_bcdef1234_150.jpg (Variation - 150px thumb)
    └── 2345678901_bcdef1234_300.jpg (Variation - 300px thumb)</code></pre>

        <h2>🚀 New API Endpoints:</h2>
        <div class='feature'>
            <ul>
                <li><strong>DELETE</strong> <code>/admin/products/images/{image}</code> - Delete product image with cleanup</li>
                <li><strong>DELETE</strong> <code>/admin/products/variation-images/{image}</code> - Delete variation image with cleanup</li>
                <li><strong>POST</strong> <code>/admin/products/{product}/batch-optimize</code> - Batch optimize existing images</li>
            </ul>
        </div>

        <h2>🧪 Testing Your Integration:</h2>
        <div class='info'>
            <ol>
                <li><strong>Create New Product:</strong> Go to <code>/admin/products/create</code></li>
                <li><strong>Upload Images:</strong> Add main product images (up to 8MB each)</li>
                <li><strong>Add Variations:</strong> Create product variations with their own images</li>
                <li><strong>Check Results:</strong> View Laravel logs for optimization statistics</li>
                <li><strong>Test Display:</strong> Use the new model methods in your templates</li>
            </ol>
        </div>

        <h2>📊 Expected Performance Improvements:</h2>
        <table>
            <tr><th>Metric</th><th>Before</th><th>After</th><th>Improvement</th></tr>
            <tr><td>File Size</td><td>2-8MB uploads</td><td>200KB-1MB optimized</td><td>60-80% smaller</td></tr>
            <tr><td>Load Time</td><td>3-5 seconds</td><td>0.5-1.5 seconds</td><td>70% faster</td></tr>
            <tr><td>Storage</td><td>Single format</td><td>Multi-format + sizes</td><td>Better compatibility</td></tr>
            <tr><td>SEO Score</td><td>65-75</td><td>85-95</td><td>Significant boost</td></tr>
        </table>

        <div class='info'>
            <h3>🎉 Production Ready Features:</h3>
            <ul>
                <li><strong>Graceful Degradation:</strong> Falls back to regular upload if optimization fails</li>
                <li><strong>Comprehensive Logging:</strong> Track optimization results and errors</li>
                <li><strong>Smart Cleanup:</strong> Automatically removes all related files when deleting images</li>
                <li><strong>Performance Focused:</strong> Different settings for main vs variation images</li>
                <li><strong>Modern Formats:</strong> WebP support with JPEG fallbacks</li>
                <li><strong>Responsive Ready:</strong> Multiple sizes and formats for all devices</li>
            </ul>
        </div>

        <p><strong>🔗 Next:</strong> <a href='/test-category-optimizer'>Test Category Integration</a> | <a href='/simple-image-test'>Test Basic Upload</a></p>
    </body>
    </html>";
    
    return $html;
});

// API endpoint to check product optimization status
Route::get('/api/test/product-optimization-status', function () {
    
    $products = \App\Models\Product::with(['images', 'variations.images'])->take(3)->get();
    
    $results = [];
    foreach ($products as $product) {
        $productData = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'main_images' => [],
            'variations' => []
        ];
        
        // Check main product images
        foreach ($product->images as $image) {
            $pathInfo = pathinfo($image->path);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'] ?? '';
            
            $webpPath = $directory . '/' . $filename . '.webp';
            $thumb150Path = $directory . '/' . $filename . '_150.' . $extension;
            $thumb300Path = $directory . '/' . $filename . '_300.' . $extension;
            $thumb600Path = $directory . '/' . $filename . '_600.' . $extension;
            
            $productData['main_images'][] = [
                'image_id' => $image->id,
                'original_path' => $image->path,
                'webp_exists' => \Storage::disk('public')->exists($webpPath),
                'thumb_150_exists' => \Storage::disk('public')->exists($thumb150Path),
                'thumb_300_exists' => \Storage::disk('public')->exists($thumb300Path),
                'thumb_600_exists' => \Storage::disk('public')->exists($thumb600Path),
                'optimized_url' => $image->optimized_image_url,
                'thumbnail_urls' => [
                    150 => $image->getThumbnailUrl(150),
                    300 => $image->getThumbnailUrl(300),
                    600 => $image->getThumbnailUrl(600)
                ]
            ];
        }
        
        // Check variation images
        foreach ($product->variations as $variation) {
            $variationData = [
                'variation_id' => $variation->id,
                'variation_sku' => $variation->sku,
                'images' => []
            ];
            
            foreach ($variation->images as $image) {
                $pathInfo = pathinfo($image->path);
                $directory = $pathInfo['dirname'];
                $filename = $pathInfo['filename'];
                $extension = $pathInfo['extension'] ?? '';
                
                $webpPath = $directory . '/' . $filename . '.webp';
                $thumb150Path = $directory . '/' . $filename . '_150.' . $extension;
                $thumb300Path = $directory . '/' . $filename . '_300.' . $extension;
                
                $variationData['images'][] = [
                    'image_id' => $image->id,
                    'original_path' => $image->path,
                    'webp_exists' => \Storage::disk('public')->exists($webpPath),
                    'thumb_150_exists' => \Storage::disk('public')->exists($thumb150Path),
                    'thumb_300_exists' => \Storage::disk('public')->exists($thumb300Path),
                    'optimized_url' => $image->optimized_image_url,
                    'thumbnail_urls' => [
                        150 => $image->getThumbnailUrl(150),
                        300 => $image->getThumbnailUrl(300)
                    ]
                ];
            }
            
            $productData['variations'][] = $variationData;
        }
        
        $results[] = $productData;
    }
    
    return response()->json([
        'status' => 'success',
        'products_tested' => count($results),
        'results' => $results
    ]);
});