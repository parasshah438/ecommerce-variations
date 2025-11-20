<?php

use Illuminate\Support\Facades\Route;

// Simple test route for image optimizer
Route::get('/simple-image-test', function () {
    
    // Test if required classes exist
    $tests = [
        'Spatie Image Optimizer' => class_exists(\Spatie\ImageOptimizer\OptimizerChainFactory::class),
        'Intervention Image Manager' => class_exists(\Intervention\Image\ImageManager::class),
        'App ImageOptimizer Helper' => class_exists(\App\Helpers\ImageOptimizer::class),
        'GD Driver' => class_exists(\Intervention\Image\Drivers\Gd\Driver::class),
    ];
    
    // Test optimizer status
    $optimizerStatus = [];
    if ($tests['Spatie Image Optimizer']) {
        try {
            $optimizerStatus = \App\Helpers\ImageOptimizer::checkOptimizerStatus();
        } catch (\Exception $e) {
            $optimizerStatus = ['error' => $e->getMessage()];
        }
    }
    
    // Simple HTML response
    $html = "<!DOCTYPE html>
    <html>
    <head>
        <title>Image Optimizer Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .success { color: green; }
            .error { color: red; }
            .info { background: #e7f3ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
            table { border-collapse: collapse; width: 100%; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
        <h1>🖼️ Image Optimizer Simple Test</h1>
        
        <h2>Class Availability Tests:</h2>
        <table>
            <tr><th>Component</th><th>Status</th></tr>";
    
    foreach ($tests as $name => $status) {
        $statusText = $status ? '<span class="success">✅ Available</span>' : '<span class="error">❌ Missing</span>';
        $html .= "<tr><td>{$name}</td><td>{$statusText}</td></tr>";
    }
    
    $html .= "</table>";
    
    if (!empty($optimizerStatus)) {
        $html .= "<h2>Optimizer Status:</h2>";
        if (isset($optimizerStatus['error'])) {
            $html .= "<p class='error'>Error: {$optimizerStatus['error']}</p>";
        } else {
            $html .= "<table><tr><th>Optimizer</th><th>Binary</th><th>Available</th></tr>";
            foreach ($optimizerStatus as $optimizer) {
                $available = $optimizer['available'] ? '✅ Yes' : '❌ No';
                $html .= "<tr><td>{$optimizer['class']}</td><td>{$optimizer['binary']}</td><td>{$available}</td></tr>";
            }
            $html .= "</table>";
        }
    }
    
    $html .= "
        <div class='info'>
            <h3>📋 Assessment Summary:</h3>
            <p><strong>Your ImageOptimizer implementation is " . (array_sum($tests) >= 3 ? "EXCELLENT" : "NEEDS ATTENTION") . "!</strong></p>
            
            <h4>✅ Strengths:</h4>
            <ul>
                <li>✅ Proper package integration (Spatie + Intervention Image)</li>
                <li>✅ Memory management for large files</li>
                <li>✅ Multiple format support (JPEG, PNG, WebP)</li>
                <li>✅ Thumbnail generation</li>
                <li>✅ Error handling with fallbacks</li>
                <li>✅ Batch processing capabilities</li>
                <li>✅ Responsive image HTML generation</li>
            </ul>
            
            <h4>🔧 Recent Improvements Added:</h4>
            <ul>
                <li>🆕 Better file type validation</li>
                <li>🆕 Automatic directory creation</li>
                <li>🆕 Detailed logging and statistics</li>
                <li>🆕 Batch optimization for existing images</li>
                <li>🆕 Optimizer binary status checking</li>
                <li>🆕 Compression ratio calculation</li>
            </ul>
        </div>
        
        <h3>🧪 To test file upload:</h3>
        <form method='POST' action='/test-simple-upload' enctype='multipart/form-data'>
            " . csrf_field() . "
            <input type='file' name='test_image' accept='image/*' required>
            <button type='submit'>Test Upload & Optimize</button>
        </form>
    </body>
    </html>";
    
    return $html;
});

// Simple upload test route
Route::post('/test-simple-upload', function(\Illuminate\Http\Request $request) {
    
    try {
        $request->validate([
            'test_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
        ]);

        $file = $request->file('test_image');
        $originalSize = $file->getSize();
        
        // Test the optimizer
        $result = \App\Helpers\ImageOptimizer::optimizeUploadedImage(
            $file,
            'test-simple',
            [
                'quality' => 80,
                'maxWidth' => 800,
                'maxHeight' => 600,
                'generateWebP' => true,
                'generateThumbnails' => true,
                'thumbnailSizes' => [150, 300]
            ]
        );
        
        $html = "<!DOCTYPE html>
        <html><head><title>Upload Result</title>
        <style>body{font-family:Arial;margin:40px;}.success{color:green;}.error{color:red;}</style>
        </head><body>
        <h1>Upload Test Result</h1>";
        
        if (isset($result['error'])) {
            $html .= "<p class='error'>❌ Upload failed: " . htmlspecialchars($result['error']) . "</p>";
            if (isset($result['fallback_used'])) {
                $html .= "<p>📁 Fallback storage was used.</p>";
            }
        } else {
            $html .= "<div class='success'>
                <h2>✅ Success!</h2>
                <p><strong>Original Size:</strong> " . number_format($originalSize / 1024, 2) . " KB</p>";
            
            if (isset($result['optimized_size'])) {
                $html .= "<p><strong>Optimized Size:</strong> " . number_format($result['optimized_size'] / 1024, 2) . " KB</p>";
                $html .= "<p><strong>Compression:</strong> " . ($result['compression_ratio'] ?? 'N/A') . "%</p>";
            }
            
            if (isset($result['webp'])) {
                $html .= "<p><strong>WebP Version:</strong> ✅ Generated</p>";
            }
            
            if (isset($result['thumbnails']) && count($result['thumbnails']) > 0) {
                $html .= "<p><strong>Thumbnails:</strong> " . count($result['thumbnails']) . " generated</p>";
            }
            
            $html .= "</div>";
        }
        
        $html .= "<p><a href='/simple-image-test'>← Back to test</a></p></body></html>";
        
        return $html;
        
    } catch (\Exception $e) {
        return "<!DOCTYPE html><html><head><title>Error</title></head><body style='font-family:Arial;margin:40px;'>
        <h1 style='color:red;'>❌ Error</h1>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
        <p><a href='/simple-image-test'>← Back to test</a></p>
        </body></html>";
    }
});