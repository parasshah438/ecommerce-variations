<!DOCTYPE html>
<html>
<head>
    <title>Product Creation Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test Product Creation Form</h1>
    
    @if($errors->any())
        <div style="background-color: #f8d7da; padding: 10px; margin: 10px 0; border: 1px solid red;">
            <h3>Validation Errors:</h3>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="/debug/test-product-creation" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div>
            <label>Product Name:</label>
            <input type="text" name="name" value="Test Product" required>
        </div>
        
        <div>
            <label>Description:</label>
            <textarea name="description" required>Test Description</textarea>
        </div>
        
        <div>
            <label>Category ID:</label>
            <input type="number" name="category_id" value="1" required>
        </div>
        
        <div>
            <label>Brand ID:</label>
            <input type="number" name="brand_id" value="1" required>
        </div>
        
        <div>
            <label>Price:</label>
            <input type="number" name="price" value="100" step="0.01" required>
        </div>
        
        <div>
            <label>Images (multiple):</label>
            <input type="file" name="images[]" multiple accept="image/*">
        </div>
        
        <button type="submit">Create Product</button>
    </form>
</body>
</html>