@extends('layouts.frontend')

@section('title', 'Products - ' . config('app.name'))

@section('breadcrumb')
<div class="bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Products</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<style>
/* Advanced Filters Sidebar Positioning and Overlap Fix */
.filters-sidebar {
    position: relative;
    z-index: 10;
}

.filters-sidebar .card {
    position: sticky;
    top: 20px;
    z-index: 11;
    border: none !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
    border-radius: 16px !important;
    overflow: hidden;
    max-height: calc(100vh - 40px);
    background: white;
}

.filters-sidebar .card-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef) !important;
    border-bottom: 1px solid #dee2e6 !important;
    padding: 1.25rem 1.5rem !important;
    position: sticky;
    top: 0;
    z-index: 12;
}

.filters-sidebar .card-body {
    padding: 0 !important;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
    position: relative;
    z-index: 11;
}

/* Filter scroll containers */
.filter-scroll-container {
    max-height: 200px;
    overflow-y: auto;
    position: relative;
    z-index: 1;
}

.filter-scroll-container::-webkit-scrollbar {
    width: 4px;
}

.filter-scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.filter-scroll-container::-webkit-scrollbar-thumb {
    background: #007bff;
    border-radius: 2px;
}

.filter-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #0056b3;
}

/* Filter items styling */
.filter-item {
    transition: background-color 0.2s ease;
    border-radius: 8px;
    padding: 4px 8px;
    margin: 2px 0;
    position: relative;
    z-index: 1;
}

.filter-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.form-check-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
    z-index: 1;
}

/* Quick filters styling */
.quick-filter {
    border-radius: 15px;
    font-size: 0.8rem;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
    border: 1px solid #dee2e6;
}

.quick-filter:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.quick-filter.active {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

/* Rating filter container */
.rating-filter-container {
    position: relative;
    z-index: 1;
}

/* Border sections */
.border-bottom {
    position: relative;
    z-index: 1;
    border-bottom: 1px solid #f0f0f0 !important;
}

/* Price range styling */
.btn-outline-secondary {
    border-color: #dee2e6;
    transition: all 0.2s ease;
    position: relative;
    z-index: 1;
}

.btn-outline-secondary:hover {
    background-color: #f8f9fa;
    border-color: #adb5bd;
}

/* Prevent header overlap - Critical Fix */
.main-header,
.navbar,
nav.navbar {
    position: relative !important;
    z-index: 1030 !important;
}

.breadcrumb-section,
.bg-light {
    position: relative !important;
    z-index: 1020 !important;
}

/* Responsive sidebar adjustments */
@media (max-width: 991.98px) {
    .filters-sidebar .card {
        position: relative !important;
        top: 0 !important;
        max-height: none !important;
        margin-bottom: 2rem;
    }
    
    .filters-sidebar .card-body {
        max-height: none !important;
        overflow-y: visible !important;
    }
    
    .filter-scroll-container {
        max-height: 150px;
    }
}

/* Sidebar scrollbar styling */
.filters-sidebar .card-body::-webkit-scrollbar {
    width: 6px;
}

.filters-sidebar .card-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.filters-sidebar .card-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.filters-sidebar .card-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Product Cards Styling to match Welcome page */
.product-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    height: 100%;
    border: none !important;
    position: relative;
}

/* Professional loading overlay */
.filter-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeInOverlay 0.2s ease-out;
}

@keyframes fadeInOverlay {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Custom spinner design */
.premium-spinner {
    width: 60px;
    height: 60px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    position: relative;
}

.premium-spinner::before {
    content: '';
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    border: 3px solid transparent;
    border-top: 3px solid #28a745;
    border-radius: 50%;
    animation: spin 1.5s linear infinite reverse;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Body blur when loading */
body.filter-loading {
    overflow: hidden;
}

body.filter-loading .container:not(.loading-container) {
    filter: blur(1px);
    transition: filter 0.3s ease;
}

/* Fade in animation for filtered results */
.filter-results {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.product-image-container {
    position: relative;
    overflow: hidden;
    height: 250px;
    background: #f8f9fa;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.4s ease;
}

.product-card:hover .product-image {
    transform: scale(1.08);
}

.discount-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    z-index: 2;
    box-shadow: 0 2px 8px rgba(238, 90, 82, 0.3);
}

.stock-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    z-index: 2;
}

.stock-badge.out-of-stock {
    background: rgba(108, 117, 125, 0.9);
    color: white;
}

.variations-badge {
    position: absolute;
    bottom: 15px;
    left: 15px;
    background: rgba(13, 110, 253, 0.9);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    opacity: 0;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.quick-actions {
    display: flex;
    gap: 10px;
}

.quick-action-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: white;
    border: none;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.quick-action-btn:hover {
    background: #f76631;
    color: white;
    transform: scale(1.1);
}

.quick-action-btn.active {
    background: #dc3545;
    color: white;
}

.product-brand {
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-title a {
    color: #333;
    font-weight: 600;
    line-height: 1.4;
    transition: color 0.3s ease;
}

.product-title a:hover {
    color: #f76631;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 8px;
}

.rating-stars {
    color: #ffc107;
    font-size: 0.9rem;
}

.rating-text {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

.product-price .current-price {
    font-size: 1.4rem;
    font-weight: 700;
    color: #f76631;
}

.product-price .original-price {
    font-size: 1rem;
    color: #6c757d;
    text-decoration: line-through;
    margin-left: 8px;
    font-weight: 500;
}

.btn-add-cart {
    width: 100%;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    border: none;
    background: linear-gradient(135deg, #f76631, #e55a2b);
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-add-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(247, 102, 49, 0.3);
    color: white;
}
</style>

<div class="container">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <!-- Advanced Filters Sidebar -->
            <div class="filters-sidebar">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-funnel me-2 text-primary"></i>Filters</h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllFiltersBtn">
                                <i class="bi bi-x-circle me-1"></i>Clear All
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <form id="filterForm">
                            <!-- Search Filter -->
                            <div class="border-bottom px-3 py-3">
                                <label class="form-label fw-bold text-dark mb-2">
                                    <i class="bi bi-search me-2 text-primary"></i>Search Products
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" name="q" placeholder="Search products..." value="{{ request('q') }}">
                                    <button class="btn btn-outline-primary" type="button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Categories Filter -->
                            @if($categories->count() > 0)
                            <div class="border-bottom px-3 py-3">
                                <label class="form-label fw-bold text-dark mb-3">
                                    <i class="bi bi-grid-3x3-gap me-2 text-primary"></i>Categories
                                </label>
                                <div class="filter-scroll-container" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($categories as $category)
                                    <div class="form-check mb-2 filter-item">
                                        <input class="form-check-input category-filter" type="checkbox" 
                                               name="categories[]" value="{{ $category->id }}" 
                                               id="category{{ $category->id }}" data-filter-type="category"
                                               {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="category{{ $category->id }}">
                                            <span>{{ $category->name }}</span>
                                            <span class="badge bg-light text-dark">{{ $category->products_count ?? 0 }}</span>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Brands Filter -->
                            @if($brands->count() > 0)
                            <div class="border-bottom px-3 py-3">
                                <label class="form-label fw-bold text-dark mb-3">
                                    <i class="bi bi-award me-2 text-primary"></i>Brands
                                </label>
                                <div class="filter-scroll-container" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($brands as $brand)
                                    <div class="form-check mb-2 filter-item">
                                        <input class="form-check-input brand-filter" type="checkbox" 
                                               name="brands[]" value="{{ $brand->id }}" 
                                               id="brand{{ $brand->id }}" data-filter-type="brand"
                                               {{ in_array($brand->id, request('brands', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="brand{{ $brand->id }}">
                                            <span>{{ $brand->name }}</span>
                                            <span class="badge bg-light text-dark">{{ $brand->products_count ?? 0 }}</span>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Price Range Filter -->
                            <div class="border-bottom px-3 py-3">
                                <label class="form-label fw-bold text-dark mb-3">
                                    <i class="bi bi-currency-rupee me-2 text-primary"></i>Price Range
                                </label>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm price-filter" 
                                               id="minPrice" name="min_price" placeholder="Min ₹" min="0" 
                                               max="{{ $priceRange->max_price ?? 10000 }}" 
                                               value="{{ request('min_price') }}" data-filter-type="price">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm price-filter" 
                                               id="maxPrice" name="max_price" placeholder="Max ₹" min="0" 
                                               max="{{ $priceRange->max_price ?? 10000 }}" 
                                               value="{{ request('max_price') }}" data-filter-type="price">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">
                                        Range: ₹{{ number_format($priceRange->min_price ?? 0) }} - ₹{{ number_format($priceRange->max_price ?? 10000) }}
                                    </small>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" onclick="clearPriceRange()">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Rating Filter -->
                            <div class="border-bottom px-3 py-3">
                                <label class="form-label fw-bold text-dark mb-3">
                                    <i class="bi bi-star me-2 text-primary"></i>Customer Rating
                                </label>
                                <div class="rating-filter-container">
                                    <!-- Clear rating option -->
                                    <div class="form-check mb-2 filter-item">
                                        <input class="form-check-input rating-filter" type="radio" name="rating" value="" id="ratingAll" data-filter-type="rating" checked>
                                        <label class="form-check-label d-flex align-items-center" for="ratingAll">
                                            <span class="me-2">All Ratings</span>
                                        </label>
                                    </div>
                                    @for($i = 5; $i >= 1; $i--)
                                    <div class="form-check mb-2 filter-item">
                                        <input class="form-check-input rating-filter" type="radio" name="rating" value="{{ $i }}" id="rating{{ $i }}" data-filter-type="rating">
                                        <label class="form-check-label d-flex align-items-center cursor-pointer" for="rating{{ $i }}">
                                            <div class="text-warning me-2" style="min-width: 80px;">
                                                @for($j = 1; $j <= 5; $j++)
                                                    <i class="bi bi-star{{ $j <= $i ? '-fill' : '' }}"></i>
                                                @endfor
                                            </div>
                                            <span>{{ $i }} Stars & Up</span>
                                        </label>
                                    </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Availability Filter -->
                            <div class="border-bottom px-3 py-3">
                                <label class="form-label fw-bold text-dark mb-3">
                                    <i class="bi bi-box me-2 text-primary"></i>Availability
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input stock-filter" type="checkbox" id="inStockOnly" name="in_stock" data-filter-type="stock" {{ request('in_stock') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="inStockOnly">
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        In Stock Only
                                    </label>
                                </div>
                            </div>

                            <!-- Quick Filters -->
                            <div class="px-3 py-3">
                                <label class="form-label fw-bold text-dark mb-3">
                                    <i class="bi bi-lightning me-2 text-primary"></i>Quick Filters
                                </label>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary btn-sm quick-filter" type="button" data-filter="discount">
                                        <i class="bi bi-percent me-1"></i>On Sale
                                    </button>
                                    <button class="btn btn-outline-success btn-sm quick-filter" type="button" data-filter="new">
                                        <i class="bi bi-star me-1"></i>New Arrivals
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm quick-filter" type="button" data-filter="trending">
                                        <i class="bi bi-graph-up me-1"></i>Trending
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>        
        <div class="col-lg-9 col-md-8">
            <!-- Sort and View Options -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">All Products</h2>
                <div class="d-flex gap-2">
                    <input id="searchBox" class="form-control" placeholder="Search products..." 
                           style="min-width:250px;" value="{{ request('q') }}">
                    <select class="form-select form-select-sm" style="width: auto;" id="sortSelect">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Sort by: Featured</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                    </select>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary btn-sm active">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-list"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="product-grid" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                @include('products._list', ['products' => $products])
            </div>
            
            <div class="text-center mt-4">
                <button id="loadMoreBtn" class="btn btn-primary">Load more</button>
            </div>
        </div>
    </div>
</div>

<!-- Premium Loading Overlay -->
<div id="filterLoadingOverlay" class="filter-loading-overlay" style="display: none;">
    <div class="premium-spinner"></div>
</div>

<!-- Wishlist Success Animation Container -->
<div id="wishlist-animation-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999;"></div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    let page = 1;
    let isLoadingMore = false;
    let filterTimeout = null;
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const grid = document.getElementById('product-grid');
    const filterForm = document.getElementById('filterForm');
    const searchBox = document.getElementById('searchBox');
    const sortSelect = document.getElementById('sortSelect');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');

    // Auto-apply filters on form change (checkboxes, radio buttons, price inputs)
    if(filterForm) {
        // Handle all input changes
        filterForm.addEventListener('change', function(e) {
            if(e.target.type === 'checkbox' || e.target.type === 'radio') {
                applyFilters();
            }
        });

        // Handle price input changes with debounce
        filterForm.addEventListener('input', function(e) {
            if(e.target.type === 'number' || e.target.type === 'text') {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    applyFilters();
                }, 800); // Wait 800ms after user stops typing
            }
        });

        // Prevent form submission
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
        });
    }

    // Clear all filters functionality
    const clearAllFiltersBtn = document.getElementById('clearAllFiltersBtn');
    if(clearAllFiltersBtn) {
        clearAllFiltersBtn.addEventListener('click', function() {
            // Clear all form inputs
            filterForm.reset();
            
            // Clear search input specifically
            const searchInput = document.getElementById('searchInput');
            if(searchInput) searchInput.value = '';
            
            // Clear sort select
            sortSelect.value = 'created_at';
            
            // Reset rating to "All Ratings"
            const ratingAll = document.getElementById('ratingAll');
            if(ratingAll) ratingAll.checked = true;
            
            // Clear quick filter buttons
            document.querySelectorAll('.quick-filter').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Apply filters (which will be empty, showing all products)
            applyFilters();
        });
    }

    // Quick filter buttons functionality
    document.querySelectorAll('.quick-filter').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Toggle active state
            this.classList.toggle('active');
            
            // Apply filters immediately
            applyFilters();
        });
    });

    // Price range clear functionality
    window.clearPriceRange = function() {
        document.getElementById('minPrice').value = '';
        document.getElementById('maxPrice').value = '';
        applyFilters();
    };

    // Search functionality with debounce
    if(searchBox){
        searchBox.addEventListener('input', function(e) {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                applyFilters();
            }, 500); // Wait 500ms after user stops typing
        });

        // Also handle Enter key for immediate search
        searchBox.addEventListener('keypress', function(e){
            if(e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(filterTimeout);
                applyFilters();
            }
        });
    }

    // Sort functionality
    if(sortSelect) {
        sortSelect.addEventListener('change', function() {
            applyFilters();
        });
    }

    // Apply filters function
    function applyFilters() {
        const loadingOverlay = document.getElementById('filterLoadingOverlay');
        
        // Show loading overlay with body blur
        if (loadingOverlay) {
            document.body.classList.add('filter-loading');
            loadingOverlay.style.display = 'flex';
        }

        const formData = new FormData(filterForm);
        
        // Add search parameter from search input
        const searchInput = document.getElementById('searchInput');
        if(searchInput && searchInput.value.trim()) {
            formData.append('q', searchInput.value.trim());
        }
        
        // Add sort parameter
        if(sortSelect.value) {
            formData.append('sort', sortSelect.value);
        }
        
        // Add quick filter parameters
        const activeQuickFilters = [];
        document.querySelectorAll('.quick-filter.active').forEach(btn => {
            activeQuickFilters.push(btn.dataset.filter);
        });
        if(activeQuickFilters.length > 0) {
            formData.append('quick_filters', activeQuickFilters.join(','));
        }

        const params = new URLSearchParams(formData);
        const url = '/products/filter?' + params.toString();

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            // Add a minimum loading time for better UX (optional)
            setTimeout(() => {
                if(data.html) {
                    // Hide loading overlay and remove body blur
                    if (loadingOverlay) {
                        loadingOverlay.style.display = 'none';
                        document.body.classList.remove('filter-loading');
                    }
                    
                    // Update grid with fade-in animation
                    grid.innerHTML = data.html;
                    grid.classList.add('filter-results');
                    
                    // Remove animation class after animation completes
                    setTimeout(() => {
                        grid.classList.remove('filter-results');
                    }, 600);
                    
                    page = 1; // Reset page counter
                    
                    // Show/hide load more button based on availability
                    if(data.has_more) {
                        loadMoreBtn.style.display = 'block';
                    } else {
                        loadMoreBtn.style.display = 'none';
                    }

                    // Log results for debugging
                    console.log(`Filters applied: ${data.total} products found`);
                }
            }, 300); // Minimum 300ms loading time for smooth experience
        })
        .catch(error => {
            console.error('Filter error:', error);
            
            // Hide loading overlay and remove body blur
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
                document.body.classList.remove('filter-loading');
            }
            
            // Show error message in grid
            grid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="text-danger">
                        <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>
                        <h5>Error Loading Products</h5>
                        <p>There was an issue applying the filters. Please try again.</p>
                        <button class="btn btn-outline-primary mt-3" onclick="location.reload()">Refresh Page</button>
                    </div>
                </div>
            `;
            
            if (typeof toastr !== 'undefined') {
                toastr.error('Failed to apply filters');
            }
        });
    }

    // Load more functionality (updated to work with filters)
    if(loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function(){
            if(isLoadingMore) return;
            
            isLoadingMore = true;
            page++;
            loadMoreBtn.disabled = true;
            loadMoreBtn.innerText = 'Loading...';
            
            // Get current filter parameters
            const formData = new FormData(filterForm);
            if(searchBox.value.trim()) {
                formData.append('q', searchBox.value.trim());
            }
            if(sortSelect.value) {
                formData.append('sort', sortSelect.value);
            }
            formData.append('page', page);
            
            const params = new URLSearchParams(formData);
            const url = '/products/filter?' + params.toString();
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.html && data.html.trim().length > 0) {
                    const temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    Array.from(temp.children).forEach(c => grid.appendChild(c));
                    
                    if(!data.has_more) {
                        loadMoreBtn.innerText = 'No more products';
                        loadMoreBtn.disabled = true;
                    } else {
                        loadMoreBtn.disabled = false;
                        loadMoreBtn.innerText = 'Load more';
                    }
                } else {
                    loadMoreBtn.innerText = 'No more products';
                    loadMoreBtn.disabled = true;
                }
            })
            .catch(err => {
                console.error('Load more error:', err);
                loadMoreBtn.disabled = false;
                loadMoreBtn.innerText = 'Load more';
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to load more products');
                }
            })
            .finally(() => {
                isLoadingMore = false;
            });
        });
    }
});
</script>@endsection