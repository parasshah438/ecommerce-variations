<!DOCTYPE html>
<html>
<head>
    <title>Upload Limits Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .info { background: #e3f2fd; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .warning { background: #fff3e0; padding: 10px; margin: 10px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Upload Limits Information</h1>
    
    <div class="info">
        <h3>Current PHP Settings:</h3>
        <ul>
            <li><strong>upload_max_filesize:</strong> <?php echo ini_get('upload_max_filesize'); ?></li>
            <li><strong>post_max_size:</strong> <?php echo ini_get('post_max_size'); ?></li>
            <li><strong>max_file_uploads:</strong> <?php echo ini_get('max_file_uploads'); ?></li>
            <li><strong>memory_limit:</strong> <?php echo ini_get('memory_limit'); ?></li>
            <li><strong>max_execution_time:</strong> <?php echo ini_get('max_execution_time'); ?> seconds</li>
            <li><strong>max_input_time:</strong> <?php echo ini_get('max_input_time'); ?> seconds</li>
        </ul>
    </div>
    
    <div class="warning">
        <h3>Analysis:</h3>
        <p><strong>Your files total:</strong> 2822 + 2356 + 1283 + 1395 = ~7.8MB</p>
        <p><strong>Individual file limit:</strong> <?php echo ini_get('upload_max_filesize'); ?> (OK - all files under limit)</p>
        <p><strong>Total POST limit:</strong> <?php echo ini_get('post_max_size'); ?> (Should be OK)</p>
        <p><strong>Max files allowed:</strong> <?php echo ini_get('max_file_uploads'); ?> (OK - you're uploading 4 files)</p>
    </div>
    
    <div class="info">
        <h3>Test Upload (One File at a Time):</h3>
        <form action="/debug/single-file-test" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="single_image" accept="image/*" required>
            <button type="submit">Test Single File</button>
        </form>
    </div>
    
    <div class="info">
        <h3>Test Upload (Multiple Files):</h3>
        <form action="/debug/upload-test" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="images[]" multiple accept="image/*" required>
            <button type="submit">Test Multiple Files</button>
        </form>
    </div>
</body>
</html>