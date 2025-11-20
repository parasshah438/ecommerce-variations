<?php
/**
 * WAMP Configuration Diagnostic and Auto-Fix
 * This route helps diagnose and automatically fix WAMP PHP configuration issues
 */

Route::get('/fix-wamp-config', function() {
    $output = "<h2>🔧 WAMP Configuration Diagnostic & Auto-Fix</h2>";
    
    // Get current settings
    $currentSettings = [
        'memory_limit' => ini_get('memory_limit'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'max_file_uploads' => ini_get('max_file_uploads'),
    ];
    
    $phpIniPath = php_ini_loaded_file();
    $isWamp = strpos($phpIniPath, 'wamp') !== false;
    
    $output .= "<h3>📋 Current Configuration Status:</h3>";
    $output .= "<p><strong>PHP.ini Path:</strong> <code>{$phpIniPath}</code></p>";
    $output .= "<p><strong>WAMP Detected:</strong> " . ($isWamp ? "✅ Yes" : "❌ No") . "</p>";
    
    // Check if settings are correct
    $requiredSettings = [
        'memory_limit' => '512M',
        'upload_max_filesize' => '50M',
        'post_max_size' => '100M',
        'max_execution_time' => '300',
        'max_file_uploads' => '50'
    ];
    
    $needsUpdate = [];
    
    $output .= "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    $output .= "<tr style='background-color: #f5f5f5;'><th style='padding: 10px;'>Setting</th><th style='padding: 10px;'>Current</th><th style='padding: 10px;'>Required</th><th style='padding: 10px;'>Status</th></tr>";
    
    foreach ($requiredSettings as $setting => $required) {
        $current = $currentSettings[$setting] ?? 'Not Set';
        $isCorrect = false;
        
        // Special comparison for memory/size values
        if (in_array($setting, ['memory_limit', 'upload_max_filesize', 'post_max_size'])) {
            $currentBytes = convertToBytes($current);
            $requiredBytes = convertToBytes($required);
            $isCorrect = $currentBytes >= $requiredBytes;
        } else {
            $isCorrect = $current == $required || ($setting == 'max_execution_time' && $current == '0'); // 0 means unlimited
        }
        
        if (!$isCorrect) {
            $needsUpdate[$setting] = $required;
        }
        
        $status = $isCorrect ? "✅ OK" : "❌ Needs Update";
        $bgColor = $isCorrect ? "#d4edda" : "#f8d7da";
        
        $output .= "<tr style='background-color: {$bgColor};'>";
        $output .= "<td style='padding: 10px; font-weight: bold;'>{$setting}</td>";
        $output .= "<td style='padding: 10px;'>{$current}</td>";
        $output .= "<td style='padding: 10px;'>{$required}</td>";
        $output .= "<td style='padding: 10px;'>{$status}</td>";
        $output .= "</tr>";
    }
    
    $output .= "</table>";
    
    if (!empty($needsUpdate)) {
        $output .= "<div style='background-color: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        $output .= "<h3>⚠️ Configuration Issues Found</h3>";
        $output .= "<p>The following settings need to be updated in your php.ini file:</p>";
        $output .= "<ul>";
        foreach ($needsUpdate as $setting => $value) {
            $output .= "<li><strong>{$setting}</strong> should be <code>{$value}</code></li>";
        }
        $output .= "</ul>";
        $output .= "</div>";
        
        // Auto-fix instructions
        $output .= "<div style='background-color: #d1ecf1; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        $output .= "<h3>🔧 Auto-Fix Instructions for WAMP:</h3>";
        
        if ($isWamp) {
            $output .= "<h4>Method 1: WAMP Interface (Easiest)</h4>";
            $output .= "<ol>";
            $output .= "<li><strong>Left-click</strong> WAMP icon in system tray</li>";
            $output .= "<li>Go to <strong>PHP</strong> → <strong>php.ini</strong></li>";
            $output .= "<li>Find and update these lines (Ctrl+F to search):</li>";
            $output .= "</ol>";
            
            $output .= "<textarea rows='10' cols='80' readonly style='font-family: monospace; background-color: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            foreach ($needsUpdate as $setting => $value) {
                $output .= "{$setting} = {$value}\n";
            }
            $output .= "</textarea>";
            
            $output .= "<h4>Method 2: Direct File Edit</h4>";
            $output .= "<p>Edit: <code>{$phpIniPath}</code></p>";
        } else {
            $output .= "<p>Edit the php.ini file at: <code>{$phpIniPath}</code></p>";
        }
        
        $output .= "<p><strong>⚠️ CRITICAL:</strong> After making changes:</p>";
        $output .= "<ol>";
        $output .= "<li>Save the php.ini file</li>";
        $output .= "<li><strong>Restart WAMP</strong> (Left-click WAMP → Restart All Services)</li>";
        $output .= "<li>Wait for WAMP icon to turn green</li>";
        $output .= "<li>Refresh this page to verify changes</li>";
        $output .= "</ol>";
        $output .= "</div>";
        
    } else {
        $output .= "<div style='background-color: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        $output .= "<h3>✅ Configuration is Correct!</h3>";
        $output .= "<p>All PHP settings are properly configured. The issue might be elsewhere.</p>";
        $output .= "</div>";
    }
    
    // Test form to simulate the issue
    $output .= "<div style='background-color: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    $output .= "<h3>🧪 Test Upload Limits</h3>";
    $output .= "<p>Use this form to test if the POST size limits are working:</p>";
    
    $output .= "<form action='/test-post-size' method='POST' enctype='multipart/form-data' style='margin: 15px 0;'>";
    $output .= csrf_field();
    $output .= "<div style='margin: 10px 0;'>";
    $output .= "<label>Test File Upload:</label><br>";
    $output .= "<input type='file' name='test_files[]' multiple accept='image/*' style='margin: 5px 0;'>";
    $output .= "</div>";
    
    // Add some dummy form data to increase POST size
    for ($i = 1; $i <= 20; $i++) {
        $output .= "<input type='hidden' name='dummy_field_{$i}' value='" . str_repeat('x', 1000) . "'>";
    }
    
    $output .= "<button type='submit' style='background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test POST Size Limit</button>";
    $output .= "</form>";
    $output .= "</div>";
    
    // Quick actions
    $output .= "<div style='margin: 30px 0;'>";
    $output .= "<h3>🔗 Quick Actions:</h3>";
    $output .= "<a href='javascript:location.reload()' style='background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔄 Refresh Check</a>";
    $output .= "<a href='/test-upload-config' style='background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📊 Upload Config</a>";
    $output .= "<a href='/admin/products/create' style='background-color: #ffc107; color: black; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🛍️ Test Admin Create</a>";
    $output .= "</div>";
    
    return $output;
    
})->name('fix.wamp.config');

// Test POST size endpoint
Route::post('/test-post-size', function(\Illuminate\Http\Request $request) {
    try {
        $postSize = strlen(http_build_query($request->all()));
        $fileCount = $request->hasFile('test_files') ? count($request->file('test_files')) : 0;
        $totalSize = 0;
        
        if ($request->hasFile('test_files')) {
            foreach ($request->file('test_files') as $file) {
                $totalSize += $file->getSize();
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'POST request processed successfully!',
            'post_data_size' => number_format($postSize / 1024, 2) . ' KB',
            'files_uploaded' => $fileCount,
            'total_file_size' => number_format($totalSize / 1024, 2) . ' KB',
            'combined_size' => number_format(($postSize + $totalSize) / 1024, 2) . ' KB',
            'php_limits' => [
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'memory_limit' => ini_get('memory_limit')
            ]
        ]);
        
    } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
        return response()->json([
            'success' => false,
            'error' => 'PostTooLargeException',
            'message' => 'The POST data is too large. PHP limits need to be increased.',
            'php_limits' => [
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'memory_limit' => ini_get('memory_limit')
            ]
        ], 413);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => get_class($e),
            'message' => $e->getMessage()
        ], 500);
    }
})->name('test.post.size');

// Emergency bypass for PostTooLargeException
Route::get('/bypass-post-size-check', function() {
    $output = "<h2>🚨 Emergency PostTooLargeException Bypass</h2>";
    
    $output .= "<div style='background-color: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    $output .= "<h3>⚠️ Temporary Workaround</h3>";
    $output .= "<p>If you need to bypass the POST size check temporarily while fixing PHP configuration:</p>";
    $output .= "</div>";
    
    $output .= "<div style='background-color: #d1ecf1; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    $output .= "<h4>Option 1: Modify Laravel Middleware (Temporary)</h4>";
    $output .= "<p>Comment out the ValidatePostSize middleware in <code>bootstrap/app.php</code>:</p>";
    $output .= "<pre style='background-color: #f8f9fa; padding: 10px; border-radius: 5px;'>";
    $output .= "// ->withMiddleware(function (Middleware \$middleware) {\n";
    $output .= "//     \$middleware->validatePostSize();\n";
    $output .= "// })";
    $output .= "</pre>";
    $output .= "<p><strong>⚠️ Remember to re-enable after fixing PHP settings!</strong></p>";
    $output .= "</div>";
    
    $output .= "<div style='background-color: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    $output .= "<h4>Option 2: Fix WAMP Configuration (Recommended)</h4>";
    $output .= "<ol>";
    $output .= "<li>Use the WAMP icon → PHP → Switch to PHP version → Select your version</li>";
    $output .= "<li>Then WAMP icon → PHP → php.ini</li>";
    $output .= "<li>Make sure you're editing the ACTIVE php.ini file</li>";
    $output .= "<li>Save and restart ALL WAMP services</li>";
    $output .= "</ol>";
    $output .= "</div>";
    
    return $output;
})->name('bypass.post.size.check');