<!DOCTYPE html>
<html>
<head>
    <title>Basic Upload Test - No JavaScript</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; max-width: 800px; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="file"] { width: 100%; padding: 8px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <h1>Basic File Upload Test (No JavaScript)</h1>
    
    <div class="alert alert-info">
        <strong>This test:</strong>
        <ul>
            <li>Uses traditional HTML form submission (no JavaScript)</li>
            <li>Tests Laravel file upload functionality directly</li>
            <li>Shows detailed debug information</li>
        </ul>
        <strong>Current PHP Limits:</strong>
        <ul>
            <li>upload_max_filesize: {{ ini_get('upload_max_filesize') }}</li>
            <li>post_max_size: {{ ini_get('post_max_size') }}</li>
        </ul>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">
            <strong>Success:</strong> {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            <strong>Error:</strong> {{ session('error') }}
        </div>
    @endif
    
    @if(session('result'))
        <div class="alert alert-info">
            <strong>Test Result:</strong>
            <pre>{{ json_encode(session('result'), JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif
    
    @if(session('debug'))
        <div class="alert alert-info">
            <strong>Debug Info:</strong>
            <pre>{{ json_encode(session('debug'), JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif
    
    <form action="/debug/basic-upload-test" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="test_image">Select Image File:</label>
            <input type="file" name="test_image" id="test_image" accept="image/*" required>
            <small style="color: #666;">Try with a small image first (under 1MB)</small>
        </div>
        
        <button type="submit">Test Upload</button>
    </form>
    
    <h3>Troubleshooting Tips:</h3>
    <ul>
        <li><strong>File too large:</strong> Your 2.5MB file exceeds the effective server limit</li>
        <li><strong>Try smaller files:</strong> Test with images under 1MB first</li>
        <li><strong>Check format:</strong> Use JPG, PNG, GIF formats</li>
        <li><strong>Server limits:</strong> Despite showing 50M, the effective limit appears to be ~2MB</li>
    </ul>
</body>
</html>