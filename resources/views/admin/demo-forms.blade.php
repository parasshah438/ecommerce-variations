@extends('admin.layout')

@section('page-title', 'Advanced Form Components')
@section('page-description', 'Showcase of advanced form components with modern UI')
@section('breadcrumb-section', 'Components')
@section('breadcrumb-page', 'Advanced Forms')

@section('page-actions')
<button class="btn btn-primary">
    <i class="fas fa-save me-2"></i>Save Form
</button>
@endsection

@section('content')
<div class="container-fluid">
    <form class="advanced-form-demo">
        <div class="row">
            <!-- Rich Text Editor -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-edit me-2"></i>Rich Text Editor
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('admin.components.advanced-forms', [
                            'component' => 'rich-editor',
                            'label' => 'Article Content',
                            'name' => 'content',
                            'value' => '<h2>Welcome to the Rich Text Editor</h2><p>This is a <strong>powerful</strong> WYSIWYG editor with toolbar controls.</p>'
                        ])
                    </div>
                </div>
            </div>

            <!-- File Upload -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-upload me-2"></i>File Upload with Drag & Drop
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('admin.components.advanced-forms', [
                            'component' => 'file-upload',
                            'label' => 'Upload Documents',
                            'name' => 'documents[]',
                            'accept' => '.pdf,.doc,.docx,.jpg,.png'
                        ])
                    </div>
                </div>
            </div>

            <!-- Image Cropper -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-crop me-2"></i>Image Cropper
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('admin.components.advanced-forms', [
                            'component' => 'image-crop',
                            'label' => 'Profile Picture',
                            'name' => 'profile_image'
                        ])
                    </div>
                </div>
            </div>

            <!-- Color Picker -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-palette me-2"></i>Advanced Color Picker
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('admin.components.advanced-forms', [
                            'component' => 'color-picker',
                            'label' => 'Brand Color',
                            'name' => 'brand_color',
                            'value' => '#667eea'
                        ])

                        <div class="mt-4">
                            @include('admin.components.advanced-forms', [
                                'component' => 'color-picker',
                                'label' => 'Secondary Color',
                                'name' => 'secondary_color',
                                'value' => '#f093fb'
                            ])
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date/Time Picker -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-calendar-alt me-2"></i>Date/Time Picker
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('admin.components.advanced-forms', [
                            'component' => 'datetime-picker',
                            'label' => 'Event Date & Time',
                            'name' => 'event_datetime'
                        ])

                        <div class="mt-4">
                            @include('admin.components.advanced-forms', [
                                'component' => 'datetime-picker',
                                'label' => 'Deadline',
                                'name' => 'deadline',
                                'value' => '12/31/2024, 11:59:59 PM'
                            ])
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tag Input -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-tags me-2"></i>Dynamic Tag Input
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('admin.components.advanced-forms', [
                            'component' => 'tag-input',
                            'label' => 'Skills & Technologies',
                            'name' => 'skills[]',
                            'value' => ['Laravel', 'Vue.js', 'PHP']
                        ])

                        <div class="mt-4">
                            @include('admin.components.advanced-forms', [
                                'component' => 'tag-input',
                                'label' => 'Keywords',
                                'name' => 'keywords[]'
                            ])
                        </div>
                    </div>
                </div>
            </div>

            <!-- Autocomplete -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-search me-2"></i>Smart Autocomplete
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('admin.components.advanced-forms', [
                            'component' => 'autocomplete',
                            'label' => 'Assign to User',
                            'name' => 'assigned_user',
                            'placeholder' => 'Search for users...'
                        ])

                        <div class="mt-4">
                            @include('admin.components.advanced-forms', [
                                'component' => 'autocomplete',
                                'label' => 'Select Category',
                                'name' => 'category',
                                'placeholder' => 'Search categories...'
                            ])
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combined Example -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-magic me-2"></i>Combined Form Example
                        </h5>
                        <p class="card-text text-muted mb-0">All components working together in a real-world scenario</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Project Title</label>
                                    <input type="text" class="form-control" placeholder="Enter project title">
                                </div>
                                
                                @include('admin.components.advanced-forms', [
                                    'component' => 'autocomplete',
                                    'label' => 'Project Manager',
                                    'name' => 'project_manager',
                                    'placeholder' => 'Select project manager...'
                                ])
                            </div>
                            <div class="col-md-6">
                                @include('admin.components.advanced-forms', [
                                    'component' => 'datetime-picker',
                                    'label' => 'Start Date',
                                    'name' => 'start_date'
                                ])

                                @include('admin.components.advanced-forms', [
                                    'component' => 'color-picker',
                                    'label' => 'Project Color',
                                    'name' => 'project_color',
                                    'value' => '#4facfe'
                                ])
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                @include('admin.components.advanced-forms', [
                                    'component' => 'tag-input',
                                    'label' => 'Technologies',
                                    'name' => 'technologies[]'
                                ])
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                @include('admin.components.advanced-forms', [
                                    'component' => 'rich-editor',
                                    'label' => 'Project Description',
                                    'name' => 'description'
                                ])
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                @include('admin.components.advanced-forms', [
                                    'component' => 'image-crop',
                                    'label' => 'Project Logo',
                                    'name' => 'project_logo'
                                ])
                            </div>
                            <div class="col-md-6">
                                @include('admin.components.advanced-forms', [
                                    'component' => 'file-upload',
                                    'label' => 'Project Documents',
                                    'name' => 'project_files[]'
                                ])
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    All form data will be validated and processed securely.
                                </small>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary">
                                    <i class="fas fa-eye me-2"></i>Preview
                                </button>
                                <button type="button" class="btn btn-outline-primary">
                                    <i class="fas fa-save me-2"></i>Save Draft
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-2"></i>Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Custom Demo Styles -->
<style>
.advanced-form-demo .card {
    transition: all 0.3s ease;
}

.advanced-form-demo .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.card-title {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.card-title i {
    color: var(--primary-color);
}

.form-demo-section {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 1rem;
}

.component-preview {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid var(--bs-border-color);
}

@media (max-width: 768px) {
    .advanced-form-demo .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .advanced-form-demo .btn-group .btn {
        margin-bottom: 0.25rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add some demo interactions
    const submitBtn = document.querySelector('button[type="submit"]');
    
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            this.disabled = true;
            
            // Simulate form processing
            setTimeout(() => {
                // Show success toast
                if (window.showToast) {
                    showToast('Form submitted successfully!', 'success');
                }
                
                // Reset button
                this.innerHTML = '<i class="fas fa-check me-2"></i>Submit';
                this.disabled = false;
            }, 2000);
        });
    }
    
    // Add preview functionality
    const previewBtn = document.querySelector('.btn-outline-secondary');
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            const formData = new FormData(document.querySelector('.advanced-form-demo'));
            console.log('Form Preview:', Object.fromEntries(formData));
            
            if (window.showToast) {
                showToast('Preview data logged to console', 'info');
            }
        });
    }
});
</script>
@endpush