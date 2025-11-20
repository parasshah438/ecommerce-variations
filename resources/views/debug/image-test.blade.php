<!DOCTYPE html>
<html>
<head>
    <title>Image Upload Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test Image Upload</h1>
    
    <form action="/debug/test-image-upload" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="image">Select Image:</label>
            <input type="file" name="image" id="image" accept="image/*" required>
        </div>
        <button type="submit">Test Upload</button>
    </form>
    
    <div style="margin-top: 20px;">
        <h3>Optimizer Status:</h3>
        <div id="status">Loading...</div>
    </div>
    
    <script>
        // Load optimizer status
        fetch('/debug/image-optimizer-status')
            .then(response => response.json())
            .then(data => {
                document.getElementById('status').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('status').innerHTML = 'Error: ' + error.message;
            });
    </script>
</body>
</html>