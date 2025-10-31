@extends('admin.layout')

@section('title', 'Edit Sale')

@section('styles')
<style>
    .product-search-container {
        position: relative;
    }
    
    .product-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 400px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .product-search-item {
        padding: 12px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: background-color 0.2s;
    }
    
    .product-search-item:hover {
        background-color: #f8f9fa;
    }
    
    .product-search-item:last-child {
        border-bottom: none;
    }
    
    .product-search-item.selected {
        background-color: #e3f2fd;
        border-left: 3px solid #2196f3;
    }
    
    .product-search-item img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        margin-right: 12px;
        border: 1px solid #dee2e6;
    }
    
    .product-search-info {
        flex-grow: 1;
    }
    
    .product-search-name {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 4px;
        color: #333;
    }
    
    .product-search-details {
        font-size: 12px;
        color: #666;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .product-price {
        font-weight: 600;
        color: #28a745;
    }
    
    .product-category {
        background: #f1f3f4;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
    }
    
    .product-variations {
        color: #007bff;
        font-size: 11px;
    }
    
    .product-selection-status {
        margin-left: 12px;
        font-size: 18px;
    }
    
    .product-add-btn {
        background: #28a745;
        color: white;
        border: none;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
    }
    
    .product-selected-btn {
        background: #17a2b8;
        color: white;
        border: none;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .selected-products-container {
        background: #f8f9fa;
        border: 1px solid #e3e6f0;
        border-radius: 6px;
        max-height: 400px;
        overflow-y: auto;
        padding: 10px;
    }
    
    .selected-product-item {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 12px;
        padding: 15px;
        transition: all 0.2s;
    }
    
    .selected-product-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: #007bff;
    }
    
    .selected-product-item:last-child {
        margin-bottom: 0;
    }
    
    .product-stats {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .custom-discount-input {
        width: 100px;
        margin-left: 10px;
    }
    
    .no-products-message {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }
    
    .loading-spinner {
        display: none;
        text-align: center;
        padding: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Edit Sale: {{ $sale->name }}</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-info">
                        <i class="bi bi-eye"></i> View Sale
                    </a>
                    <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Sales
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.sales.update', $sale) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Sale Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="name">Sale Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $sale->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description', $sale->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="banner_image">Banner Image</label>
                                    @if($sale->banner_image)
                                        <div class="mb-2">
                                            <img src="{{ Storage::url($sale->banner_image) }}" 
                                                 alt="Current banner" 
                                                 class="img-thumbnail" 
                                                 style="max-height: 150px;">
                                            <small class="d-block text-muted">Current banner image</small>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('banner_image') is-invalid @enderror" 
                                           id="banner_image" name="banner_image" accept="image/*">
                                    @error('banner_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" 
                                                   id="start_date" name="start_date" 
                                                   value="{{ old('start_date', $sale->start_date->format('Y-m-d\TH:i')) }}" required>
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="end_date">End Date <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                                                   id="end_date" name="end_date" 
                                                   value="{{ old('end_date', $sale->end_date->format('Y-m-d\TH:i')) }}" required>
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', $sale->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Sale is Active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Discount Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="type">Discount Type <span class="text-danger">*</span></label>
                                            <select class="form-control @error('type') is-invalid @enderror" 
                                                    id="type" name="type" required>
                                                <option value="">Select Type</option>
                                                <option value="percentage" {{ old('type', $sale->type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                                <option value="fixed" {{ old('type', $sale->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                                                <option value="bogo" {{ old('type', $sale->type) === 'bogo' ? 'selected' : '' }}>Buy One Get One</option>
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="discount_value">Discount Value <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control @error('discount_value') is-invalid @enderror" 
                                                   id="discount_value" name="discount_value" 
                                                   value="{{ old('discount_value', $sale->discount_value) }}" required>
                                            @error('discount_value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="max_discount">Maximum Discount (₹)</label>
                                            <input type="number" step="0.01" class="form-control @error('max_discount') is-invalid @enderror" 
                                                   id="max_discount" name="max_discount" 
                                                   value="{{ old('max_discount', $sale->max_discount) }}">
                                            @error('max_discount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="min_order_value">Minimum Order Value (₹)</label>
                                            <input type="number" step="0.01" class="form-control @error('min_order_value') is-invalid @enderror" 
                                                   id="min_order_value" name="min_order_value" 
                                                   value="{{ old('min_order_value', $sale->min_order_value) }}">
                                            @error('min_order_value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="usage_limit">Usage Limit</label>
                                    <input type="number" class="form-control @error('usage_limit') is-invalid @enderror" 
                                           id="usage_limit" name="usage_limit" 
                                           value="{{ old('usage_limit', $sale->usage_limit) }}">
                                    <small class="form-text text-muted">Leave empty for unlimited usage</small>
                                    @error('usage_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Applicable Products</h6>
                                <span class="badge bg-info" id="selected-count">{{ $sale->products->count() }} selected</span>
                            </div>
                            <div class="card-body">
                                <!-- Product Search -->
                                <div class="form-group mb-3">
                                    <label for="product_search">Search & Add Products</label>
                                    <div class="product-search-container">
                                        <input type="text" 
                                               class="form-control" 
                                               id="product_search" 
                                               placeholder="Search products by name, description, or category...">
                                        <div class="product-search-results" id="search_results"></div>
                                    </div>
                                    <small class="text-muted">Type to search from {{ \App\Models\Product::count() }} products</small>
                                </div>
                                
                                <!-- Loading Spinner -->
                                <div class="loading-spinner" id="loading_spinner">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="ms-2">Searching products...</span>
                                </div>
                                
                                <!-- Selected Products with Scrollable Design like Create Page -->
                                <div class="form-group">
                                    <label>Selected Products</label>
                                    <div class="selected-products-container" style="max-height: 400px; overflow-y: auto; border: 1px solid #e3e6f0; padding: 10px; border-radius: 6px; background: #f8f9fa;">
                                        <div class="selected-products" id="selected_products">
                                            @php
                                                $selectedProducts = $sale->products;
                                            @endphp
                                            
                                            @if($selectedProducts->count() > 0)
                                                @foreach($selectedProducts as $product)
                                                    <div class="selected-product-item mb-3 p-3 bg-white border rounded" data-product-id="{{ $product->id }}">
                                                        <input type="checkbox" name="products[]" value="{{ $product->id }}" 
                                                               checked style="display: none;">
                                                        
                                                        <div class="d-flex align-items-center">
                                                            @php
                                                                $thumbnailImage = $product->getThumbnailImage();
                                                            @endphp
                                                            
                                                            @if($thumbnailImage)
                                                                <img src="{{ asset('storage/' . $thumbnailImage->image_path) }}" 
                                                                     alt="{{ $product->name }}" 
                                                                     class="me-3"
                                                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;"
                                                                     onerror="this.src='{{ asset('images/no-image.png') }}'">
                                                            @else
                                                                <div class="me-3 d-flex align-items-center justify-content-center" 
                                                                     style="width: 60px; height: 60px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px;">
                                                                    <i class="bi bi-image text-muted"></i>
                                                                </div>
                                                            @endif
                                                            
                                                            <div class="product-info flex-grow-1">
                                                                <div class="fw-bold mb-1">{{ $product->name }}</div>
                                                                <div class="product-stats small text-muted mb-2">
                                                                    <span class="badge bg-secondary me-2">{{ $product->category->name ?? 'No Category' }}</span>
                                                                    <span class="me-2">₹{{ number_format($product->price, 2) }}</span>
                                                                    @if($product->variations_count > 1)
                                                                        <span class="text-info">{{ $product->variations_count }} variants</span>
                                                                    @endif
                                                                </div>
                                                                
                                                                <!-- Custom Discount for this product -->
                                                                <div class="mt-2">
                                                                    <label class="form-label small text-muted mb-1">Custom discount (optional):</label>
                                                                    <div class="d-flex align-items-center">
                                                                        <input type="number" 
                                                                               name="custom_discounts[{{ $product->id }}]" 
                                                                               class="form-control form-control-sm" 
                                                                               placeholder="%" 
                                                                               value="{{ $product->pivot->custom_discount ?? '' }}"
                                                                               min="0" 
                                                                               max="100" 
                                                                               step="0.01"
                                                                               style="width: 80px;">
                                                                        <span class="ms-2 small text-muted">%</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <button type="button" class="btn btn-sm btn-outline-danger ms-3" onclick="removeProduct({{ $product->id }})" title="Remove product">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="no-products-message text-center py-4" id="no_products_message">
                                                    <i class="bi bi-box text-muted" style="font-size: 2rem;"></i>
                                                    <p class="mt-2 mb-0 text-muted">No products selected</p>
                                                    <small class="text-muted">Search and add products above</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Bulk Actions -->
                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Bulk Actions:</small>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearAllProducts()">
                                                <i class="bi bi-trash"></i> Clear All
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-sm" onclick="showCategoryModal()">
                                                <i class="bi bi-tags"></i> Add by Category
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-body text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-lg"></i> Update Sale
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category Selection Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Add Products by Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="bulk_categories">Select Categories</label>
                    <select class="form-control" id="bulk_categories" multiple size="6">
                        @foreach(\App\Models\Category::withCount('products')->get() as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }} ({{ $category->products_count }} products)
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl/Cmd to select multiple categories</small>
                </div>
                
                <div class="form-group">
                    <label for="bulk_discount">Apply Custom Discount (%)</label>
                    <input type="number" class="form-control" id="bulk_discount" 
                           placeholder="Optional: Override sale discount for these products"
                           min="0" max="100" step="0.01">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addProductsByCategory()">
                    <i class="bi bi-plus-circle"></i> Add Products
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let searchTimeout;
let selectedProductIds = new Set();

// Initialize selected products
document.addEventListener('DOMContentLoaded', function() {
    // Get initially selected products
    document.querySelectorAll('input[name="products[]"]:checked').forEach(input => {
        selectedProductIds.add(parseInt(input.value));
    });
    updateSelectedCount();
    
    // Initialize product search
    initializeProductSearch();
});

// Product search functionality
function initializeProductSearch() {
    const productSearchElement = document.getElementById('product_search');
    if (productSearchElement) {
        productSearchElement.addEventListener('input', function(e) {
            const query = e.target.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                hideSearchResults();
                return;
            }
            
            searchTimeout = setTimeout(() => {
                searchProducts(query);
            }, 300);
        });
    }
}

// Hide search results when clicking outside
document.addEventListener('click', function(e) {
    const searchContainer = document.querySelector('.product-search-container');
    if (searchContainer && !e.target.closest('.product-search-container')) {
        hideSearchResults();
    }
});

function searchProducts(query) {
    showLoading();
    
    console.log('Searching for:', query);
    console.log('Search URL:', `{{ route('admin.sales.search-products') }}?q=${encodeURIComponent(query)}`);
    
    fetch(`{{ route('admin.sales.search-products') }}?q=${encodeURIComponent(query)}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Search response data:', data);
        hideLoading();
        displaySearchResults(data.products || []);
    })
    .catch(error => {
        hideLoading();
        console.error('Search error:', error);
        
        // Show error message to user
        const resultsContainer = document.getElementById('search_results');
        if (resultsContainer) {
            resultsContainer.innerHTML = '<div class="p-3 text-center text-danger">Search failed. Please try again.</div>';
            showSearchResults();
        }
    });
}

function displaySearchResults(products) {
    const resultsContainer = document.getElementById('search_results');
    
    if (!resultsContainer) {
        console.error('Search results container not found');
        return;
    }
    
    if (products.length === 0) {
        resultsContainer.innerHTML = '<div class="p-3 text-center text-muted">No products found</div>';
    } else {
        resultsContainer.innerHTML = products.map(product => {
            const isSelected = selectedProductIds.has(product.id);
            const imageUrl = product.thumbnail ? 
                `{{ asset('storage/') }}/${product.thumbnail}` : 
                `{{ asset('images/no-image.png') }}`;
            
            return `
                <div class="product-search-item ${isSelected ? 'selected' : ''}" 
                     onclick="toggleProduct(${product.id})" 
                     data-product-id="${product.id}">
                    <img src="${imageUrl}" alt="${product.name}" 
                         onerror="this.src='{{ asset('images/no-image.png') }}'">
                    
                    <div class="product-search-info">
                        <div class="product-search-name">${product.name}</div>
                        <div class="product-search-details">
                            <span class="product-category">${product.category || 'No Category'}</span>
                            <span class="product-price">₹${parseFloat(product.price).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                            ${product.variations_count > 1 ? `<span class="product-variations">${product.variations_count} variants</span>` : ''}
                        </div>
                    </div>
                    
                    <div class="product-selection-status">
                        ${isSelected ? 
                            '<button class="product-selected-btn">✓ Added</button>' : 
                            '<button class="product-add-btn">+ Add</button>'
                        }
                    </div>
                </div>
            `;
        }).join('');
    }
    
    showSearchResults();
}

function toggleProduct(productId) {
    if (selectedProductIds.has(productId)) {
        removeProduct(productId);
    } else {
        addProduct(productId);
    }
}

function addProduct(productId) {
    // Get product data from search results
    fetch(`{{ route('admin.sales.get-product') }}?id=${productId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(product => {
        addProductToSelected(product);
        selectedProductIds.add(productId);
        updateSelectedCount();
        hideSearchResults();
        document.getElementById('product_search').value = '';
        
        // Update search results display
        const searchItem = document.querySelector(`[data-product-id="${productId}"]`);
        if (searchItem) {
            searchItem.classList.add('selected');
            const statusBtn = searchItem.querySelector('.product-selection-status button');
            if (statusBtn) {
                statusBtn.className = 'product-selected-btn';
                statusBtn.innerHTML = '✓ Added';
            }
        }
    })
    .catch(error => {
        console.error('Error adding product:', error);
        alert('Error adding product. Please try again.');
    });
}

function addProductToSelected(product) {
    const container = document.getElementById('selected_products');
    const noProductsMessage = document.getElementById('no_products_message');
    
    if (noProductsMessage) {
        noProductsMessage.remove();
    }
    
    const imageUrl = product.thumbnail ? 
        `{{ asset('storage/') }}/${product.thumbnail}` : 
        null;
    
    const productHtml = `
        <div class="selected-product-item mb-3 p-3 bg-white border rounded" data-product-id="${product.id}">
            <input type="checkbox" name="products[]" value="${product.id}" checked style="display: none;">
            
            <div class="d-flex align-items-center">
                ${imageUrl ? 
                    `<img src="${imageUrl}" alt="${product.name}" 
                         class="me-3"
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;"
                         onerror="this.src='{{ asset('images/no-image.png') }}'">` :
                    `<div class="me-3 d-flex align-items-center justify-content-center" 
                         style="width: 60px; height: 60px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px;">
                        <i class="bi bi-image text-muted"></i>
                    </div>`
                }
                
                <div class="product-info flex-grow-1">
                    <div class="fw-bold mb-1">${product.name}</div>
                    <div class="product-stats small text-muted mb-2">
                        <span class="badge bg-secondary me-2">${product.category || 'No Category'}</span>
                        <span class="me-2">₹${parseFloat(product.price).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                        ${product.variations_count > 1 ? `<span class="text-info">${product.variations_count} variants</span>` : ''}
                    </div>
                    
                    <div class="mt-2">
                        <label class="form-label small text-muted mb-1">Custom discount (optional):</label>
                        <div class="d-flex align-items-center">
                            <input type="number" 
                                   name="custom_discounts[${product.id}]" 
                                   class="form-control form-control-sm" 
                                   placeholder="%" 
                                   min="0" 
                                   max="100" 
                                   step="0.01"
                                   style="width: 80px;">
                            <span class="ms-2 small text-muted">%</span>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-sm btn-outline-danger ms-3" onclick="removeProduct(${product.id})" title="Remove product">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', productHtml);
}

function removeProduct(productId) {
    const productElement = document.querySelector(`[data-product-id="${productId}"]`);
    if (productElement) {
        productElement.remove();
    }
    
    selectedProductIds.delete(productId);
    updateSelectedCount();
    
    // Show no products message if none selected
    const container = document.getElementById('selected_products');
    if (container.children.length === 0) {
        container.innerHTML = `
            <div class="no-products-message text-center py-4" id="no_products_message">
                <i class="bi bi-box text-muted" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0 text-muted">No products selected</p>
                <small class="text-muted">Search and add products above</small>
            </div>
        `;
    }
    
    // Update search results if visible
    const searchItem = document.querySelector(`#search_results [data-product-id="${productId}"]`);
    if (searchItem) {
        searchItem.classList.remove('selected');
        const statusBtn = searchItem.querySelector('.product-selection-status button');
        if (statusBtn) {
            statusBtn.className = 'product-add-btn';
            statusBtn.innerHTML = '+ Add';
        }
    }
}

function clearAllProducts() {
    if (confirm('Are you sure you want to remove all selected products?')) {
        document.getElementById('selected_products').innerHTML = `
            <div class="no-products-message text-center py-4" id="no_products_message">
                <i class="bi bi-box text-muted" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0 text-muted">No products selected</p>
                <small class="text-muted">Search and add products above</small>
            </div>
        `;
        selectedProductIds.clear();
        updateSelectedCount();
    }
}

function showCategoryModal() {
    const categoryModal = document.getElementById('categoryModal');
    if (categoryModal) {
        const modal = new bootstrap.Modal(categoryModal);
        modal.show();
    } else {
        console.error('Category modal not found');
    }
}

function addProductsByCategory() {
    const bulkCategoriesElement = document.getElementById('bulk_categories');
    const bulkDiscountElement = document.getElementById('bulk_discount');
    
    if (!bulkCategoriesElement) {
        console.error('Bulk categories element not found');
        return;
    }
    
    const selectedCategories = Array.from(bulkCategoriesElement.selectedOptions)
        .map(option => option.value);
    
    if (selectedCategories.length === 0) {
        alert('Please select at least one category');
        return;
    }
    
    const customDiscount = bulkDiscountElement ? bulkDiscountElement.value : '';
    
    // Show loading
    const categoryModal = document.getElementById('categoryModal');
    if (categoryModal) {
        const modal = bootstrap.Modal.getInstance(categoryModal);
        if (modal) {
            modal.hide();
        }
    }
    
    showLoading();
    
    fetch(`{{ route('admin.sales.products-by-category') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            categories: selectedCategories,
            custom_discount: customDiscount,
            exclude_ids: Array.from(selectedProductIds)
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            data.products.forEach(product => {
                addProductToSelected(product);
                selectedProductIds.add(product.id);
            });
            updateSelectedCount();
            
            // Clear modal form
            if (bulkCategoriesElement) {
                bulkCategoriesElement.selectedIndex = -1;
            }
            if (bulkDiscountElement) {
                bulkDiscountElement.value = '';
            }
            
            alert(`Added ${data.products.length} products from selected categories.`);
        } else {
            alert('Error adding products: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('Error adding products. Please try again.');
    });
}

function updateSelectedCount() {
    const selectedCountElement = document.getElementById('selected-count');
    if (selectedCountElement) {
        selectedCountElement.textContent = `${selectedProductIds.size} selected`;
    }
}

function showSearchResults() {
    const searchResults = document.getElementById('search_results');
    if (searchResults) {
        searchResults.style.display = 'block';
    }
}

function hideSearchResults() {
    const searchResults = document.getElementById('search_results');
    if (searchResults) {
        searchResults.style.display = 'none';
    }
}

function showLoading() {
    const loadingSpinner = document.getElementById('loading_spinner');
    if (loadingSpinner) {
        loadingSpinner.style.display = 'block';
    }
}

function hideLoading() {
    const loadingSpinner = document.getElementById('loading_spinner');
    if (loadingSpinner) {
        loadingSpinner.style.display = 'none';
    }
}
</script>