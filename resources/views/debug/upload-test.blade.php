<!DOCTYPE html>
<html>
<head>
    <title>Upload Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { margin-top: 20px; padding: 10px; border: 1px solid #ccc; }
        .success { background-color: #d4edda; }
        .error { background-color: #f8d7da; }
    </style>
</head>
<body>
    <h1>File Upload Test</h1>
    
    <form id="uploadForm" enctype="multipart/form-data">
        @csrf
        <div>
            <label>Select Images (multiple):</label><br>
            <input type="file" name="images[]" multiple accept="image/*" id="imageInput">
        </div>
        <br>
        <button type="submit">Test Upload</button>
    </form>
    
    <div id="results"></div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            const files = document.getElementById('imageInput').files;
            
            // Add CSRF token
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            // Add files
            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }
            
            try {
                const response = await fetch('/debug/upload-test', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                document.getElementById('results').innerHTML = 
                    '<div class="result ' + (result.success ? 'success' : 'error') + '">' +
                    '<h3>Result:</h3>' +
                    '<pre>' + JSON.stringify(result, null, 2) + '</pre>' +
                    '</div>';
            } catch (error) {
                document.getElementById('results').innerHTML = 
                    '<div class="result error">' +
                    '<h3>Error:</h3>' +
                    '<p>' + error.message + '</p>' +
                    '</div>';
            }
        });
        
        // Show file info when selected
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const files = e.target.files;
            let info = '<h3>Selected Files:</h3>';
            
            for (let i = 0; i < files.length; i++) {
                info += '<p><strong>File ' + (i + 1) + ':</strong> ' + 
                       files[i].name + ' (' + Math.round(files[i].size / 1024) + ' KB)</p>';
            }
            
            document.getElementById('results').innerHTML = '<div class="result">' + info + '</div>';
        });
    </script>
</body>
</html>