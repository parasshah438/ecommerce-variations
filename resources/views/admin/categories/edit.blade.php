@extends('admin.layout')

@section('title', 'Edit Category')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">Edit Category</h1>
                    <p class="text-muted mb-0">Update category information</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-outline-info">
                        <i class="bi bi-eye"></i> View Category
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Categories
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" id="categoryForm">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Category Information
                    </h5>
                </div>
                <div class="card-body">
                        <div class="row">
                            <!-- Category Name -->
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $category->name) }}" 
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
                                        <option value="{{ $parent->id }}" 
                                                {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Leave blank to make this a root category</div>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" 
                                          placeholder="Enter category description (optional)">{{ old('description', $category->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-12 mb-4">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Active Status</strong>
                                        <div class="form-text">Enable this category to make it visible</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                </div>
            </div>

            <!-- Category Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Category Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">{{ $category->products->count() }}</h4>
                                <small class="text-muted">Products</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border-end">
                                <h4 class="text-info mb-1">{{ $category->children->count() }}</h4>
                                <small class="text-muted">Subcategories</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h4 class="text-success mb-1">{{ $category->created_at->format('M Y') }}</h4>
                            <small class="text-muted">Created</small>
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
                    <!-- Current Image -->
                    @if($category->image)
                        <div id="currentImage" class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div class="text-center">
                                <div class="position-relative d-inline-block">
                                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" 
                                         class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: contain;">
                                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-1" 
                                            id="removeCurrentImage"
                                            onclick="removeImage({{ $category->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label for="image" class="form-label">
                            {{ $category->image ? 'Replace Image' : 'Upload Image' }}
                        </label>
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

                    <!-- Placeholder (when no current image) -->
                    @if(!$category->image)
                        <div id="imagePlaceholder" class="text-center">
                            <div class="border border-dashed rounded p-4 bg-light">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">No image selected</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card mt-3 border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Once you delete this category, it cannot be recovered. 
                        Make sure this category has no products or subcategories.
                    </p>
                    <button type="button" class="btn btn-outline-danger btn-sm" 
                            onclick="deleteCategory({{ $category->id }})">
                        <i class="bi bi-trash"></i> Delete Category
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Categories
                </a>
                <div>
                    <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-outline-primary me-2">
                        <i class="bi bi-eye me-1"></i>View Category
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Update Category
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Separate Delete Form (outside main form) -->
<form id="deleteForm" action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
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
                if (imagePlaceholder) {
                    imagePlaceholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle remove preview
    if (removePreview) {
        removePreview.addEventListener('click', function() {
            imageInput.value = '';
            imagePreview.style.display = 'none';
            if (imagePlaceholder) {
                imagePlaceholder.style.display = 'block';
            }
            previewImg.src = '';
        });
    }

    // Form validation
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        const nameInput = document.getElementById('name');
        if (!nameInput.value.trim()) {
            e.preventDefault();
            nameInput.focus();
            alert('Please enter a category name');
        }
    });
});

// Function to remove current image via AJAX
function removeImage(categoryId) {
    if (confirm('Are you sure you want to remove this image?')) {
        fetch(`/admin/categories/${categoryId}/remove-image`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('currentImage').style.display = 'none';
                // Show placeholder if no new image is being previewed
                if (document.getElementById('imagePlaceholder') && 
                    document.getElementById('imagePreview').style.display === 'none') {
                    document.getElementById('imagePlaceholder').style.display = 'block';
                }
                // Show success message
                showAlert('success', data.message);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Error removing image');
        });
    }
}

// Function to delete category
function deleteCategory(categoryId) {
    if (confirm('Are you absolutely sure? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}

// Function to show alert messages
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="bi bi-check-circle me-1"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
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

#imagePreview img, #currentImage img {
    transition: all 0.3s ease;
}

#imagePreview img:hover, #currentImage img:hover {
    transform: scale(1.05);
}

.position-relative .btn {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endsection