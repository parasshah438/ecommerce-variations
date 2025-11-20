<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Optimizer Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .status-card {
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        .danger { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        
        .upload-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .file-input {
            margin: 10px 0;
            padding: 10px;
            border: 2px dashed #ccc;
            border-radius: 5px;
            text-align: center;
        }
        
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .results {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
            display: none;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
            display: none;
        }
        
        .progress-fill {
            height: 100%;
            background-color: #007bff;
            width: 0%;
            transition: width 0.3s ease;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖼️ Image Optimizer Test Suite</h1>
        <p>Testing Spatie Image Optimizer with Laravel Implementation</p>

        <div class="status-grid">
            <!-- Package Status -->
            <div class="status-card {{ $optimizer_exists ? 'success' : 'danger' }}">
                <h3>📦 Spatie Image Optimizer</h3>
                <p><strong>Status:</strong> {{ $optimizer_exists ? 'Installed ✅' : 'Not Installed ❌' }}</p>
            </div>

            <!-- Helper Status -->
            <div class="status-card {{ $helper_exists ? 'success' : 'danger' }}">
                <h3>🔧 ImageOptimizer Helper</h3>
                <p><strong>Status:</strong> {{ $helper_exists ? 'Available ✅' : 'Missing ❌' }}</p>
            </div>

            <!-- Intervention Image Status -->
            <div class="status-card {{ str_contains($intervention_image_version, 'Available') ? 'success' : 'danger' }}">
                <h3>🖼️ Intervention Image</h3>
                <p><strong>Status:</strong> {{ $intervention_image_version }}</p>
            </div>
        </div>

        <!-- Available Optimizers -->
        @if($optimizer_exists && !empty($available_optimizers))
        <div class="status-card info">
            <h3>🔧 Available Image Optimizers</h3>
            @if(isset($available_optimizers['error']))
                <p class="text-danger">Error: {{ $available_optimizers['error'] }}</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Optimizer</th>
                            <th>Can Handle</th>
                            <th>Binary</th>
                            <th>Binary Available</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($available_optimizers as $optimizer)
                        <tr>
                            <td>{{ basename($optimizer['class']) }}</td>
                            <td>{{ $optimizer['can_handle'] }}</td>
                            <td>{{ $optimizer['binary_available'] }}</td>
                            <td>{{ $optimizer['binary_exists'] ? 'Yes ✅' : 'No ❌' }}</td>
                            <td>
                                <span class="badge {{ $optimizer['status'] === 'Working' ? 'success' : 'warning' }}">
                                    {{ $optimizer['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @endif

        <!-- Directory Status -->
        <div class="status-card info">
            <h3>📁 Storage Directory Status</h3>
            <table>
                <thead>
                    <tr>
                        <th>Directory</th>
                        <th>Exists</th>
                        <th>Writable</th>
                        <th>Files Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($directory_status as $name => $status)
                    <tr>
                        <td>{{ $name }}</td>
                        <td>{{ $status['exists'] ? 'Yes ✅' : 'No ❌' }}</td>
                        <td>{{ $status['writable'] ? 'Yes ✅' : 'No ❌' }}</td>
                        <td>{{ $status['files_count'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- PHP Upload Settings -->
        <div class="status-card info">
            <h3>⚙️ PHP Upload Settings</h3>
            <table>
                @foreach($upload_settings as $setting => $value)
                <tr>
                    <td><strong>{{ str_replace('_', ' ', ucwords($setting)) }}:</strong></td>
                    <td>{{ $value }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <!-- Upload Test Section -->
        @if($optimizer_exists && $helper_exists)
        <div class="upload-section">
            <h3>🧪 Test Image Upload & Optimization</h3>
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="file-input">
                    <label for="test_image">
                        <strong>Choose an image to test optimization:</strong><br>
                        <small>Supported formats: JPEG, PNG, GIF, WebP (Max: 10MB)</small>
                    </label>
                    <br><br>
                    <input type="file" id="test_image" name="test_image" accept="image/*" required>
                </div>
                
                <div class="progress-bar" id="progressBar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                
                <button type="submit" class="btn">Upload & Optimize Image</button>
            </form>

            <div id="results" class="results">
                <!-- Results will be displayed here -->
            </div>
        </div>
        @else
        <div class="upload-section">
            <div class="status-card danger">
                <h3>❌ Upload Test Unavailable</h3>
                <p>Image optimization testing is not available because:</p>
                <ul>
                    @if(!$optimizer_exists)
                    <li>Spatie Image Optimizer is not installed</li>
                    @endif
                    @if(!$helper_exists)
                    <li>ImageOptimizer Helper class is missing</li>
                    @endif
                </ul>
            </div>
        </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadForm = document.getElementById('uploadForm');
            const resultsDiv = document.getElementById('results');
            const progressBar = document.getElementById('progressBar');
            const progressFill = document.getElementById('progressFill');

            if (uploadForm) {
                uploadForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const fileInput = document.getElementById('test_image');
                    
                    if (!fileInput.files[0]) {
                        alert('Please select an image file first.');
                        return;
                    }

                    // Show progress bar
                    progressBar.style.display = 'block';
                    progressFill.style.width = '0%';
                    resultsDiv.style.display = 'none';

                    // Simulate progress (since we can't track real upload progress easily)
                    let progress = 0;
                    const progressInterval = setInterval(() => {
                        progress += Math.random() * 15;
                        if (progress > 90) progress = 90;
                        progressFill.style.width = progress + '%';
                    }, 200);

                    fetch('/test-image-upload', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        clearInterval(progressInterval);
                        progressFill.style.width = '100%';
                        
                        setTimeout(() => {
                            progressBar.style.display = 'none';
                            displayResults(data);
                        }, 500);
                    })
                    .catch(error => {
                        clearInterval(progressInterval);
                        progressBar.style.display = 'none';
                        console.error('Error:', error);
                        displayResults({
                            success: false,
                            message: 'Upload failed: ' + error.message
                        });
                    });
                });
            }

            function displayResults(data) {
                resultsDiv.style.display = 'block';
                
                if (data.success) {
                    resultsDiv.innerHTML = `
                        <h4 style="color: green;">✅ Upload & Optimization Successful!</h4>
                        <table>
                            <tr><td><strong>Original Size:</strong></td><td>${data.original_size}</td></tr>
                            <tr><td><strong>Optimized Size:</strong></td><td>${data.optimized_size}</td></tr>
                            <tr><td><strong>Compression Ratio:</strong></td><td>${data.compression_ratio}%</td></tr>
                            <tr><td><strong>Files Generated:</strong></td><td>${data.files ? data.files.length : 'N/A'}</td></tr>
                        </table>
                        ${data.files ? '<p><strong>Generated Files:</strong> ' + data.files.join(', ') + '</p>' : ''}
                    `;
                } else {
                    resultsDiv.innerHTML = `
                        <h4 style="color: red;">❌ Upload Failed</h4>
                        <p><strong>Error:</strong> ${data.message}</p>
                        ${data.error_code ? '<p><strong>Error Code:</strong> ' + data.error_code + '</p>' : ''}
                    `;
                }
            }
        });
    </script>
</body>
</html>