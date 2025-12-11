@extends('admin.layout')

@section('title', 'Create Slider')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">Create New Slider</h1>
                    <p class="text-muted mb-0">Add a new slider to your website</p>
                </div>
                <a href="{{ route('admin.sliders.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Sliders
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Create Form -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Slider Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.sliders.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                Slider Title <span class="text-muted">(Optional)</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   placeholder="Enter slider title">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Description <span class="text-muted">(Optional)</span>
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Enter slider description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">
                                Slider Image <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   id="image" 
                                   name="image" 
                                   accept="image/*"
                                   required>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> 
                                Upload image (JPEG, PNG, GIF, WebP). Max 5MB. 
                                <strong>Recommended: 1920x800px for best quality.</strong>
                            </div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div class="mb-3">
                            <img id="imagePreview" 
                                 class="img-fluid rounded shadow-sm" 
                                 style="max-height: 200px; display: none;" 
                                 alt="Image Preview">
                        </div>

                        <!-- Link -->
                        <div class="mb-3">
                            <label for="link" class="form-label">
                                Link URL <span class="text-muted">(Optional)</span>
                            </label>
                            <input type="url" 
                                   class="form-control @error('link') is-invalid @enderror" 
                                   id="link" 
                                   name="link" 
                                   value="{{ old('link') }}"
                                   placeholder="https://example.com">
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> 
                                URL where users will be redirected when clicking the slider
                            </div>
                            @error('link')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" 
                                   class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="{{ old('sort_order', 0) }}"
                                   min="0">
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> 
                                Lower numbers appear first (0 = highest priority)
                            </div>
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active Status</strong>
                                    <small class="text-muted d-block">Enable this slider to show on website</small>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-lg me-2"></i>Create Slider
                                </button>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-x-lg me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });
});
</script>
@endsection