<?php
/**
 * Document Root and .htaccess Diagnostic
 * This route helps identify the correct document root and .htaccess location
 */

Route::get('/check-document-root', function() {
    $output = "<h2>📂 Document Root & .htaccess Diagnostic</h2>";
    
    // Get server information
    $serverInfo = [
        'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'Not set',
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'Not set', 
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'Not set',
        'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'Not set',
        'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'Not set',
    ];
    
    $output .= "<h3>🌐 Server Path Information:</h3>";
    $output .= "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    $output .= "<tr style='background-color: #f5f5f5;'><th style='padding: 10px;'>Server Variable</th><th style='padding: 10px;'>Value</th></tr>";
    
    foreach ($serverInfo as $key => $value) {
        $output .= "<tr>";
        $output .= "<td style='padding: 10px; font-weight: bold;'>{$key}</td>";
        $output .= "<td style='padding: 10px; font-family: monospace;'>{$value}</td>";
        $output .= "</tr>";
    }
    $output .= "</table>";
    
    // Check various .htaccess locations
    $possibleHtaccessPaths = [
        'Project Root' => base_path('.htaccess'),
        'Public Folder' => public_path('.htaccess'),
        'Document Root' => ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/.htaccess',
    ];
    
    $output .= "<h3>📄 .htaccess File Locations:</h3>";
    $output .= "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    $output .= "<tr style='background-color: #f5f5f5;'><th style='padding: 10px;'>Location</th><th style='padding: 10px;'>Path</th><th style='padding: 10px;'>Exists</th><th style='padding: 10px;'>Size</th></tr>";
    
    foreach ($possibleHtaccessPaths as $location => $path) {
        $exists = file_exists($path);
        $size = $exists ? filesize($path) . ' bytes' : 'N/A';
        $status = $exists ? "✅ Yes" : "❌ No";
        $bgColor = $exists ? "#d4edda" : "#f8d7da";
        
        $output .= "<tr style='background-color: {$bgColor};'>";
        $output .= "<td style='padding: 10px; font-weight: bold;'>{$location}</td>";
        $output .= "<td style='padding: 10px; font-family: monospace; font-size: 12px;'>{$path}</td>";
        $output .= "<td style='padding: 10px;'>{$status}</td>";
        $output .= "<td style='padding: 10px;'>{$size}</td>";
        $output .= "</tr>";
    }
    $output .= "</table>";
    
    // Check if PHP values are being overridden
    $currentWebSettings = [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
    ];
    
    $output .= "<h3>🔧 Current Web Server PHP Settings:</h3>";
    $output .= "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    $output .= "<tr style='background-color: #f5f5f5;'><th style='padding: 10px;'>Setting</th><th style='padding: 10px;'>Value</th><th style='padding: 10px;'>Status</th></tr>";
    
    $requiredValues = [
        'upload_max_filesize' => '50M',
        'post_max_size' => '100M', 
        'memory_limit' => '512M',
        'max_execution_time' => '300'
    ];
    
    foreach ($currentWebSettings as $setting => $value) {
        $required = $requiredValues[$setting];
        $isCorrect = false;
        
        if (in_array($setting, ['upload_max_filesize', 'post_max_size', 'memory_limit'])) {
            $currentBytes = convertToBytes($value);
            $requiredBytes = convertToBytes($required);
            $isCorrect = $currentBytes >= $requiredBytes;
        } else {
            $isCorrect = $value >= $required || ($setting == 'max_execution_time' && $value == '0');
        }
        
        $status = $isCorrect ? "✅ OK" : "❌ TOO LOW";
        $bgColor = $isCorrect ? "#d4edda" : "#f8d7da";
        
        $output .= "<tr style='background-color: {$bgColor};'>";
        $output .= "<td style='padding: 10px; font-weight: bold;'>{$setting}</td>";
        $output .= "<td style='padding: 10px; font-family: monospace;'>{$value}</td>";
        $output .= "<td style='padding: 10px;'>{$status}</td>";
        $output .= "</tr>";
    }
    $output .= "</table>";
    
    // WAMP-specific recommendations
    $output .= "<div style='background-color: #d1ecf1; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    $output .= "<h3>🎯 WAMP Document Root Solutions:</h3>";
    
    $output .= "<h4>Most Likely Issue:</h4>";
    $output .= "<p>Your WAMP virtual host is pointing to <code>C:\\wamp64\\www\\test\\12\\variations</code> instead of <code>C:\\wamp64\\www\\test\\12\\variations\\public</code></p>";
    
    $output .= "<h4>Solution 1: Fix WAMP Virtual Host (Recommended)</h4>";
    $output .= "<ol>";
    $output .= "<li>Left-click WAMP icon → <strong>Apache</strong> → <strong>httpd-vhosts.conf</strong></li>";
    $output .= "<li>Find your project's virtual host configuration</li>";
    $output .= "<li>Change <code>DocumentRoot</code> from:<br>";
    $output .= "<code>C:/wamp64/www/test/12/variations</code><br>";
    $output .= "to:<br>";
    $output .= "<code>C:/wamp64/www/test/12/variations/public</code></li>";
    $output .= "<li>Restart Apache</li>";
    $output .= "</ol>";
    
    $output .= "<h4>Solution 2: Use Project Root .htaccess (Already Applied)</h4>";
    $output .= "<p>I've created an .htaccess file in your project root with PHP overrides.</p>";
    
    $output .= "<h4>Solution 3: Alternative Apache Config</h4>";
    $output .= "<p>Add to Apache httpd.conf or virtual host:</p>";
    $output .= "<pre style='background-color: #f8f9fa; padding: 10px; border-radius: 5px;'>";
    $output .= "&lt;Directory \"C:/wamp64/www/test/12/variations\"&gt;\n";
    $output .= "    php_value upload_max_filesize 50M\n";
    $output .= "    php_value post_max_size 100M\n";
    $output .= "    php_value memory_limit 512M\n";
    $output .= "    php_value max_execution_time 300\n";
    $output .= "&lt;/Directory&gt;";
    $output .= "</pre>";
    $output .= "</div>";
    
    // Test buttons
    $output .= "<div style='background-color: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    $output .= "<h3>🧪 Quick Tests:</h3>";
    $output .= "<button onclick='testCurrentSettings()' style='background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px;'>Test Current Settings</button>";
    $output .= "<button onclick='window.location.reload()' style='background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px;'>Refresh Check</button>";
    $output .= "<a href='/admin/products/create' style='background-color: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>Test Admin Upload</a>";
    $output .= "<div id='test-result' style='margin: 15px 0; padding: 10px; border-radius: 5px; display: none;'></div>";
    $output .= "</div>";
    
    $output .= "<script>
    async function testCurrentSettings() {
        const result = document.getElementById('test-result');
        result.style.display = 'block';
        result.style.backgroundColor = '#d1ecf1';
        result.innerHTML = '<p>⏳ Testing PHP configuration...</p>';
        
        try {
            const response = await fetch('/web-php-info');
            const data = await response.text();
            
            // Check if settings are correct by looking for specific values
            const hasCorrectPost = data.includes('post_max_size') && (data.includes('100M') || data.includes('104857600'));
            const hasCorrectUpload = data.includes('upload_max_filesize') && (data.includes('50M') || data.includes('52428800'));
            
            if (hasCorrectPost && hasCorrectUpload) {
                result.style.backgroundColor = '#d4edda';
                result.innerHTML = '<h4>✅ Configuration Applied Successfully!</h4><p>PHP settings are now correct. Try uploading to admin panel.</p>';
            } else {
                result.style.backgroundColor = '#f8d7da';
                result.innerHTML = '<h4>❌ Configuration Not Applied Yet</h4><p>Settings still need to be updated. Check WAMP configuration.</p>';
            }
        } catch (error) {
            result.style.backgroundColor = '#f8d7da';
            result.innerHTML = '<h4>❌ Test Failed</h4><p>Error: ' + error.message + '</p>';
        }
    }
    </script>";
    
    return $output;
    
})->name('check.document.root');