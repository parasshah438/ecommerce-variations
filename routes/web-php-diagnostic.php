<?php
/**
 * Web Server PHP Info Diagnostic
 * This route shows the ACTUAL PHP settings used by the web server
 */

Route::get('/web-php-info', function() {
    // Get web server PHP settings
    $webSettings = [
        'PHP Version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'max_input_vars' => ini_get('max_input_vars'),
        'Configuration File' => php_ini_loaded_file(),
    ];
    
    $output = "<h2>🌐 Web Server PHP Configuration</h2>";
    $output .= "<p><strong>This shows the ACTUAL settings used by your web server (Apache/WAMP)</strong></p>";
    
    // Required settings for comparison
    $requiredSettings = [
        'memory_limit' => '512M',
        'upload_max_filesize' => '50M', 
        'post_max_size' => '100M',
        'max_execution_time' => '300',
        'max_file_uploads' => '50'
    ];
    
    $output .= "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    $output .= "<tr style='background-color: #f5f5f5;'><th style='padding: 10px;'>Setting</th><th style='padding: 10px;'>Web Server Value</th><th style='padding: 10px;'>Required</th><th style='padding: 10px;'>Status</th></tr>";
    
    $hasIssues = false;
    
    foreach ($webSettings as $setting => $value) {
        if (isset($requiredSettings[$setting])) {
            $required = $requiredSettings[$setting];
            $isCorrect = false;
            
            // Special comparison for memory/size values
            if (in_array($setting, ['memory_limit', 'upload_max_filesize', 'post_max_size'])) {
                $currentBytes = convertToBytes($value);
                $requiredBytes = convertToBytes($required);
                $isCorrect = $currentBytes >= $requiredBytes;
            } else {
                $isCorrect = $value >= $required || ($setting == 'max_execution_time' && $value == '0');
            }
            
            if (!$isCorrect) {
                $hasIssues = true;
            }
            
            $status = $isCorrect ? "✅ OK" : "❌ TOO LOW";
            $bgColor = $isCorrect ? "#d4edda" : "#f8d7da";
            
            $output .= "<tr style='background-color: {$bgColor};'>";
            $output .= "<td style='padding: 10px; font-weight: bold;'>{$setting}</td>";
            $output .= "<td style='padding: 10px; font-family: monospace;'>{$value}</td>";
            $output .= "<td style='padding: 10px; font-family: monospace;'>{$required}</td>";
            $output .= "<td style='padding: 10px;'>{$status}</td>";
            $output .= "</tr>";
        } else {
            // Show info-only settings
            $output .= "<tr style='background-color: #e2e3e5;'>";
            $output .= "<td style='padding: 10px; font-weight: bold;'>{$setting}</td>";
            $output .= "<td style='padding: 10px; font-family: monospace;' colspan='3'>{$value}</td>";
            $output .= "</tr>";
        }
    }
    
    $output .= "</table>";
    
    // Problem diagnosis
    if ($hasIssues) {
        $output .= "<div style='background-color: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        $output .= "<h3>🚨 PROBLEM IDENTIFIED!</h3>";
        $output .= "<p><strong>Your web server is using different PHP settings than expected.</strong></p>";
        
        $currentPostSize = convertToBytes(ini_get('post_max_size'));
        $currentUploadSize = convertToBytes(ini_get('upload_max_filesize'));
        
        $output .= "<p><strong>Current POST limit:</strong> " . formatBytes($currentPostSize) . "</p>";
        $output .= "<p><strong>Current upload limit:</strong> " . formatBytes($currentUploadSize) . "</p>";
        $output .= "<p>This explains why you're getting the PostTooLargeException!</p>";
        $output .= "</div>";
    } else {
        $output .= "<div style='background-color: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        $output .= "<h3>✅ Configuration Looks Good!</h3>";
        $output .= "<p>Web server settings are correct. The issue might be elsewhere.</p>";
        $output .= "</div>";
    }
    
    // WAMP-specific solutions
    $output .= "<div style='background-color: #d1ecf1; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    $output .= "<h3>🔧 WAMP-Specific Solutions:</h3>";
    
    $output .= "<h4>Solution 1: Check PHP Version Mismatch</h4>";
    $output .= "<ol>";
    $output .= "<li>Left-click WAMP icon → <strong>PHP</strong> → <strong>Version</strong></li>";
    $output .= "<li>Make sure the selected version matches: <code>" . PHP_VERSION . "</code></li>";
    $output .= "<li>If different, select the correct version and restart</li>";
    $output .= "</ol>";
    
    $output .= "<h4>Solution 2: Force Apache PHP Settings</h4>";
    $output .= "<ol>";
    $output .= "<li>Left-click WAMP icon → <strong>Apache</strong> → <strong>httpd.conf</strong></li>";
    $output .= "<li>Add these lines at the bottom:</li>";
    $output .= "</ol>";
    $output .= "<pre style='background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace;'>";
    $output .= "php_value upload_max_filesize 50M\n";
    $output .= "php_value post_max_size 100M\n";
    $output .= "php_value memory_limit 512M\n";
    $output .= "php_value max_execution_time 300\n";
    $output .= "php_value max_file_uploads 50";
    $output .= "</pre>";
    $output .= "<p>Then restart Apache.</p>";
    
    $output .= "<h4>Solution 3: .htaccess Override (Project-Specific)</h4>";
    $output .= "<p>Add to your project's <code>public/.htaccess</code> file:</p>";
    $output .= "<pre style='background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace;'>";
    $output .= "php_value upload_max_filesize 50M\n";
    $output .= "php_value post_max_size 100M\n";
    $output .= "php_value memory_limit 512M\n";
    $output .= "php_value max_execution_time 300";
    $output .= "</pre>";
    
    $output .= "</div>";
    
    // Quick test
    $output .= "<div style='background-color: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    $output .= "<h3>🧪 Quick Test</h3>";
    $output .= "<button onclick='testPostLimit()' style='background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Current POST Limit</button>";
    $output .= "<div id='test-result' style='margin: 15px 0; padding: 10px; border-radius: 5px; display: none;'></div>";
    $output .= "</div>";
    
    $output .= "<script>
    async function testPostLimit() {
        const button = event.target;
        const result = document.getElementById('test-result');
        
        button.disabled = true;
        button.innerHTML = '⏳ Testing...';
        result.style.display = 'block';
        result.style.backgroundColor = '#d1ecf1';
        result.innerHTML = '<p>Testing POST size limits...</p>';
        
        // Create a large POST request
        const formData = new FormData();
        
        // Add dummy data to simulate large POST
        for (let i = 0; i < 100; i++) {
            formData.append('dummy_' + i, 'x'.repeat(1000)); // 100KB of dummy data
        }
        
        try {
            const response = await fetch('/test-post-size', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || ''
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                result.style.backgroundColor = '#d4edda';
                result.innerHTML = `
                    <h4>✅ POST Test Successful!</h4>
                    <p><strong>POST Data Size:</strong> \${data.post_data_size}</p>
                    <p><strong>Server Limits:</strong> post_max_size = \${data.php_limits.post_max_size}</p>
                `;
            } else {
                result.style.backgroundColor = '#f8d7da';
                result.innerHTML = `
                    <h4>❌ POST Test Failed</h4>
                    <p><strong>Error:</strong> \${data.message}</p>
                    <p><strong>Current Limit:</strong> \${data.php_limits.post_max_size}</p>
                `;
            }
        } catch (error) {
            result.style.backgroundColor = '#f8d7da';
            result.innerHTML = `
                <h4>❌ Network Error</h4>
                <p>Request failed: \${error.message}</p>
                <p>This might indicate a server configuration issue.</p>
            `;
        } finally {
            button.disabled = false;
            button.innerHTML = 'Test Current POST Limit';
        }
    }
    </script>";
    
    return $output;
    
})->name('web.php.info');