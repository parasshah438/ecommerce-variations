@extends('admin.layout')

@section('title', 'Add New Product')
@section('page-title', 'Add New Product')
@section('page-description', 'Create a new product with variations and images')

@section('content')
<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf
    
    <!-- Upload Alerts and Messages -->
    @include('admin.products.partials.upload-alerts')
    
    <div class="row">
        <!-- Basic Product Information -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="{{ old('name') }}" required 
                                   placeholder="e.g., Men's Cotton T-Shirt">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="brand_id" class="form-label">Brand *</label>
                            <select class="form-select" id="brand_id" name="brand_id" required>
                                <option value="">Select Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Base Price (₹) *</label>
                            <input type="number" class="form-control" id="price" name="price" 
                                   value="{{ old('price') }}" required step="0.01" min="0"
                                   placeholder="999.00">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="mrp" class="form-label">MRP (₹)</label>
                            <input type="number" class="form-control" id="mrp" name="mrp" 
                                   value="{{ old('mrp') }}" step="0.01" min="0"
                                   placeholder="1299.00">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                   value="{{ old('stock_quantity', 10) }}" min="0"
                                   placeholder="10">
                            <div class="form-text">For simple products without variations</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="sku" class="form-label">SKU</label>
                            <input type="text" class="form-control" id="sku" name="sku" 
                                   value="{{ old('sku') }}" 
                                   placeholder="AUTO-GENERATED">
                            <div class="form-text">Leave empty for auto-generation</div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="4" required placeholder="Detailed product description...">{{ old('description') }}</textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="video" class="form-label">Product Video</label>
                            <input type="file" class="form-control" id="video" name="video" 
                                   accept="video/*" onchange="previewVideo(this)">
                            <div class="form-text">Upload a product demonstration video (MP4, WebM, etc.)</div>
                            <div id="video-preview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Images -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-images me-2"></i>
                        Product Images
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="images" class="form-label">Upload Images</label>
                        <input type="file" class="form-control" id="images" name="images[]" 
                               multiple accept="image/*" onchange="previewImages(this, 'main-images-preview')">
                        <div class="form-text">You can upload multiple images. First image will be the main image.</div>
                    </div>
                    <div id="main-images-preview" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>
        
        <!-- Product Settings -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="active" name="active" 
                               value="1" {{ old('active', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="active">
                            Active Product
                        </label>
                    </div>
                    <div class="form-text">Inactive products won't be visible on frontend</div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2"></i>
                        Preview Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h6 class="mb-1" id="variations-count">0</h6>
                                <small class="text-muted">Variations</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h6 class="mb-1" id="total-stock">0</h6>
                                <small class="text-muted">Total Stock</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Variations -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-grid me-2"></i>
                Product Variations
            </h5>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="hasVariations" onchange="toggleVariations()">
                <label class="form-check-label" for="hasVariations">
                    This product has variations
                </label>
            </div>
        </div>
        <div class="card-body" id="variationsSection" style="display: none;">
            
            <!-- Step 1: Select Variation Attributes -->
            <div class="step-section" id="step1">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-primary me-2">1</span>
                    <h6 class="mb-0">Select Variation Attributes</h6>
                </div>
                
                <div class="row">
                    @foreach($attributes as $attribute)
                    <div class="col-md-6 mb-3">
                        <div class="card border">
                            <div class="card-body p-3">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input attribute-toggle" type="checkbox" 
                                           id="attr_{{ $attribute->id }}" 
                                           data-attribute-id="{{ $attribute->id }}"
                                           data-attribute-name="{{ $attribute->name }}"
                                           onchange="toggleAttributeValues({{ $attribute->id }})">
                                    <label class="form-check-label fw-bold" for="attr_{{ $attribute->id }}">
                                        {{ $attribute->name }}
                                    </label>
                                </div>
                                
                                <div class="attribute-values" id="values_{{ $attribute->id }}" style="display: none;">
                                    <label class="form-label small text-muted">Select {{ strtolower($attribute->name) }} options:</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($attribute->values as $value)
                                        <div class="form-check">
                                            <input class="form-check-input attribute-value" type="checkbox" 
                                                   data-attribute="{{ $attribute->id }}" 
                                                   value="{{ $value->id }}" 
                                                   id="val_{{ $value->id }}">
                                            <label class="form-check-label badge bg-light text-dark" for="val_{{ $value->id }}">
                                                {{ $value->value }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-primary" id="generateBtn" onclick="generateVariations()" disabled>
                        <i class="bi bi-magic me-1"></i>
                        Generate Variations
                    </button>
                </div>
            </div>
            
            <!-- Step 2: Generated Variations -->
            <div class="step-section mt-4" id="step2" style="display: none;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success me-2">2</span>
                        <h6 class="mb-0">Configure Variations</h6>
                    </div>
                    <div class="text-muted">
                        <span id="variationsCount">0</span> variations generated
                    </div>
                </div>
                
                <!-- Bulk Actions -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label small">Bulk Price Update</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control" id="bulkPrice" placeholder="Price" step="0.01">
                            <button class="btn btn-outline-secondary" type="button" onclick="applyBulkPrice()">
                                Apply to All
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Bulk Stock Update</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control" id="bulkStock" placeholder="Stock" min="0">
                            <button class="btn btn-outline-secondary" type="button" onclick="applyBulkStock()">
                                Apply to All
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Bulk SKU Prefix</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="bulkSKU" placeholder="PREFIX">
                            <button class="btn btn-outline-secondary" type="button" onclick="generateBulkSKU()">
                                Generate SKUs
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Quick Actions</label>
                        <div class="btn-group btn-group-sm w-100">
                            <button class="btn btn-outline-warning" type="button" onclick="toggleAllVariations()">
                                <i class="bi bi-toggle-on"></i>
                            </button>
                            <button class="btn btn-outline-danger" type="button" onclick="removeAllVariations()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Variations Table -->
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="variationsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="selectAll" onchange="toggleAllRows()">
                                </th>
                                <th width="25%">Variation</th>
                                <th width="15%">SKU</th>
                                <th width="15%">Price (₹)</th>
                                <th width="10%">Stock</th>
                                <th width="10%">Min Qty</th>
                                <th width="15%">Images</th>
                                <th width="5%">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="variationsTableBody">
                            <!-- Generated variations will appear here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="d-flex justify-content-between">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Products
        </a>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" onclick="previewProduct()">
                <i class="bi bi-eye me-1"></i>
                Preview
            </button>
            <button type="submit" class="btn btn-primary" onclick="return validateForm()">
                <i class="bi bi-check-lg me-1"></i>
                Create Product
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let variationIndex = 0;
    let selectedAttributes = {};
    
    // Auto-fill MRP when price changes
    $('#price').on('input', function() {
        const price = parseFloat($(this).val());
        if (price && !$('#mrp').val()) {
            $('#mrp').val((price * 1.3).toFixed(2)); // 30% markup
        }
    });
});

// Toggle variations section
function toggleVariations() {
    const checkbox = document.getElementById('hasVariations');
    const section = document.getElementById('variationsSection');
    const stockField = document.getElementById('stock_quantity').closest('.col-md-6');
    const skuField = document.getElementById('sku').closest('.col-md-6');
    
    if (checkbox.checked) {
        section.style.display = 'block';
        // Hide simple product stock/sku fields when variations are enabled
        stockField.style.display = 'none';
        skuField.style.display = 'none';
    } else {
        section.style.display = 'none';
        // Show simple product stock/sku fields when variations are disabled
        stockField.style.display = 'block';
        skuField.style.display = 'block';
        // Clear all variations
        clearAllVariations();
    }
}

// Toggle attribute values visibility
function toggleAttributeValues(attributeId) {
    const checkbox = document.getElementById(`attr_${attributeId}`);
    const valuesDiv = document.getElementById(`values_${attributeId}`);
    
    if (checkbox.checked) {
        valuesDiv.style.display = 'block';
    } else {
        valuesDiv.style.display = 'none';
        // Uncheck all values for this attribute
        document.querySelectorAll(`input[data-attribute="${attributeId}"]`).forEach(input => {
            if (input.type === 'checkbox' && input.classList.contains('attribute-value')) {
                input.checked = false;
            }
        });
    }
    
    checkGenerateButton();
}

// Check if generate button should be enabled
function checkGenerateButton() {
    const selectedAttrs = {};
    let hasSelections = false;
    
    document.querySelectorAll('.attribute-value:checked').forEach(input => {
        const attrId = input.getAttribute('data-attribute');
        if (!selectedAttrs[attrId]) selectedAttrs[attrId] = [];
        selectedAttrs[attrId].push(input.value);
        hasSelections = true;
    });
    
    document.getElementById('generateBtn').disabled = !hasSelections;
    return selectedAttrs;
}

// Listen for attribute value changes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('attribute-value')) {
        checkGenerateButton();
    }
});

// Generate variations
function generateVariations() {
    const selectedAttrs = checkGenerateButton();
    
    if (Object.keys(selectedAttrs).length === 0) {
        alert('Please select at least one attribute value');
        return;
    }
    
    // Generate combinations
    const combinations = generateCombinations(selectedAttrs);
    renderVariationsTable(combinations);
    
    // Show step 2
    document.getElementById('step2').style.display = 'block';
    document.getElementById('variationsCount').textContent = combinations.length;
    
    // Scroll to variations table
    document.getElementById('step2').scrollIntoView({ behavior: 'smooth' });
}

// Generate all possible combinations
function generateCombinations(attributes) {
    let combinations = [{}];
    
    for (const attrId in attributes) {
        const newCombinations = [];
        const valueIds = attributes[attrId];
        
        combinations.forEach(combination => {
            valueIds.forEach(valueId => {
                const newCombination = { ...combination };
                newCombination[attrId] = valueId;
                newCombinations.push(newCombination);
            });
        });
        
        combinations = newCombinations;
    }
    
    return combinations;
}

// Render variations in table
function renderVariationsTable(combinations) {
    const tbody = document.getElementById('variationsTableBody');
    tbody.innerHTML = '';
    
    combinations.forEach((combination, index) => {
        const row = createVariationRow(combination, index);
        tbody.appendChild(row);
    });
    
    updateStats();
}

// Create a variation row
function createVariationRow(combination, index) {
    const row = document.createElement('tr');
    
    // Get attribute names for display
    const attributeNames = [];
    for (const attrId in combination) {
        const valueId = combination[attrId];
        const valueElement = document.getElementById(`val_${valueId}`);
        const attrElement = document.getElementById(`attr_${attrId}`);
        
        if (valueElement && attrElement) {
            const attrName = attrElement.getAttribute('data-attribute-name');
            const valueName = valueElement.nextElementSibling.textContent.trim();
            attributeNames.push(`${attrName}: ${valueName}`);
        }
    }
    
    const displayName = attributeNames.join(' | ');
    const basePrice = parseFloat(document.getElementById('price').value) || 0;
    
    row.innerHTML = `
        <td>
            <input type="checkbox" class="variation-checkbox" onchange="updateBulkActions()">
        </td>
        <td>
            <strong>${displayName}</strong>
            ${Object.values(combination).map(valueId => 
                `<input type="hidden" name="variations[${index}][attributes][]" value="${valueId}">`
            ).join('')}
        </td>
        <td>
            <input type="text" class="form-control form-control-sm sku-input" 
                   name="variations[${index}][sku]" 
                   placeholder="Auto-generated"
                   onchange="validateSKU(this)">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm price-input" 
                   name="variations[${index}][price]" 
                   value="${basePrice}" 
                   step="0.01" min="0" 
                   onchange="updateStats()">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm stock-input" 
                   name="variations[${index}][stock]" 
                   value="0" min="0" required
                   onchange="updateStats()">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" 
                   name="variations[${index}][min_qty]" 
                   value="1" min="1">
        </td>
        <td>
            <input type="file" class="form-control form-control-sm" 
                   name="variation_images[${index}][]" 
                   multiple accept="image/*"
                   onchange="previewVariationImages(this, ${index})">
            <div id="preview_${index}" class="mt-1"></div>
        </td>
        <td>
            <button type="button" class="btn btn-outline-danger btn-sm" 
                    onclick="removeVariation(this)" title="Remove">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    return row;
}

// Bulk operations
function applyBulkPrice() {
    const price = document.getElementById('bulkPrice').value;
    if (!price) return;
    
    document.querySelectorAll('.variation-checkbox:checked').forEach(checkbox => {
        const row = checkbox.closest('tr');
        const priceInput = row.querySelector('.price-input');
        if (priceInput) priceInput.value = price;
    });
    updateStats();
}

function applyBulkStock() {
    const stock = document.getElementById('bulkStock').value;
    if (stock === '') return;
    
    document.querySelectorAll('.variation-checkbox:checked').forEach(checkbox => {
        const row = checkbox.closest('tr');
        const stockInput = row.querySelector('.stock-input');
        if (stockInput) stockInput.value = stock;
    });
    updateStats();
}

function generateBulkSKU() {
    const prefix = document.getElementById('bulkSKU').value || 'PROD';
    
    document.querySelectorAll('.variation-checkbox:checked').forEach((checkbox, index) => {
        const row = checkbox.closest('tr');
        const skuInput = row.querySelector('.sku-input');
        if (skuInput) {
            skuInput.value = `${prefix}-${String(index + 1).padStart(3, '0')}-${Math.random().toString(36).substr(2, 4).toUpperCase()}`;
        }
    });
}

function toggleAllVariations() {
    const checkboxes = document.querySelectorAll('.variation-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => cb.checked = !allChecked);
    updateBulkActions();
}

function removeAllVariations() {
    if (confirm('Are you sure you want to remove all selected variations?')) {
        document.querySelectorAll('.variation-checkbox:checked').forEach(checkbox => {
            removeVariation(checkbox.closest('tr').querySelector('.btn-outline-danger'));
        });
    }
}

function toggleAllRows() {
    const selectAll = document.getElementById('selectAll');
    document.querySelectorAll('.variation-checkbox').forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const selected = document.querySelectorAll('.variation-checkbox:checked').length;
    // Update UI to show how many are selected
}

function removeVariation(button) {
    if (confirm('Remove this variation?')) {
        button.closest('tr').remove();
        updateStats();
    }
}

function clearAllVariations() {
    document.getElementById('variationsTableBody').innerHTML = '';
    document.getElementById('step2').style.display = 'none';
    updateStats();
}

function updateStats() {
    const variations = document.querySelectorAll('#variationsTableBody tr').length;
    let totalStock = 0;
    
    document.querySelectorAll('.stock-input').forEach(input => {
        totalStock += parseInt(input.value) || 0;
    });
    
    document.getElementById('variations-count').textContent = variations;
    document.getElementById('total-stock').textContent = totalStock;
    document.getElementById('variationsCount').textContent = variations;
}

function validateSKU(input) {
    // Check for duplicate SKUs
    const sku = input.value;
    if (!sku) return;
    
    const allSkus = Array.from(document.querySelectorAll('.sku-input'))
        .map(inp => inp.value)
        .filter(val => val);
    
    const duplicates = allSkus.filter(s => s === sku).length;
    if (duplicates > 1) {
        input.classList.add('is-invalid');
        alert('Duplicate SKU detected: ' + sku);
    } else {
        input.classList.remove('is-invalid');
    }
}

function previewVariationImages(input, index) {
    const preview = document.getElementById(`preview_${index}`);
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).slice(0, 3).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '30px';
                img.style.height = '30px';
                img.style.objectFit = 'cover';
                img.style.marginRight = '2px';
                img.style.borderRadius = '4px';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }
}

// Image preview function for main images
function previewImages(input, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'image-preview';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" style="width: 80px; height: 80px; object-fit: cover;">
                    <button type="button" class="remove-image" onclick="removeImagePreview(this, '${containerId}', ${index})">
                        <i class="bi bi-x"></i>
                    </button>
                `;
                container.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

function removeImagePreview(button, containerId, index) {
    button.parentElement.remove();
}

// Video preview function
function previewVideo(input) {
    const container = document.getElementById('video-preview');
    container.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'video-preview mt-2';
            div.innerHTML = `
                <div class="d-flex align-items-center gap-3 p-3 border rounded">
                    <video width="120" height="80" controls style="border-radius: 4px;">
                        <source src="${e.target.result}" type="${file.type}">
                        Your browser does not support the video tag.
                    </video>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${file.name}</h6>
                        <small class="text-muted">${formatFileSize(file.size)} • ${file.type}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVideoPreview()">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(div);
        };
        
        reader.readAsDataURL(file);
    }
}

function removeVideoPreview() {
    document.getElementById('video-preview').innerHTML = '';
    document.getElementById('video').value = '';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function previewProduct() {
    alert('Product preview functionality would open in a new tab');
}

function validateForm() {
    // Check basic required fields
    const name = document.getElementById('name').value.trim();
    const description = document.getElementById('description').value.trim();
    const categoryId = document.getElementById('category_id').value;
    const brandId = document.getElementById('brand_id').value;
    const price = document.getElementById('price').value;
    
    if (!name) {
        alert('Product name is required');
        document.getElementById('name').focus();
        return false;
    }
    
    if (!description) {
        alert('Product description is required');
        document.getElementById('description').focus();
        return false;
    }
    
    if (!categoryId) {
        alert('Please select a category');
        document.getElementById('category_id').focus();
        return false;
    }
    
    if (!brandId) {
        alert('Please select a brand');
        document.getElementById('brand_id').focus();
        return false;
    }
    
    if (!price || price <= 0) {
        alert('Please enter a valid price');
        document.getElementById('price').focus();
        return false;
    }
    
    // Check if variations are enabled but none are created
    const hasVariationsToggle = document.getElementById('hasVariations');
    if (hasVariationsToggle && hasVariationsToggle.checked) {
        const variationsCount = document.querySelectorAll('#variationsTableBody tr').length;
        if (variationsCount === 0) {
            alert('You have enabled variations but no variations are created. Please generate variations or disable the variations toggle.');
            return false;
        }
        
        // Validate each variation has required stock
        const stockInputs = document.querySelectorAll('.stock-input');
        let hasInvalidStock = false;
        stockInputs.forEach(input => {
            if (input.value === '' || input.value < 0) {
                hasInvalidStock = true;
            }
        });
        
        if (hasInvalidStock) {
            alert('Please enter valid stock quantities for all variations');
            return false;
        }
    }
    
    return true; // Form is valid
}
</script>
@endpush
