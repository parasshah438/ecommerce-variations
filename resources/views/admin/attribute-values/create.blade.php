@extends('admin.layout')

@section('title', 'Create New Attribute Value')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-gray-800">Create New Attribute Value</h1>
                <a href="{{ route('admin.attribute-values.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Values
                </a>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Attribute Value Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.attribute-values.store') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="attribute_id" class="form-label">Attribute <span class="text-danger">*</span></label>
                            <select class="form-control @error('attribute_id') is-invalid @enderror" 
                                    id="attribute_id" 
                                    name="attribute_id" 
                                    required
                                    onchange="toggleColorField()">
                                <option value="">Select an attribute...</option>
                                @foreach($attributes as $attribute)
                                    <option value="{{ $attribute->id }}" 
                                            data-type="{{ $attribute->type }}"
                                            {{ old('attribute_id', request('attribute_id')) == $attribute->id ? 'selected' : '' }}>
                                        {{ $attribute->name }} ({{ ucfirst($attribute->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('attribute_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">
                                Choose the attribute this value belongs to.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="value" class="form-label">Value <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('value') is-invalid @enderror" 
                                   id="value" 
                                   name="value" 
                                   value="{{ old('value') }}" 
                                   placeholder="e.g., Red, Large, Cotton"
                                   required>
                            @error('value')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">
                                Enter the value name (must be unique for this attribute).
                            </small>
                        </div>

                        <div class="form-group" id="colorGroup" style="display: none;">
                            <label for="hex_color" class="form-label">Hex Color Code</label>
                            <div class="input-group">
                                <input type="color" 
                                       class="form-control form-control-color @error('hex_color') is-invalid @enderror" 
                                       id="colorPicker" 
                                       value="{{ old('hex_color', '#000000') }}"
                                       style="width: 60px; height: 38px;">
                                <input type="text" 
                                       class="form-control @error('hex_color') is-invalid @enderror" 
                                       id="hex_color" 
                                       name="hex_color" 
                                       value="{{ old('hex_color') }}" 
                                       placeholder="#FF0000"
                                       pattern="^#[0-9A-Fa-f]{6}$">
                            </div>
                            @error('hex_color')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">
                                Optional: Specify a hex color code for color attributes (e.g., #FF0000 for red).
                            </small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_default" 
                                       name="is_default" 
                                       value="1"
                                       {{ old('is_default') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_default">
                                    Set as Default Value
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Check to make this the default value for the attribute (will unset other defaults).
                            </small>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Attribute Value
                            </button>
                            <a href="{{ route('admin.attribute-values.index') }}" class="btn btn-secondary ml-2">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">Value Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>Unique Values:</strong> Each value must be unique within its attribute</li>
                        <li><strong>Color Attributes:</strong> Can optionally include hex color codes for visual display</li>
                        <li><strong>Default Values:</strong> Only one value can be set as default per attribute</li>
                        <li><strong>Examples:</strong> Red, Blue (Color) | Small, Medium, Large (Size) | Cotton, Silk (Material)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleColorField() {
    const attributeSelect = document.getElementById('attribute_id');
    const colorGroup = document.getElementById('colorGroup');
    const selectedOption = attributeSelect.options[attributeSelect.selectedIndex];
    
    if (selectedOption && selectedOption.dataset.type === 'color') {
        colorGroup.style.display = 'block';
    } else {
        colorGroup.style.display = 'none';
        document.getElementById('hex_color').value = '';
    }
}

// Color picker sync
document.getElementById('colorPicker').addEventListener('change', function() {
    document.getElementById('hex_color').value = this.value.toUpperCase();
});

document.getElementById('hex_color').addEventListener('input', function() {
    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
        document.getElementById('colorPicker').value = this.value;
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleColorField();
});
</script>
@endpush