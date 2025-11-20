<?php

use Illuminate\Support\Facades\Route;

// Test route for CategoryController integration
Route::get('/test-category-optimizer', function () {
    
    $html = "<!DOCTYPE html>
    <html>
    <head>
        <title>Category ImageOptimizer Integration Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .success { color: green; }
            .error { color: red; }
            .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .feature { background: #f0f8ff; padding: 10px; margin: 10px 0; border-left: 4px solid #007bff; }
            pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
            table { border-collapse: collapse; width: 100%; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
        <h1>🎯 CategoryController ImageOptimizer Integration</h1>
        
        <div class='info'>
            <h2>✅ Integration Complete!</h2>
            <p>The CategoryController has been successfully integrated with the ImageOptimizer helper.</p>
        </div>

        <h2>🔧 What Was Added:</h2>
        
        <div class='feature'>
            <h3>1. Enhanced Image Upload (Store Method)</h3>
            <ul>
                <li>✅ Automatic image optimization with Spatie + Intervention Image</li>
                <li>✅ WebP format generation for better performance</li>
                <li>✅ Thumbnail generation (150px, 300px)</li>
                <li>✅ Compression ratio logging</li>
                <li>✅ Fallback to regular upload if optimization fails</li>
            </ul>
        </div>

        <div class='feature'>
            <h3>2. Enhanced Image Update (Update Method)</h3>
            <ul>
                <li>✅ Same optimization features as store method</li>
                <li>✅ Automatic cleanup of old optimized files</li>
                <li>✅ Proper error handling and logging</li>
            </ul>
        </div>

        <div class='feature'>
            <h3>3. Updated Category Model</h3>
            <ul>
                <li>✅ <code>optimized_image_url</code> attribute (prefers WebP)</li>
                <li>✅ <code>getThumbnailUrl(\$size)</code> method</li>
                <li>✅ <code>getResponsiveImageHtml()</code> method</li>
                <li>✅ Automatic cleanup of all related files on delete</li>
            </ul>
        </div>

        <div class='feature'>
            <h3>4. Enhanced File Management</h3>
            <ul>
                <li>✅ Increased file size limit to 5MB (will be optimized)</li>
                <li>✅ Added WebP format support</li>
                <li>✅ Smart cleanup of thumbnails, WebP, and backup files</li>
            </ul>
        </div>

        <h2>📖 Usage Examples:</h2>

        <h3>In Blade Templates:</h3>
        <pre><code>{{-- Display optimized image (WebP if available) --}}
&lt;img src=\"{{ \$category->optimized_image_url }}\" alt=\"{{ \$category->name }}\"&gt;

{{-- Display thumbnail --}}
&lt;img src=\"{{ \$category->getThumbnailUrl(150) }}\" alt=\"{{ \$category->name }}\"&gt;

{{-- Generate responsive image with multiple formats --}}
{!! \$category->getResponsiveImageHtml(\$category->name, 'img-fluid rounded') !!}</code></pre>

        <h3>In Controllers:</h3>
        <pre><code>// Get category with optimized image
\$category = Category::find(1);

// Check if WebP version exists
\$webpUrl = \$category->optimized_image_url;

// Get specific thumbnail size
\$thumbnailUrl = \$category->getThumbnailUrl(300);</code></pre>

        <h2>🎨 Image Optimization Settings:</h2>
        <table>
            <tr><th>Setting</th><th>Value</th><th>Description</th></tr>
            <tr><td>Quality</td><td>85%</td><td>Good balance of quality and file size</td></tr>
            <tr><td>Max Width</td><td>800px</td><td>Suitable for category display</td></tr>
            <tr><td>Max Height</td><td>600px</td><td>Maintains aspect ratio</td></tr>
            <tr><td>WebP Generation</td><td>Enabled</td><td>Better compression than JPEG</td></tr>
            <tr><td>Thumbnails</td><td>150px, 300px</td><td>For different display contexts</td></tr>
            <tr><td>File Size Limit</td><td>5MB</td><td>Large files will be compressed</td></tr>
        </table>

        <h2>🧪 Test Category Upload:</h2>
        <div class='info'>
            <p>To test the integration:</p>
            <ol>
                <li>Go to your admin panel: <code>/admin/categories/create</code></li>
                <li>Upload a category image (up to 5MB)</li>
                <li>Check the Laravel logs to see optimization results</li>
                <li>View the category to see optimized images in action</li>
            </ol>
        </div>

        <h2>📊 Expected File Structure After Upload:</h2>
        <pre><code>storage/app/public/categories/
├── 1234567890_abcdef123.jpg     (Original optimized)
├── 1234567890_abcdef123.webp    (WebP version)
├── 1234567890_abcdef123_150.jpg (150px thumbnail)
└── 1234567890_abcdef123_300.jpg (300px thumbnail)</code></pre>

        <div class='info'>
            <h3>🎉 Benefits:</h3>
            <ul>
                <li><strong>Faster Loading:</strong> Smaller file sizes, WebP format</li>
                <li><strong>Better SEO:</strong> Optimized images improve page speed scores</li>
                <li><strong>Responsive Design:</strong> Multiple sizes for different devices</li>
                <li><strong>Storage Efficiency:</strong> Better compression ratios</li>
                <li><strong>Modern Formats:</strong> Automatic WebP generation</li>
            </ul>
        </div>
    </body>
    </html>";
    
    return $html;
});

// Test API endpoint for category image optimization status
Route::get('/api/test/category-optimization-status', function () {
    
    $categories = \App\Models\Category::whereNotNull('image')->take(5)->get();
    
    $results = [];
    foreach ($categories as $category) {
        $pathInfo = pathinfo($category->image);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';
        
        $webpPath = $directory . '/' . $filename . '.webp';
        $thumb150Path = $directory . '/' . $filename . '_150.' . $extension;
        $thumb300Path = $directory . '/' . $filename . '_300.' . $extension;
        
        $results[] = [
            'category_id' => $category->id,
            'category_name' => $category->name,
            'original_image' => $category->image,
            'image_exists' => \Storage::disk('public')->exists($category->image),
            'webp_exists' => \Storage::disk('public')->exists($webpPath),
            'thumb_150_exists' => \Storage::disk('public')->exists($thumb150Path),
            'thumb_300_exists' => \Storage::disk('public')->exists($thumb300Path),
            'optimized_url' => $category->optimized_image_url,
            'thumbnail_150_url' => $category->getThumbnailUrl(150),
            'thumbnail_300_url' => $category->getThumbnailUrl(300)
        ];
    }
    
    return response()->json([
        'status' => 'success',
        'categories_tested' => count($results),
        'results' => $results
    ]);
});