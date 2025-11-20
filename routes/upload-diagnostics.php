<?php
/**
 * Upload Diagnostics and Configuration Test Routes
 * These routes help diagnose and fix upload issues
 */

// PHP Upload Configuration Diagnostic
Route::get('/test-upload-config', function() {
    $output = "<h2>📋 PHP Upload Configuration Diagnostic</h2>";
    
    // Current PHP settings
    $settings = [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'max_input_vars' => ini_get('max_input_vars'),
        'max_input_time' => ini_get('max_input_time'),
    ];
    
    $output .= "<h3>🔧 Current PHP Settings:</h3>";
    $output .= "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    $output .= "<tr style='background-color: #f5f5f5;'><th style='padding: 10px;'>Setting</th><th style='padding: 10px;'>Current Value</th><th style='padding: 10px;'>Recommended</th><th style='padding: 10px;'>Status</th></tr>";
    
    $recommendations = [
        'upload_max_filesize' => ['recommended' => '50M', 'min' => '10M'],
        'post_max_size' => ['recommended' => '100M', 'min' => '20M'],
        'max_execution_time' => ['recommended' => '300', 'min' => '60'],
        'memory_limit' => ['recommended' => '256M', 'min' => '128M'],
        'max_file_uploads' => ['recommended' => '50', 'min' => '20'],
        'max_input_vars' => ['recommended' => '3000', 'min' => '1000'],
        'max_input_time' => ['recommended' => '300', 'min' => '60'],
    ];
    
    foreach ($settings as $setting => $value) {
        $rec = $recommendations[$setting] ?? ['recommended' => 'N/A', 'min' => 'N/A'];
        
        // Convert values to bytes for comparison
        $currentBytes = $setting === 'max_execution_time' || $setting === 'max_file_uploads' || $setting === 'max_input_vars' || $setting === 'max_input_time'
            ? (int)$value
            : $this->convertToBytes($value);
        
        $minBytes = $setting === 'max_execution_time' || $setting === 'max_file_uploads' || $setting === 'max_input_vars' || $setting === 'max_input_time'
            ? (int)$rec['min']
            : $this->convertToBytes($rec['min']);
        
        $status = $currentBytes >= $minBytes ? "✅ OK" : "❌ Too Low";
        $rowColor = $currentBytes >= $minBytes ? "#d4edda" : "#f8d7da";
        
        $output .= "<tr style='background-color: {$rowColor};'>";
        $output .= "<td style='padding: 10px; font-weight: bold;'>{$setting}</td>";
        $output .= "<td style='padding: 10px;'>{$value}</td>";
        $output .= "<td style='padding: 10px;'>{$rec['recommended']}</td>";
        $output .= "<td style='padding: 10px;'>{$status}</td>";
        $output .= "</tr>";
    }
    
    $output .= "</table>";
    
    // PHP.ini file location
    $output .= "<h3>📂 Configuration File Location:</h3>";
    $output .= "<p><strong>PHP.ini Path:</strong> <code>" . php_ini_loaded_file() . "</code></p>";
    
    // Test image upload capacity
    $uploadMaxBytes = $this->convertToBytes($settings['upload_max_filesize']);
    $postMaxBytes = $this->convertToBytes($settings['post_max_size']);
    $effectiveLimit = min($uploadMaxBytes, $postMaxBytes);
    
    $output .= "<h3>📊 Upload Capacity Analysis:</h3>";
    $output .= "<ul>";
    $output .= "<li><strong>Max Single File Size:</strong> " . $this->formatBytes($uploadMaxBytes) . "</li>";
    $output .= "<li><strong>Max POST Data Size:</strong> " . $this->formatBytes($postMaxBytes) . "</li>";
    $output .= "<li><strong>Effective Upload Limit:</strong> " . $this->formatBytes($effectiveLimit) . "</li>";
    $output .= "</ul>";
    
    if ($effectiveLimit < (5 * 1024 * 1024)) { // Less than 5MB
        $output .= "<div style='background-color: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
        $output .= "<h4>⚠️ Configuration Issue Detected</h4>";
        $output .= "<p>Your current settings only allow uploads up to " . $this->formatBytes($effectiveLimit) . ". This is insufficient for high-quality product images.</p>";
        $output .= "</div>";
    }
    
    // Recommendations
    $output .= "<h3>🛠️ How to Fix:</h3>";
    $output .= "<div style='background-color: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px;'>";
    $output .= "<h4>For WAMP Users:</h4>";
    $output .= "<ol>";
    $output .= "<li>Click on the WAMP icon in your system tray</li>";
    $output .= "<li>Go to <strong>PHP → PHP Settings</strong></li>";
    $output .= "<li>Or edit the php.ini file directly at: <code>" . php_ini_loaded_file() . "</code></li>";
    $output .= "<li>Update these settings and restart WAMP:</li>";
    $output .= "</ol>";
    $output .= "<pre style='background-color: #f8f9fa; padding: 10px; border-radius: 5px;'>";
    $output .= "upload_max_filesize = 50M\n";
    $output .= "post_max_size = 100M\n";
    $output .= "max_execution_time = 300\n";
    $output .= "memory_limit = 256M\n";
    $output .= "max_file_uploads = 50\n";
    $output .= "max_input_vars = 3000\n";
    $output .= "max_input_time = 300";
    $output .= "</pre>";
    $output .= "</div>";
    
    // Test links
    $output .= "<h3>🔗 Quick Actions:</h3>";
    $output .= "<p>";
    $output .= "<a href='/admin/products/create' style='background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Test Admin Upload</a>";
    $output .= "<a href='/test-image-optimization' style='background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Test Optimization</a>";
    $output .= "<a href='javascript:location.reload()' style='background-color: #ffc107; color: black; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Refresh Check</a>";
    $output .= "</p>";
    
    return $output;
    
})->name('test.upload.config');

// Helper function for the route (since we can't use class methods in closures easily)
if (!function_exists('convertToBytes')) {
    function convertToBytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $number = (int) $value;
        
        switch($last) {
            case 'g':
                $number *= 1024;
            case 'm':
                $number *= 1024;
            case 'k':
                $number *= 1024;
        }
        
        return $number;
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// Test large file upload simulation
Route::get('/test-large-upload-form', function() {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Large File Upload Test</title>
        <meta name="csrf-token" content="' . csrf_token() . '">
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .upload-area { 
                border: 2px dashed #ccc; 
                padding: 40px; 
                text-align: center; 
                margin: 20px 0;
                border-radius: 10px;
            }
            .upload-area.dragover { 
                border-color: #007bff; 
                background-color: #f8f9fa; 
            }
            .progress-bar {
                width: 100%;
                height: 20px;
                background-color: #f0f0f0;
                border-radius: 10px;
                margin: 10px 0;
                overflow: hidden;
                display: none;
            }
            .progress-fill {
                height: 100%;
                background-color: #007bff;
                width: 0%;
                transition: width 0.3s ease;
            }
            .result { 
                margin: 20px 0; 
                padding: 15px; 
                border-radius: 5px; 
                display: none;
            }
            .success { 
                background-color: #d4edda; 
                border: 1px solid #c3e6cb; 
                color: #155724; 
            }
            .error { 
                background-color: #f8d7da; 
                border: 1px solid #f5c6cb; 
                color: #721c24; 
            }
        </style>
    </head>
    <body>
        <h2>📤 Large File Upload Test</h2>
        
        <div class="upload-area" id="uploadArea">
            <p>🖼️ Drop large images here or click to select</p>
            <p><small>Test with 2MB+ images to verify configuration</small></p>
            <input type="file" id="fileInput" multiple accept="image/*" style="display: none;">
            <button onclick="document.getElementById(\'fileInput\').click()" 
                    style="background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                Select Images
            </button>
        </div>
        
        <div class="progress-bar" id="progressBar">
            <div class="progress-fill" id="progressFill"></div>
        </div>
        
        <div id="result" class="result"></div>
        
        <div id="fileInfo" style="margin: 20px 0;"></div>
        
        <script>
            const uploadArea = document.getElementById("uploadArea");
            const fileInput = document.getElementById("fileInput");
            const progressBar = document.getElementById("progressBar");
            const progressFill = document.getElementById("progressFill");
            const result = document.getElementById("result");
            const fileInfo = document.getElementById("fileInfo");
            
            // Drag and drop handlers
            uploadArea.addEventListener("dragover", (e) => {
                e.preventDefault();
                uploadArea.classList.add("dragover");
            });
            
            uploadArea.addEventListener("dragleave", () => {
                uploadArea.classList.remove("dragover");
            });
            
            uploadArea.addEventListener("drop", (e) => {
                e.preventDefault();
                uploadArea.classList.remove("dragover");
                const files = e.dataTransfer.files;
                handleFiles(files);
            });
            
            fileInput.addEventListener("change", (e) => {
                handleFiles(e.target.files);
            });
            
            function handleFiles(files) {
                if (files.length === 0) return;
                
                // Display file info
                let infoHtml = "<h3>📁 Selected Files:</h3>";
                for (let file of files) {
                    const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                    infoHtml += `<p>• <strong>${file.name}</strong> - ${sizeInMB} MB (${file.type})</p>`;
                }
                fileInfo.innerHTML = infoHtml;
                
                // Test with first file
                uploadFile(files[0]);
            }
            
            function uploadFile(file) {
                const formData = new FormData();
                formData.append("test_image", file);
                formData.append("_token", document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content"));
                
                progressBar.style.display = "block";
                result.style.display = "none";
                
                const xhr = new XMLHttpRequest();
                
                xhr.upload.addEventListener("progress", (e) => {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressFill.style.width = percentComplete + "%";
                    }
                });
                
                xhr.addEventListener("load", () => {
                    progressBar.style.display = "none";
                    result.style.display = "block";
                    
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            result.className = "result success";
                            result.innerHTML = `
                                <h4>✅ Upload Successful!</h4>
                                <p><strong>Original Size:</strong> ${response.original_size}</p>
                                <p><strong>Optimized Size:</strong> ${response.optimized_size}</p>
                                <p><strong>Compression:</strong> ${response.compression_ratio}%</p>
                                <p><strong>Files Generated:</strong> ${response.files.length}</p>
                            `;
                        } else {
                            result.className = "result error";
                            result.innerHTML = `<h4>❌ Upload Failed</h4><p>${response.message}</p>`;
                        }
                    } else {
                        result.className = "result error";
                        result.innerHTML = `<h4>❌ Server Error</h4><p>HTTP ${xhr.status}: ${xhr.statusText}</p>`;
                    }
                });
                
                xhr.addEventListener("error", () => {
                    progressBar.style.display = "none";
                    result.style.display = "block";
                    result.className = "result error";
                    result.innerHTML = `<h4>❌ Network Error</h4><p>Failed to upload file. Check your network connection and server configuration.</p>`;
                });
                
                xhr.open("POST", "/test-image-upload");
                xhr.send(formData);
            }
        </script>
    </body>
    </html>';
})->name('test.large.upload.form');

// Configuration helper for WAMP users
Route::get('/php-config-helper', function() {
    $phpIniPath = php_ini_loaded_file();
    $isWamp = strpos($phpIniPath, 'wamp') !== false;
    
    $output = "<h2>🔧 PHP Configuration Helper</h2>";
    
    if ($isWamp) {
        $output .= "<div style='background-color: #d1ecf1; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        $output .= "<h3>🟢 WAMP Server Detected</h3>";
        $output .= "<p><strong>PHP.ini Location:</strong> <code>{$phpIniPath}</code></p>";
        
        $output .= "<h4>Quick WAMP Configuration Steps:</h4>";
        $output .= "<ol>";
        $output .= "<li>🔴 Left-click on the WAMP icon in your system tray</li>";
        $output .= "<li>📋 Select <strong>PHP</strong> → <strong>PHP Settings</strong></li>";
        $output .= "<li>📝 Or edit the php.ini file directly</li>";
        $output .= "<li>🔄 Restart all WAMP services after changes</li>";
        $output .= "</ol>";
        
        $output .= "<h4>Settings to Update:</h4>";
        $output .= "<textarea rows='8' cols='60' readonly style='font-family: monospace; background-color: #f8f9fa; padding: 10px; border-radius: 5px;'>";
        $output .= "upload_max_filesize = 50M\n";
        $output .= "post_max_size = 100M\n";
        $output .= "max_execution_time = 300\n";
        $output .= "memory_limit = 256M\n";
        $output .= "max_file_uploads = 50\n";
        $output .= "max_input_vars = 3000\n";
        $output .= "max_input_time = 300";
        $output .= "</textarea>";
        
        $output .= "</div>";
    } else {
        $output .= "<div style='background-color: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        $output .= "<h3>⚠️ Non-WAMP Server Detected</h3>";
        $output .= "<p><strong>PHP.ini Location:</strong> <code>{$phpIniPath}</code></p>";
        $output .= "<p>Please edit the php.ini file manually and restart your web server.</p>";
        $output .= "</div>";
    }
    
    $output .= "<div style='margin: 30px 0;'>";
    $output .= "<a href='/test-upload-config' style='background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Check Current Config</a>";
    $output .= "<a href='/test-large-upload-form' style='background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Test Upload</a>";
    $output .= "</div>";
    
    return $output;
})->name('php.config.helper');