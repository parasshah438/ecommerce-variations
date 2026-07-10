@extends('admin.layout')

@section('title', 'Products')
@section('page-title', 'Products')
@section('page-description', 'Manage your product catalog')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">All Products ({{ $products->total() }})</h4>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fas fa-filter me-2"></i>Filter
            @if(request()->hasAny(['search', 'category_id', 'brand_id', 'status', 'has_variations', 'price_min', 'price_max', 'date_from', 'date_to', 'quick_filter']))
                <span class="badge bg-primary ms-1">Active</span>
            @endif
        </button>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Add New Product
        </a>
    </div>
</div>

<!-- Active Filters Indicator -->
@if(request()->hasAny(['search', 'category_id', 'brand_id', 'status', 'has_variations', 'price_min', 'price_max', 'date_from', 'date_to', 'quick_filter']))
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info d-flex justify-content-between align-items-center py-2">
                <div>
                    <i class="fas fa-filter me-2"></i>
                    <strong>Active Filters:</strong>
                    @if(request('search'))
                        <span class="badge bg-light text-dark me-1">Search: "{{ request('search') }}"</span>
                    @endif
                    @if(request('category_id'))
                        @php $catName = $categories->where('id', request('category_id'))->first()?->name ?? request('category_id'); @endphp
                        <span class="badge bg-primary me-1">Category: {{ $catName }}</span>
                    @endif
                    @if(request('brand_id'))
                        @php $brandName = $brands->where('id', request('brand_id'))->first()?->name ?? request('brand_id'); @endphp
                        <span class="badge bg-secondary me-1">Brand: {{ $brandName }}</span>
                    @endif
                    @if(request('status'))
                        <span class="badge bg-{{ request('status') === 'active' ? 'success' : 'danger' }} me-1">{{ ucfirst(request('status')) }}</span>
                    @endif
                    @if(request('has_variations'))
                        <span class="badge bg-info me-1">{{ request('has_variations') === 'yes' ? 'With Variations' : 'Simple Products' }}</span>
                    @endif
                    @if(request('price_min') || request('price_max'))
                        <span class="badge bg-warning me-1">
                            Price: ₹{{ request('price_min') ?: '0' }} - ₹{{ request('price_max') ?: '∞' }}
                        </span>
                    @endif
                    @if(request('date_from') || request('date_to'))
                        <span class="badge bg-info me-1">
                            Date: {{ request('date_from') ? request('date_from') : 'Start' }} to {{ request('date_to') ? request('date_to') : 'End' }}
                        </span>
                    @endif
                    @if(request('quick_filter'))
                        <span class="badge bg-primary me-1">Period: {{ str_replace('_', ' ', ucwords(request('quick_filter'))) }}</span>
                    @endif
                </div>
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Clear All
                </a>
            </div>
        </div>
    </div>
@endif

<!-- Search Bar -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Search products..." value="{{ request('search') }}">
            @if(request('search'))
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
            @endif
        </div>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <small class="text-muted">
            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results
            @if(request()->hasAny(['search', 'category_id', 'brand_id', 'status', 'has_variations', 'price_min', 'price_max', 'date_from', 'date_to', 'quick_filter']))
                (filtered)
            @endif
        </small>
    </div>
</div>

@if($products->count() > 0)
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Variations</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 product-thumbnail">
                                        @php
                                            $thumbnailImage = $product->getThumbnailImage();
                                        @endphp
                                        @if($thumbnailImage && $thumbnailImage->path)
                                            @php
                                                $isVariationImage = isset($thumbnailImage->product_variation_id) && $thumbnailImage->product_variation_id 
                                                                   || get_class($thumbnailImage) === 'App\Models\ProductVariationImage';
                                                
                                                // Check if original file exists
                                                $originalExists = Storage::disk('public')->exists($thumbnailImage->path);
                                                
                                                if ($originalExists) {
                                                    $thumbnailUrl = $thumbnailImage->getThumbnailUrl(150);
                                                    $pathInfo = pathinfo($thumbnailImage->path);
                                                    $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_150.webp';
                                                    $webpExists = Storage::disk('public')->exists($webpPath);
                                                } else {
                                                    $thumbnailUrl = asset('images/product-placeholder.jpg');
                                                    $webpExists = false;
                                                }
                                            @endphp
                                            
                                            @if($originalExists)
                                                <picture>
                                                    @if($webpExists)
                                                        <source srcset="{{ Storage::disk('public')->url($webpPath) }}" type="image/webp">
                                                    @endif
                                                    <img src="{{ $thumbnailUrl }}" 
                                                         alt="{{ $product->name }}" 
                                                         style="width: 50px; height: 50px; object-fit: cover;" 
                                                         class="rounded"
                                                         loading="lazy"
                                                         onerror="handleImageError(this)">
                                                </picture>
                                                @if($product->cover_image)
                                                    <span class="position-absolute badge bg-primary rounded-pill variation-indicator" 
                                                          title="Cover Image">C</span>
                                                @elseif($isVariationImage)
                                                    <span class="position-absolute badge bg-info rounded-pill variation-indicator" 
                                                          title="Variation Image">V</span>
                                                @endif
                                            @else
                                                <!-- File doesn't exist, show placeholder -->
                                                <div class="bg-warning rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;" 
                                                     title="Image file not found">
                                                    <i class="bi bi-exclamation-triangle text-white"></i>
                                                </div>
                                                @if($isVariationImage)
                                                    <span class="position-absolute badge bg-info rounded-pill variation-indicator" 
                                                          title="Variation Image">V</span>
                                                @endif
                                            @endif
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0 product-name">{{ Str::limit($product->name, 40) }}</h6>
                                        <small class="product-slug">{{ $product->slug }}</small>
                                        @if($product->variations->count() > 0)
                                            <br><small class="text-info variation-info">
                                                <i class="bi bi-layers"></i> 
                                                {{ $product->variations->count() }} variations
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>{{ $product->brand->name ?? 'N/A' }}</td>
                            <td>
                                @if($product->variations->count() > 0)
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-primary mb-1">{{ $product->variations->count() }} variations</span>
                                        @php
                                            $hasVariationImages = $product->variationImages->count() > 0 || 
                                                                 $product->images->where('product_variation_id', '!=', null)->count() > 0;
                                        @endphp
                                        @if($hasVariationImages)
                                            <small class="text-success">
                                                <i class="bi bi-images"></i> Has variation images
                                            </small>
                                        @else
                                            <small class="text-muted">
                                                <i class="bi bi-image"></i> No variation images
                                            </small>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-secondary">Simple product</span>
                                @endif
                            </td>
                            <td>
                                <div class="price-display">
                                    <strong>₹{{ number_format($product->price, 2) }}</strong>
                                    @if($product->mrp && $product->mrp > $product->price)
                                        <br><small class="text-muted text-decoration-line-through">₹{{ number_format($product->mrp, 2) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $product->active ? 'success' : 'secondary' }}">
                                    {{ $product->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $product->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.products.show', $product) }}" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteProduct({{ $product->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
        </div>
        <div>
            {{ $products->links('custom.pagination') }}
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-box-seam fs-1 text-muted d-block mb-3"></i>
            <h5>No products found</h5>
            <p class="text-muted">
                @if(request()->hasAny(['search', 'category_id', 'brand_id', 'status', 'has_variations', 'price_min', 'price_max', 'date_from', 'date_to', 'quick_filter']))
                    No products match your current filters. Try adjusting your search criteria.
                @else
                    Start building your product catalog by adding your first product.
                @endif
            </p>
            @if(!request()->hasAny(['search', 'category_id', 'brand_id', 'status', 'has_variations', 'price_min', 'price_max', 'date_from', 'date_to', 'quick_filter']))
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Add Your First Product
                </a>
            @else
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>
                    Clear Filters
                </a>
            @endif
        </div>
    </div>
@endif

@include('admin.products.modals.filter-modal')
@endsection

@push('styles')
<style>
.product-thumbnail {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-thumbnail picture,
.product-thumbnail img {
    transition: opacity 0.3s ease;
}

.product-thumbnail img:not([src]) {
    opacity: 0;
}

.product-thumbnail img[src] {
    opacity: 1;
}

/* Optimized image loading animation */
.product-thumbnail img {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

.product-thumbnail img[src]:not([src=""]) {
    animation: none;
    background: none;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.variation-indicator {
    position: absolute;
    top: -2px;
    right: -2px;
    font-size: 0.6rem;
    transform: scale(0.8);
    z-index: 10;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
}

.variation-info {
    font-size: 0.75rem;
}

.table td {
    vertical-align: middle;
}

.product-name {
    font-weight: 600;
    color: #495057;
}

.product-slug {
    font-size: 0.8rem;
    color: #6c757d;
}

.price-display {
    font-size: 0.9rem;
}

.price-display strong {
    color: #28a745;
}

/* Better placeholder styling */
.product-thumbnail .bg-light {
    border: 1px dashed #dee2e6;
    color: #6c757d;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .product-thumbnail {
        width: 40px !important;
        height: 40px !important;
    }
    
    .product-thumbnail picture img,
    .product-thumbnail > div {
        width: 40px !important;
        height: 40px !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/products/${productId}`;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function handleImageError(img) {
    try {
        // Find the closest picture element or the image container
        const pictureElement = img.closest('picture') || img.parentElement;
        const container = pictureElement.closest('.product-thumbnail');
        
        if (container) {
            container.innerHTML = '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="bi bi-image text-muted"></i></div>';
        } else {
            // Fallback: hide the image and show placeholder
            img.style.display = 'none';
            if (img.nextElementSibling) {
                img.nextElementSibling.style.display = 'block';
            }
        }
    } catch (error) {
        console.warn('Image error handler failed:', error);
        // Last resort: just hide the broken image
        img.style.display = 'none';
    }
}

// Search with debounce
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value.trim();
                
                if (searchTerm.length >= 3 || searchTerm.length === 0) {
                    const urlParams = new URLSearchParams(window.location.search);
                    
                    if (searchTerm) {
                        urlParams.set('search', searchTerm);
                    } else {
                        urlParams.delete('search');
                    }
                    
                    // Preserve other filter params
                    const newUrl = window.location.pathname + '?' + urlParams.toString();
                    window.location.href = newUrl;
                }
            }, 500);
        });
        
        // Trigger search on Enter key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                const searchTerm = this.value.trim();
                const urlParams = new URLSearchParams(window.location.search);
                
                if (searchTerm) {
                    urlParams.set('search', searchTerm);
                } else {
                    urlParams.delete('search');
                }
                
                const newUrl = window.location.pathname + '?' + urlParams.toString();
                window.location.href = newUrl;
            }
        });
    }
});
</script>
@endpush
