@extends('admin.layout')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')
@section('page-description', 'Update product details, variations, and images')

@section('content')
<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf
    @method('PUT')
    
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
                                   value="{{ old('name', $product->name) }}" required 
                                   placeholder="e.g., Men's Cotton T-Shirt">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Base Price (₹) *</label>
                            <input type="number" class="form-control" id="price" name="price" 
                                   value="{{ old('price', $product->price) }}" required step="0.01" min="0"
                                   placeholder="999.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mrp" class="form-label">MRP (₹)</label>
                            <input type="number" class="form-control" id="mrp" name="mrp" 
                                   value="{{ old('mrp', $product->mrp) }}" step="0.01" min="0"
                                   placeholder="1299.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                   value="{{ old('stock_quantity', $product->variations->first()->stock->quantity ?? 10) }}" min="0"
                                   placeholder="10">
                            <div class="form-text">For simple products without variations</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sku" class="form-label">SKU</label>
                            <input type="text" class="form-control" id="sku" name="sku" 
                                   value="{{ old('sku', $product->variations->first()->sku ?? $product->sku) }}" 
                                   placeholder="AUTO-GENERATED">
                            <div class="form-text">Leave empty for auto-generation</div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="4" required placeholder="Detailed product description...">{{ old('description', $product->description) }}</textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="video" class="form-label">Product Video</label>
                            @if($product->video)
                                <div class="current-video mb-2">
                                    <p class="text-muted mb-2">Current video:</p>
                                    <video width="200" height="120" controls>
                                        <source src="{{ asset('storage/' . $product->video) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            @endif
                            <input type="file" class="form-control" id="video" name="video" 
                                   accept="video/*" onchange="previewVideo(this)">
                            <div class="form-text">Upload a new product demonstration video (MP4, WebM, etc.) - Leave empty to keep current video</div>
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
                    <div id="main-images-preview" class="d-flex flex-wrap gap-2">
                        @foreach($product->images as $image)
                            <div class="position-relative me-2 mb-2">
                                <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->alt }}" width="80" class="rounded border">
                                <!-- Optionally add a delete button here -->
                            </div>
                        @endforeach
                    </div>
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
                               value="1" {{ old('active', $product->active) ? 'checked' : '' }}>
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
                                <h6 class="mb-1" id="variations-count">{{ $product->variations->count() }}</h6>
                                <small class="text-muted">Variations</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h6 class="mb-1" id="total-stock">{{ $product->variations->sum(fn($v) => $v->stock->quantity ?? 0) }}</h6>
                                <small class="text-muted">Total Stock</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Product Variations (reuse your JS logic for variations, prefill with $product->variations) -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-grid me-2"></i>
                Product Variations
            </h5>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="hasVariations" onchange="toggleVariations()" {{ $product->variations->count() > 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="hasVariations">
                    This product has variations
                </label>
            </div>
        </div>
        <div class="card-body" id="variationsSection" style="display: {{ $product->variations->count() > 1 ? 'block' : 'none' }};">
            <!-- Step 1: Select Variation Attributes -->
            <div class="step-section" id="step1">
                <div class="d-flex align-items-center mb-3">
                    <!-- ...reuse your attribute selection logic here... -->
                </div>
            </div>
            <!-- Configure Variations Section -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2">2</span>
                    <h6 class="mb-0">Configure Variations</h6>
                </div>
                <div class="text-muted">
                    <span id="variationsCount">{{ $product->variations->count() }}</span> variations
                </div>
            </div>
            
            <!-- Bulk Actions -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label small">Bulk Price Update</label>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control" id="bulkPrice" placeholder="Price" step="0.01">
                        <button class="btn btn-outline-secondary" type="button" onclick="applyBulkPrice()">
                            Apply to Selected
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Bulk Stock Update</label>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control" id="bulkStock" placeholder="Stock" min="0">
                        <button class="btn btn-outline-secondary" type="button" onclick="applyBulkStock()">
                            Apply to Selected
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
                        <button class="btn btn-outline-primary" type="button" onclick="toggleAllVariations()" title="Select/Deselect All">
                            <i class="bi bi-check-square"></i>
                        </button>
                        <button class="btn btn-outline-warning" type="button" onclick="duplicateSelected()" title="Duplicate Selected">
                            <i class="bi bi-files"></i>
                        </button>
                        <button class="btn btn-outline-danger" type="button" onclick="removeAllVariations()" title="Remove Selected">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-bordered align-middle mb-0">
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
                        @foreach($product->variations as $vIndex => $variation)
        <tr>
            <td>
                <input type="checkbox" class="variation-checkbox" onchange="updateBulkActions()">
                <input type="hidden" name="variations[{{ $vIndex }}][id]" value="{{ $variation->id }}">
            </td>
            <td>
                <strong>
                    @foreach($variation->attributeValues as $attributeValue)
                        <span class="badge bg-light text-dark me-1">
                            {{ $attributeValue->attribute->name ?? 'N/A' }}: {{ $attributeValue->value }}
                        </span>
                        <input type="hidden" name="variations[{{ $vIndex }}][attributes][]" value="{{ $attributeValue->id }}">
                    @endforeach
                </strong>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm sku-input" 
                       name="variations[{{ $vIndex }}][sku]" 
                       value="{{ $variation->sku }}" 
                       placeholder="Auto-generated"
                       onchange="validateSKU(this)">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm price-input" 
                       name="variations[{{ $vIndex }}][price]" 
                       value="{{ $variation->price }}" 
                       step="0.01" min="0" 
                       onchange="updateStats()">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm stock-input" 
                       name="variations[{{ $vIndex }}][stock]" 
                       value="{{ $variation->stock->quantity ?? 0 }}" min="0" required
                       onchange="updateStats()">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" 
                       name="variations[{{ $vIndex }}][min_qty]" 
                       value="{{ $variation->min_qty }}" min="1">
            </td>
            <td>
                <input type="file" class="form-control form-control-sm" 
                       name="variation_images[{{ $vIndex }}][]" 
                       multiple accept="image/*"
                       onchange="previewVariationImages(this, {{ $vIndex }})">
                <div id="preview_{{ $vIndex }}" class="mt-1">
                    @foreach($variation->images as $img)
                        <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $img->alt }}" width="30" class="rounded border me-1 mb-1">
                    @endforeach
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm" 
                        onclick="removeVariation(this)" title="Remove">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-end">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-2"></i> Update Product
        </button>
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
            $('#mrp').val((price * 1.3).toFixed(2));
        }
    });
    
    // Initialize bulk actions on existing variations
    updateBulkActions();
    
    // Add event listeners to existing variation checkboxes
    document.querySelectorAll('.variation-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
    
    // Initialize stats
    updateStats();
});
// Toggle variations section
function toggleVariations() {
    const checkbox = document.getElementById('hasVariations');
    const section = document.getElementById('variationsSection');
    const stockField = document.getElementById('stock_quantity').closest('.col-md-6');
    const skuField = document.getElementById('sku').closest('.col-md-6');
    if (checkbox.checked) {
        section.style.display = 'block';
        stockField.style.display = 'none';
        skuField.style.display = 'none';
    } else {
        section.style.display = 'none';
        stockField.style.display = 'block';
        skuField.style.display = 'block';
        clearAllVariations();
    }
}
function toggleAttributeValues(attributeId) {
    const checkbox = document.getElementById(`attr_${attributeId}`);
    const valuesDiv = document.getElementById(`values_${attributeId}`);
    if (checkbox.checked) {
        valuesDiv.style.display = 'block';
    } else {
        valuesDiv.style.display = 'none';
        document.querySelectorAll(`input[data-attribute="${attributeId}"]`).forEach(input => {
            if (input.type === 'checkbox' && input.classList.contains('attribute-value')) {
                input.checked = false;
            }
        });
    }
    checkGenerateButton();
}
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
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('attribute-value')) {
        checkGenerateButton();
    }
});
function generateVariations() {
    const selectedAttrs = checkGenerateButton();
    if (Object.keys(selectedAttrs).length === 0) {
        alert('Please select at least one attribute value');
        return;
    }
    const combinations = generateCombinations(selectedAttrs);
    renderVariationsTable(combinations);
    document.getElementById('step2').style.display = 'block';
    document.getElementById('variationsCount').textContent = combinations.length;
    document.getElementById('step2').scrollIntoView({ behavior: 'smooth' });
}
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
function renderVariationsTable(combinations) {
    const tbody = document.getElementById('variationsTableBody');
    tbody.innerHTML = '';
    combinations.forEach((combination, index) => {
        const row = createVariationRow(combination, index);
        tbody.appendChild(row);
    });
    updateStats();
}
function createVariationRow(combination, index) {
    const row = document.createElement('tr');
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
function applyBulkPrice() {
    const price = document.getElementById('bulkPrice').value;
    if (!price) {
        alert('Please enter a price value');
        return;
    }
    
    const selectedCheckboxes = document.querySelectorAll('.variation-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one variation');
        return;
    }
    
    if (confirm(`Apply price ₹${price} to ${selectedCheckboxes.length} selected variation(s)?`)) {
        selectedCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const priceInput = row.querySelector('.price-input');
            if (priceInput) priceInput.value = price;
        });
        updateStats();
        
        // Clear the input
        document.getElementById('bulkPrice').value = '';
        
        // Show success message
        showNotification(`Price updated for ${selectedCheckboxes.length} variation(s)`, 'success');
    }
}
function applyBulkStock() {
    const stock = document.getElementById('bulkStock').value;
    if (stock === '') {
        alert('Please enter a stock value');
        return;
    }
    
    const selectedCheckboxes = document.querySelectorAll('.variation-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one variation');
        return;
    }
    
    if (confirm(`Apply stock quantity ${stock} to ${selectedCheckboxes.length} selected variation(s)?`)) {
        selectedCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const stockInput = row.querySelector('.stock-input');
            if (stockInput) stockInput.value = stock;
        });
        updateStats();
        
        // Clear the input
        document.getElementById('bulkStock').value = '';
        
        // Show success message
        showNotification(`Stock updated for ${selectedCheckboxes.length} variation(s)`, 'success');
    }
}
function generateBulkSKU() {
    const prefix = document.getElementById('bulkSKU').value || 'PROD';
    const selectedCheckboxes = document.querySelectorAll('.variation-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one variation');
        return;
    }
    
    if (confirm(`Generate SKUs with prefix "${prefix}" for ${selectedCheckboxes.length} selected variation(s)?`)) {
        selectedCheckboxes.forEach((checkbox, index) => {
            const row = checkbox.closest('tr');
            const skuInput = row.querySelector('.sku-input');
            if (skuInput) {
                const timestamp = Date.now().toString().slice(-4);
                const randomStr = Math.random().toString(36).substr(2, 4).toUpperCase();
                skuInput.value = `${prefix}-${String(index + 1).padStart(3, '0')}-${timestamp}-${randomStr}`;
            }
        });
        
        // Clear the input
        document.getElementById('bulkSKU').value = '';
        
        // Show success message
        showNotification(`SKUs generated for ${selectedCheckboxes.length} variation(s)`, 'success');
    }
}
function toggleAllVariations() {
    const checkboxes = document.querySelectorAll('.variation-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => cb.checked = !allChecked);
    selectAllCheckbox.checked = !allChecked;
    
    updateBulkActions();
    
    const selectedCount = document.querySelectorAll('.variation-checkbox:checked').length;
    const action = !allChecked ? 'Selected' : 'Deselected';
    showNotification(`${action} ${selectedCount} variation(s)`, 'info');
}
function removeAllVariations() {
    const selectedCheckboxes = document.querySelectorAll('.variation-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one variation to remove');
        return;
    }
    
    const totalVariations = document.querySelectorAll('.variation-checkbox').length;
    if (selectedCheckboxes.length === totalVariations) {
        alert('Cannot remove all variations. At least one variation must remain.');
        return;
    }
    
    if (confirm(`Are you sure you want to remove ${selectedCheckboxes.length} selected variation(s)? This action cannot be undone.`)) {
        selectedCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            row.remove();
        });
        
        updateStats();
        updateBulkActions();
        
        // Update select all checkbox
        const remainingCheckboxes = document.querySelectorAll('.variation-checkbox');
        const selectAllCheckbox = document.getElementById('selectAll');
        selectAllCheckbox.checked = false;
        
        showNotification(`Removed ${selectedCheckboxes.length} variation(s)`, 'warning');
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
    const total = document.querySelectorAll('.variation-checkbox').length;
    
    // Update select all checkbox state
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selected === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (selected === total) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
    }
    
    // Update bulk action buttons state
    const bulkButtons = document.querySelectorAll('#bulkPrice + button, #bulkStock + button, #bulkSKU + button');
    bulkButtons.forEach(button => {
        button.disabled = selected === 0;
        if (selected > 0) {
            button.textContent = button.textContent.replace(/to \w+/, `to Selected (${selected})`);
        } else {
            button.textContent = button.textContent.replace(/to Selected.*/, 'to Selected');
        }
    });
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
    if (document.getElementById('variations-count')) {
        document.getElementById('variations-count').textContent = variations;
    }
    if (document.getElementById('total-stock')) {
        document.getElementById('total-stock').textContent = totalStock;
    }
    if (document.getElementById('variationsCount')) {
        document.getElementById('variationsCount').textContent = variations;
    }
}
function validateSKU(input) {
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
function previewProduct() {
    alert('Product preview functionality would open in a new tab');
}
function validateForm() {
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
    const hasVariationsToggle = document.getElementById('hasVariations');
    if (hasVariationsToggle && hasVariationsToggle.checked) {
        const variationsCount = document.querySelectorAll('#variationsTableBody tr').length;
        if (variationsCount === 0) {
            alert('You have enabled variations but no variations are created. Please generate variations or disable the variations toggle.');
            return false;
        }
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
    return true;
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

// Duplicate selected variations
function duplicateSelected() {
    const selectedCheckboxes = document.querySelectorAll('.variation-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one variation to duplicate');
        return;
    }
    
    if (confirm(`Duplicate ${selectedCheckboxes.length} selected variation(s)?`)) {
        const tbody = document.getElementById('variationsTableBody');
        let duplicatedCount = 0;
        
        selectedCheckboxes.forEach(checkbox => {
            const originalRow = checkbox.closest('tr');
            const newRow = originalRow.cloneNode(true);
            
            // Update the variation index for the new row
            const currentRows = tbody.querySelectorAll('tr').length;
            const newIndex = currentRows;
            
            // Update all input names and IDs
            const inputs = newRow.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name && input.name.includes('[')) {
                    input.name = input.name.replace(/\[\d+\]/, `[${newIndex}]`);
                }
                if (input.id && input.id.includes('_')) {
                    input.id = input.id.replace(/_\d+/, `_${newIndex}`);
                }
                
                // Clear the checkbox state and add "COPY" to SKU
                if (input.type === 'checkbox' && input.classList.contains('variation-checkbox')) {
                    input.checked = false;
                } else if (input.classList.contains('sku-input') && input.value) {
                    input.value = input.value + '-COPY';
                }
            });
            
            // Update preview div ID
            const previewDiv = newRow.querySelector('[id^="preview_"]');
            if (previewDiv) {
                previewDiv.id = `preview_${newIndex}`;
            }
            
            // Update onclick attributes
            const fileInput = newRow.querySelector('input[type="file"]');
            if (fileInput) {
                fileInput.setAttribute('onchange', `previewVariationImages(this, ${newIndex})`);
            }
            
            tbody.appendChild(newRow);
            duplicatedCount++;
        });
        
        updateStats();
        updateBulkActions();
        
        showNotification(`Duplicated ${duplicatedCount} variation(s)`, 'success');
    }
}

// Show notification helper
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>
@endpush
