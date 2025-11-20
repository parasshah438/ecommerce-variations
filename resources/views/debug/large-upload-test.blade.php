<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Large Upload Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="file"] { padding: 10px; border: 2px dashed #ccc; width: 100%; }
        button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
        .results { margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 4px; }
        .file-result { margin-bottom: 15px; padding: 15px; background: white; border-radius: 4px; border-left: 4px solid #007cba; }
        .success { border-left-color: #28a745; }
        .error { border-left-color: #dc3545; }
        .info { margin: 5px 0; font-size: 14px; }
        .loading { display: none; text-align: center; margin: 20px 0; }
        .progress { width: 100%; height: 20px; background: #f0f0f0; border-radius: 10px; margin: 10px 0; }
        .progress-bar { height: 100%; background: #007cba; border-radius: 10px; width: 0%; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Large File Upload Test (up to 5MB)</h1>
        
        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="test_images">Select Test Images (supports up to 5MB each):</label>
                <input type="file" 
                       name="test_images[]" 
                       id="test_images" 
                       multiple 
                       accept="image/jpeg,image/png,image/gif,image/webp"
                       required>
                <small>Supports: JPEG, PNG, GIF, WebP - Maximum 5MB per file</small>
            </div>
            
            <button type="submit">Upload and Test Processing</button>
        </form>
        
        <div class="loading" id="loading">
            <p>Processing uploads...</p>
            <div class="progress">
                <div class="progress-bar" id="progressBar"></div>
            </div>
        </div>
        
        <div id="results" class="results" style="display: none;">
            <h3>Upload Results:</h3>
            <div id="resultsContent"></div>
        </div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const loading = document.getElementById('loading');
            const results = document.getElementById('results');
            const progressBar = document.getElementById('progressBar');
            
            // Show loading
            loading.style.display = 'block';
            results.style.display = 'none';
            
            // Animate progress bar
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress > 90) progress = 90;
                progressBar.style.width = progress + '%';
            }, 100);
            
            fetch('/debug/large-upload-test', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                
                setTimeout(() => {
                    loading.style.display = 'none';
                    results.style.display = 'block';
                    displayResults(data);
                }, 500);
            })
            .catch(error => {
                clearInterval(progressInterval);
                loading.style.display = 'none';
                results.style.display = 'block';
                document.getElementById('resultsContent').innerHTML = `
                    <div class="file-result error">
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
            });
        });
        
        function displayResults(data) {
            const resultsContent = document.getElementById('resultsContent');
            
            if (!data.success) {
                resultsContent.innerHTML = `
                    <div class="file-result error">
                        <strong>Upload Failed:</strong> ${data.error}
                    </div>
                `;
                return;
            }
            
            let html = '';
            
            if (data.summary) {
                html += `
                    <div class="file-result">
                        <strong>Summary:</strong> 
                        ${data.summary.successful}/${data.summary.total_files} files processed successfully
                    </div>
                `;
            }
            
            data.files.forEach(file => {
                const success = file.processing && file.processing.success;
                const cssClass = success ? 'success' : 'error';
                
                html += `
                    <div class="file-result ${cssClass}">
                        <h4>${file.original_name}</h4>
                        <div class="info"><strong>Size:</strong> ${file.size_mb} MB (${file.size.toLocaleString()} bytes)</div>
                        <div class="info"><strong>MIME Type:</strong> ${file.mime_type}</div>
                        <div class="info"><strong>Upload Error Code:</strong> ${file.upload_error}</div>
                        <div class="info"><strong>Is Valid:</strong> ${file.is_valid ? 'Yes' : 'No'}</div>
                        
                        ${file.processing ? `
                            <div class="info"><strong>Processing Result:</strong> ${file.processing.success ? 'Success' : 'Failed'}</div>
                            ${file.processing.method_used ? `<div class="info"><strong>Method Used:</strong> ${file.processing.method_used}</div>` : ''}
                            ${file.processing.fallback_used ? `<div class="info"><strong>Fallback Used:</strong> Yes</div>` : ''}
                            ${file.processing.simple_store ? `<div class="info"><strong>Simple Store:</strong> Yes</div>` : ''}
                            ${file.processing.stored_path ? `<div class="info"><strong>Stored Path:</strong> ${file.processing.stored_path}</div>` : ''}
                            ${file.processing.file_exists ? `<div class="info"><strong>File Exists:</strong> ${file.processing.file_exists ? 'Yes' : 'No'}</div>` : ''}
                            ${file.processing.optimization_error ? `<div class="info"><strong>Optimization Error:</strong> ${file.processing.optimization_error}</div>` : ''}
                            ${file.processing.error ? `<div class="info" style="color: red;"><strong>Error:</strong> ${file.processing.error}</div>` : ''}
                        ` : ''}
                    </div>
                `;
            });
            
            resultsContent.innerHTML = html;
        }
        
        // File input change handler to show selected files
        document.getElementById('test_images').addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                console.log('Selected files:', files.map(f => ({
                    name: f.name,
                    size: f.size,
                    sizeMB: (f.size / (1024 * 1024)).toFixed(2)
                })));
            }
        });
    </script>
</body>
</html>