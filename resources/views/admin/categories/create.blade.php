@extends('admin.layout')

@section('title', 'Create Category')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">Create New Category</h1>
                    <p class="text-muted mb-0">Add a new category to organize your products</p>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Categories
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" id="categoryForm">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-folder-plus me-2"></i>Category Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Category Name -->
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="Enter category name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Parent Category -->
                            <div class="col-md-12 mb-3">
                                <label for="parent_id" class="form-label">Parent Category</label>
                                <select class="form-select @error('parent_id') is-invalid @enderror" 
                                        id="parent_id" name="parent_id">
                                    <option value="">Select Parent Category (Optional)</option>
                                    @foreach($parentCategories as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Leave blank to create a root category</div>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" 
                                          placeholder="Enter category description (optional)">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-12 mb-4">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Active Status</strong>
                                        <div class="form-text">Enable this category to make it visible</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Upload Section -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-image me-2"></i>Category Image
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Upload Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            <div class="form-text">Supported formats: JPEG, PNG, JPG, GIF. Max size: 2MB</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div id="imagePreview" class="text-center" style="display: none;">
                            <div class="border rounded p-3 bg-light">
                                <img id="previewImg" src="" alt="Preview" class="img-fluid rounded" 
                                     style="max-height: 200px; width: 100%; object-fit: contain;">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="removePreview">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Placeholder -->
                        <div id="imagePlaceholder" class="text-center">
                            <div class="border border-dashed rounded p-4 bg-light">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">No image selected</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="bi bi-info-circle me-1"></i> Tips</h6>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-1">• Use clear, descriptive category names</li>
                            <li class="mb-1">• High-quality images improve user experience</li>
                            <li class="mb-1">• Consider organizing with parent categories</li>
                            <li>• You can always edit these details later</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Create Category
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const imagePlaceholder = document.getElementById('imagePlaceholder');
    const removePreview = document.getElementById('removePreview');

    // Handle image selection
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (2MB limit)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                this.value = '';
                return;
            }

            // Check file type
            if (!file.type.match('image.*')) {
                alert('Please select a valid image file');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
                imagePlaceholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle remove preview
    removePreview.addEventListener('click', function() {
        imageInput.value = '';
        imagePreview.style.display = 'none';
        imagePlaceholder.style.display = 'block';
        previewImg.src = '';
    });

    // Form validation
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        const nameInput = document.getElementById('name');
        if (!nameInput.value.trim()) {
            e.preventDefault();
            nameInput.focus();
            alert('Please enter a category name');
        }
    });

    // Auto-generate slug preview (optional enhancement)
    const nameInput = document.getElementById('name');
    nameInput.addEventListener('input', function() {
        // You can add slug preview functionality here if needed
    });
});
</script>

<style>
.border-dashed {
    border-style: dashed !important;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

#imagePreview img {
    transition: all 0.3s ease;
}

#imagePreview img:hover {
    transform: scale(1.05);
}
</style>
@endsection