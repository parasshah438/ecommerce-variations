@extends('admin.layout')

@section('title', 'Create New Attribute')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-gray-800">Create New Attribute</h1>
                <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Attributes
                </a>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Attribute Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.attributes.store') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="name" class="form-label">Attribute Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="e.g., Color, Size, Material"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">
                                Enter a unique name for this attribute (e.g., Color, Size, Material).
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="type" class="form-label">Attribute Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror" 
                                    id="type" 
                                    name="type" 
                                    required>
                                <option value="">Select attribute type...</option>
                                <option value="text" {{ old('type') === 'text' ? 'selected' : '' }}>Text</option>
                                <option value="color" {{ old('type') === 'color' ? 'selected' : '' }}>Color</option>
                                <option value="size" {{ old('type') === 'size' ? 'selected' : '' }}>Size</option>
                                <option value="number" {{ old('type') === 'number' ? 'selected' : '' }}>Number</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">
                                Choose the type that best describes this attribute's values.
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="is_required" 
                                               name="is_required" 
                                               value="1"
                                               {{ old('is_required') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_required">
                                            Required Attribute
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Check if this attribute must be specified for all product variations.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="is_filterable" 
                                               name="is_filterable" 
                                               value="1"
                                               {{ old('is_filterable') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_filterable">
                                            Filterable in Frontend
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Check if customers can filter products by this attribute.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Attribute
                            </button>
                            <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary ml-2">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Type Information Card -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">Attribute Type Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Text</h6>
                            <p class="small text-muted">General text values like "Cotton", "Leather", "Organic"</p>
                            
                            <h6 class="text-warning">Size</h6>
                            <p class="small text-muted">Size variations like "S", "M", "L", "XL", "32", "34"</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info">Color</h6>
                            <p class="small text-muted">Color values with optional hex codes like "Red #FF0000"</p>
                            
                            <h6 class="text-secondary">Number</h6>
                            <p class="small text-muted">Numeric values like "500ml", "1kg", "12 inches"</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection