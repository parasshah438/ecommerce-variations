<!DOCTYPE html>
<html>
<head>
    <title>Simple Upload Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="file"] { width: 100%; padding: 8px; border: 1px solid #ddd; }
        button { background: #28a745; color: white; padding: 12px 20px; border: none; cursor: pointer; }
        button:hover { background: #218838; }
        .result { margin-top: 20px; padding: 15px; border-radius: 4px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Simple Upload Test</h1>
        <p>This tests basic Laravel file upload functionality without optimization.</p>
        
        <form id="simpleForm" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="test_image">Select Test Image:</label>
                <input type="file" id="test_image" name="test_image" accept="image/*" required>
            </div>
            
            <button type="submit">Test Basic Upload</button>
        </form>
        
        <div id="result"></div>
    </div>

    <script>
        document.getElementById('simpleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultDiv = document.getElementById('result');
            
            resultDiv.innerHTML = '<div class="info">Testing basic upload... Please wait.</div>';
            
            fetch('/debug/simple-upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="success">
                            <h3>✅ Basic Upload Successful!</h3>
                            <p>${data.message}</p>
                            <pre>${JSON.stringify(data.results, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error">
                            <h3>❌ Basic Upload Failed</h3>
                            <p><strong>Error:</strong> ${data.error}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="error">
                        <h3>❌ Request Failed</h3>
                        <p><strong>Error:</strong> ${error.message}</p>
                    </div>
                `;
            });
        });
    </script>
</body>
</html>