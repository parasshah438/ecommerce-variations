<?php
/**
 * Memory Management and Performance Optimization Routes
 * These routes help diagnose and fix memory-related issues
 */

// Memory usage diagnostic
Route::get('/test-memory-usage', function() {
    $output = "<h2>💾 Memory Usage Diagnostic</h2>";
    
    // Current memory usage
    $memoryUsage = memory_get_usage(true);
    $memoryPeak = memory_get_peak_usage(true);
    $memoryLimit = ini_get('memory_limit');
    
    $output .= "<h3>📊 Current Memory Statistics:</h3>";
    $output .= "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    $output .= "<tr style='background-color: #f5f5f5;'><th style='padding: 10px;'>Metric</th><th style='padding: 10px;'>Value</th><th style='padding: 10px;'>Percentage of Limit</th></tr>";
    
    // Convert memory limit to bytes
    $limitBytes = convertToBytes($memoryLimit);
    $usagePercent = round(($memoryUsage / $limitBytes) * 100, 2);
    $peakPercent = round(($memoryPeak / $limitBytes) * 100, 2);
    
    $rows = [
        ['Current Usage', formatBytes($memoryUsage), $usagePercent . '%'],
        ['Peak Usage', formatBytes($memoryPeak), $peakPercent . '%'],
        ['Memory Limit', formatBytes($limitBytes), '100%'],
        ['Available', formatBytes($limitBytes - $memoryUsage), (100 - $usagePercent) . '%']
    ];
    
    foreach ($rows as $row) {
        $bgColor = '#ffffff';
        if (str_contains($row[0], 'Peak') && $peakPercent > 80) {
            $bgColor = '#f8d7da'; // Red for high usage
        } elseif (str_contains($row[0], 'Available') && (100 - $usagePercent) < 20) {
            $bgColor = '#fff3cd'; // Yellow for low available
        }
        
        $output .= "<tr style='background-color: {$bgColor};'>";
        foreach ($row as $cell) {
            $output .= "<td style='padding: 10px;'>{$cell}</td>";
        }
        $output .= "</tr>";
    }
    
    $output .= "</table>";
    
    // Memory recommendations
    $output .= "<h3>🎯 Recommendations:</h3>";
    if ($peakPercent > 80) {
        $output .= "<div style='background-color: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
        $output .= "<h4>⚠️ High Memory Usage Detected</h4>";
        $output .= "<p>Peak usage is {$peakPercent}% of the limit. Consider increasing memory_limit to at least 512M.</p>";
        $output .= "</div>";
    } else {
        $output .= "<div style='background-color: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
        $output .= "<h4>✅ Memory Usage is Normal</h4>";
        $output .= "<p>Current usage is {$usagePercent}%, which is within acceptable limits.</p>";
        $output .= "</div>";
    }
    
    // Test memory allocation
    $output .= "<h3>🧪 Memory Allocation Test:</h3>";
    try {
        // Try to allocate a large array to test memory limits
        $testArray = [];
        for ($i = 0; $i < 100000; $i++) {
            $testArray[] = str_repeat('x', 100); // 100 chars * 100k = ~10MB
        }
        unset($testArray); // Free memory
        
        $output .= "<p>✅ Successfully allocated and freed 10MB of test data.</p>";
    } catch (\Exception $e) {
        $output .= "<p>❌ Memory allocation test failed: " . $e->getMessage() . "</p>";
    }
    
    // Image processing memory estimation
    $output .= "<h3>🖼️ Image Processing Memory Requirements:</h3>";
    $output .= "<ul>";
    $output .= "<li><strong>2MP image (1920x1080):</strong> ~25MB RAM needed</li>";
    $output .= "<li><strong>5MP image (2560x1920):</strong> ~50MB RAM needed</li>";
    $output .= "<li><strong>12MP image (4000x3000):</strong> ~140MB RAM needed</li>";
    $output .= "</ul>";
    $output .= "<p><em>Note: Image processing typically requires 3-4x the image file size in RAM.</em></p>";
    
    // Quick actions
    $output .= "<h3>🔗 Quick Actions:</h3>";
    $output .= "<p>";
    $output .= "<a href='/test-safe-optimize' style='background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Test Safe Optimize</a>";
    $output .= "<a href='/php-config-helper' style='background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>PHP Config Helper</a>";
    $output .= "<a href='javascript:location.reload()' style='background-color: #ffc107; color: black; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Refresh Check</a>";
    $output .= "</p>";
    
    return $output;
    
})->name('test.memory.usage');

// Test the safe optimize command
Route::get('/test-safe-optimize', function() {
    $output = "<h2>🚀 Safe Optimize Command Test</h2>";
    
    $output .= "<div style='background-color: #d1ecf1; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    $output .= "<h3>💡 Optimization Methods Available:</h3>";
    
    $output .= "<h4>1. Manual Method (Recommended):</h4>";
    $output .= "<pre style='background-color: #f8f9fa; padding: 10px; border-radius: 5px;'>";
    $output .= "php -d memory_limit=1024M artisan optimize";
    $output .= "</pre>";
    
    $output .= "<h4>2. Using Custom Command:</h4>";
    $output .= "<pre style='background-color: #f8f9fa; padding: 10px; border-radius: 5px;'>";
    $output .= "php artisan optimize:safe";
    $output .= "</pre>";
    
    $output .= "<h4>3. Step by Step Method:</h4>";
    $output .= "<pre style='background-color: #f8f9fa; padding: 10px; border-radius: 5px;'>";
    $output .= "php artisan cache:clear\n";
    $output .= "php artisan config:clear\n";
    $output .= "php artisan route:clear\n";
    $output .= "php artisan view:clear\n";
    $output .= "php -d memory_limit=512M artisan config:cache\n";
    $output .= "php -d memory_limit=512M artisan route:cache\n";
    $output .= "php -d memory_limit=512M artisan view:cache";
    $output .= "</pre>";
    $output .= "</div>";
    
    // Test button
    $output .= "<div style='margin: 30px 0;'>";
    $output .= "<button onclick='runOptimizeTest()' style='background-color: #007bff; color: white; padding: 15px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>🚀 Run Safe Optimize Test</button>";
    $output .= "</div>";
    
    $output .= "<div id='optimize-result' style='margin: 20px 0; padding: 15px; border-radius: 5px; display: none;'></div>";
    
    $output .= "<script>
    async function runOptimizeTest() {
        const button = event.target;
        const result = document.getElementById('optimize-result');
        
        button.disabled = true;
        button.innerHTML = '⏳ Running optimization...';
        result.style.display = 'block';
        result.className = '';
        result.style.backgroundColor = '#d1ecf1';
        result.innerHTML = '<p>🔄 Testing optimization commands...</p>';
        
        try {
            const response = await fetch('/run-safe-optimize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || ''
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                result.style.backgroundColor = '#d4edda';
                result.innerHTML = `
                    <h4>✅ Optimization Completed Successfully!</h4>
                    <p><strong>Execution Time:</strong> \${data.execution_time}s</p>
                    <p><strong>Memory Peak:</strong> \${data.memory_peak}</p>
                    <p><strong>Commands Run:</strong> \${data.commands_run}</p>
                `;
            } else {
                result.style.backgroundColor = '#f8d7da';
                result.innerHTML = `
                    <h4>❌ Optimization Failed</h4>
                    <p><strong>Error:</strong> \${data.message}</p>
                `;
            }
        } catch (error) {
            result.style.backgroundColor = '#f8d7da';
            result.innerHTML = `
                <h4>❌ Request Failed</h4>
                <p><strong>Error:</strong> \${error.message}</p>
            `;
        } finally {
            button.disabled = false;
            button.innerHTML = '🚀 Run Safe Optimize Test';
        }
    }
    </script>";
    
    return $output;
})->name('test.safe.optimize');

// API endpoint to run safe optimize
Route::post('/run-safe-optimize', function() {
    $startTime = microtime(true);
    $startMemory = memory_get_usage(true);
    
    try {
        // Increase memory limit
        ini_set('memory_limit', '1024M');
        set_time_limit(600);
        
        $commands = ['cache:clear', 'config:clear', 'route:clear', 'view:clear'];
        $commandsRun = 0;
        
        // Run clearing commands first
        foreach ($commands as $command) {
            \Artisan::call($command);
            $commandsRun++;
        }
        
        // Run caching commands
        $cachingCommands = ['config:cache', 'route:cache', 'view:cache'];
        foreach ($cachingCommands as $command) {
            \Artisan::call($command);
            $commandsRun++;
        }
        
        $endTime = microtime(true);
        $peakMemory = memory_get_peak_usage(true);
        
        return response()->json([
            'success' => true,
            'execution_time' => round($endTime - $startTime, 2),
            'memory_peak' => formatBytes($peakMemory),
            'commands_run' => $commandsRun,
            'message' => 'Optimization completed successfully'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('run.safe.optimize');

// Memory cleanup utility
Route::get('/cleanup-memory', function() {
    // Force garbage collection
    gc_collect_cycles();
    
    // Clear opcache if available
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    
    // Get memory info
    $memoryBefore = memory_get_usage(true);
    $memoryAfter = memory_get_usage(true);
    
    return response()->json([
        'success' => true,
        'memory_before' => formatBytes($memoryBefore),
        'memory_after' => formatBytes($memoryAfter),
        'memory_freed' => formatBytes($memoryBefore - $memoryAfter),
        'gc_cycles' => gc_collect_cycles()
    ]);
})->name('cleanup.memory');