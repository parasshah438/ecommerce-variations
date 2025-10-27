@extends('layouts.frontend')

@section('title', 'New Arrivals - ' . config('app.name'))

@section('content')
<!-- Mobile Filter Sidebar (Completely isolated - only visible on mobile) -->
<div class="mobile-filter-sidebar d-lg-none" id="mobileFilterSidebar" style="display: none;">
    <!-- Backdrop -->
    <div class="mobile-filter-backdrop" id="mobileFilterBackdrop"></div>
    
    <!-- Sidebar Content -->
    <div class="mobile-filter-content">
        <!-- Sidebar Header -->
        <div class="mobile-filter-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    Filter Products
                </h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="closeMobileFilter">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Sidebar Body -->
        <div class="mobile-filter-body">
            @include('products._filters', compact('categories', 'brands', 'priceRange'))
        </div>
        
        <!-- Sidebar Footer -->
        <div class="mobile-filter-footer">
            <div class="row g-2">
                <div class="col-6">
                    <button class="btn btn-outline-secondary w-100" id="clearFiltersMobile">
                        <i class="bi bi-x-circle me-1"></i>Clear All
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-primary w-100" id="applyFiltersMobile">
                        <i class="bi bi-check-lg me-1"></i>Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header Section -->
<div class="bg-primary text-white py-5 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-dark mb-3">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white">New Arrivals</li>
                    </ol>
                </nav>
                <h1 class="display-5 fw-bold mb-3">New Arrivals</h1>
                <p class="lead mb-0 text-white-75">Discover our latest products added in the last 30 days</p>
            </div>
            <div class="col-md-4 text-end">
                <i class="bi bi-stars display-3 text-white-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="row g-4 align-items-start">
        <!-- Filters Sidebar -->
        <div class="col-xl-3 col-lg-4 d-none d-lg-block">
            <div class="filters-wrapper">
                <!-- Filters Container -->
                <div class="card shadow-sm border-0 sticky-top" style="top: 2rem;">
                    <div class="card-header bg-light border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-funnel me-2 text-primary"></i>Filter Products
                            </h6>
                            <button class="btn btn-sm btn-outline-secondary" id="clearFilters">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0" id="filtersContainer">
                        @include('products._filters', compact('categories', 'brands', 'priceRange'))
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="col-xl-9 col-lg-8">
            <!-- Products Toolbar -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-md-12 mb-2 mb-lg-0">
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <!-- Mobile Filter Toggle -->
                                <div class="d-lg-none">
                                    <button class="btn btn-outline-primary btn-sm" type="button" id="mobileFilterToggle">
                                        <i class="bi bi-funnel me-1"></i>Filters
                                        <span class="badge bg-primary ms-1" id="activeFiltersCount" style="display: none;">0</span>
                                    </button>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary px-3 py-2 fs-6">
                                        <i class="bi bi-grid me-1"></i>
                                        <span id="totalProducts">{{ $products->total() }}</span> Products
                                    </span>
                                </div>
                                <div class="btn-group" role="group" id="viewToggle">
                                    <input type="radio" class="btn-check" name="viewType" id="gridView" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary btn-sm" for="gridView" data-view="grid" title="Grid View">
                                        <i class="bi bi-grid-3x3-gap"></i>
                                    </label>
                                    <input type="radio" class="btn-check" name="viewType" id="listView" autocomplete="off">
                                    <label class="btn btn-outline-primary btn-sm" for="listView" data-view="list" title="List View">
                                        <i class="bi bi-view-list"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="d-flex align-items-center justify-content-lg-end gap-2">
                                <label class="form-label me-1 mb-0 small text-muted fw-bold">Sort:</label>
                                <select class="form-select form-select-sm" id="sortBy" style="min-width: 160px;">
                                    <option value="created_at">Newest First</option>
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                    <option value="name">Name A-Z</option>
                                    <option value="rating">Highest Rated</option>
                                </select>
                                <select class="form-select form-select-sm" id="perPage" style="min-width: 80px;">
                                    <option value="12">12</option>
                                    <option value="24">24</option>
                                    <option value="48">48</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Container -->
            <div class="products-wrapper min-height-600">
                <!-- Product Loading Skeleton (shown initially) -->
                <div class="row g-4" id="productsSkeletonGrid">
                    @for($i = 0; $i < 6; $i++)
                    <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="skeleton-box" style="height: 250px;"></div>
                            <div class="card-body">
                                <div class="skeleton-line mb-2" style="width: 60%;"></div>
                                <div class="skeleton-line mb-2" style="width: 100%;"></div>
                                <div class="skeleton-line mb-3" style="width: 80%;"></div>
                                <div class="skeleton-line mb-2" style="width: 40%;"></div>
                                <div class="skeleton-line" style="width: 30%;"></div>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>

                <!-- Actual Products Grid (initially hidden) -->
                <div class="row g-4 d-none" id="productsGrid">
                    @include('products._new_arrivals_list', compact('products'))
                </div>

                <!-- Load More Button -->
                <div class="text-center mt-5" id="loadMoreContainer" style="display: none;">
                    <button class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-sm" id="loadMoreBtn">
                        <span class="btn-text">
                            <i class="bi bi-arrow-down-circle me-2"></i>Load More Products
                        </span>
                        <span class="btn-loading d-none">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            Loading...
                        </span>
                    </button>
                </div>

                <!-- No Products Message -->
                <div class="text-center py-5 d-none" id="noProducts">
                    <div class="mb-4">
                        <i class="bi bi-search display-1 text-muted opacity-50"></i>
                    </div>
                    <h4 class="text-muted mb-3">No products found</h4>
                    <p class="text-muted mb-4">We couldn't find any products matching your criteria</p>
                    <button class="btn btn-primary rounded-pill px-4" id="resetFilters">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none align-items-center justify-content-center" style="z-index: 9999;" id="loadingOverlay">
    <div class="text-center">
        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
        <h6 class="text-muted">Loading products...</h6>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Product Cards */
.product-card {
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    border-radius: 0.5rem;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
}

.product-image {
    transition: transform 0.5s ease;
    border-radius: 0.5rem 0.5rem 0 0;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.product-actions-overlay {
    transition: all 0.3s ease;
}

.product-card:hover .product-actions-overlay {
    opacity: 1 !important;
}

/* Quick action buttons */
.product-actions-overlay .btn {
    width: 36px;
    height: 36px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.product-actions-overlay .btn:hover {
    transform: scale(1.1);
}

/* Utility classes */
.letter-spacing-1 {
    letter-spacing: 0.5px;
}

/* Filter styles */
.form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.form-check-label {
    cursor: pointer;
}

.form-check:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
    border-radius: 0.25rem;
    padding: 0.25rem;
    margin: -0.25rem;
}

/* Loading animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.product-card.animate {
    animation: fadeInUp 0.6s ease forwards;
}

/* List view styles */
.list-view .row > div {
    margin-bottom: 1rem;
}

.list-view .product-card {
    flex-direction: row;
    max-height: 200px;
}

.list-view .product-card img {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 0.5rem 0 0 0.5rem;
}

.list-view .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-card {
        margin-bottom: 1.5rem;
    }
    
    .list-view .product-card {
        flex-direction: column;
        max-height: none;
    }
    
    .list-view .product-card img {
        width: 100%;
        height: 200px;
        border-radius: 0.5rem 0.5rem 0 0;
    }
}

/* Ensure minimum height to prevent layout shift */
.min-height-600 {
    min-height: 600px;
}

.filters-wrapper {
    min-height: 500px;
}

/* Skeleton loading animations */
.skeleton-box, .skeleton-line {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 4px;
}

.skeleton-box {
    border-radius: 8px 8px 0 0;
}

.skeleton-line {
    height: 16px;
    margin-bottom: 8px;
}

@keyframes skeleton-loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

/* Rating filter styles */
.rating-filter-container .form-check {
    transition: all 0.2s ease;
    border-radius: 0.375rem;
    margin: 0 -0.5rem;
    padding: 0.5rem;
}

.rating-filter-container .form-check:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.rating-filter-container .form-check.bg-light {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.rating-filter-container label {
    cursor: pointer;
    width: 100%;
    margin-bottom: 0;
}

.rating-filter-container .text-warning i {
    font-size: 0.9rem;
}

/* Custom scrollbar for filter sections */
div[style*="max-height"]::-webkit-scrollbar {
    width: 4px;
}

div[style*="max-height"]::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

div[style*="max-height"]::-webkit-scrollbar-thumb {
    background: var(--bs-primary);
    border-radius: 2px;
}

div[style*="max-height"]::-webkit-scrollbar-thumb:hover {
    background: var(--bs-primary);
    opacity: 0.8;
}

/* Layout stability */
.row.align-items-start {
    align-items: flex-start !important;
}

/* Better responsive behavior */
@media (max-width: 991px) {
    .filters-wrapper {
        min-height: auto;
    }
}

/* Active filter indicator */
#activeFiltersCount {
    font-size: 0.75rem;
    min-width: 20px;
    height: 20px;
    line-height: 1;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
}

/* Newly loaded products highlight */
.product-card.newly-loaded {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 110, 253, 0.1) 100%);
    border: 2px solid rgba(13, 110, 253, 0.3) !important;
    box-shadow: 0 4px 20px rgba(13, 110, 253, 0.15) !important;
    transform: scale(1.02);
    transition: all 0.6s ease;
}

.product-card.newly-loaded::after {
    content: "New";
    position: absolute;
    top: 10px;
    right: 10px;
    background: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    z-index: 2;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

/* Mobile Filter Sidebar */
.mobile-filter-sidebar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 99999 !important;
    visibility: hidden;
    opacity: 0;
    transition: all 0.3s ease;
    display: none !important;
    pointer-events: none;
}

.mobile-filter-sidebar.active {
    visibility: visible !important;
    opacity: 1 !important;
    display: block !important;
    pointer-events: auto !important;
}

.mobile-filter-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(2px);
    cursor: pointer;
}

.mobile-filter-content {
    position: absolute;
    top: 0;
    left: 0;
    width: 320px;
    max-width: 85vw;
    height: 100%;
    background: white;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
    transform: translateX(-100%);
    transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.mobile-filter-sidebar.active .mobile-filter-content {
    transform: translateX(0);
}

.mobile-filter-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    flex-shrink: 0;
}

.mobile-filter-body {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}

.mobile-filter-body .card {
    border: none;
    border-radius: 0;
    box-shadow: none;
}

.mobile-filter-body .card-header {
    background: white;
    border-bottom: 1px solid #e9ecef;
    padding: 0.75rem 1.25rem;
}

.mobile-filter-body .card-body {
    padding: 1rem 1.25rem;
}

.mobile-filter-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    flex-shrink: 0;
}

/* Custom scrollbar for mobile sidebar */
.mobile-filter-body::-webkit-scrollbar {
    width: 6px;
}

.mobile-filter-body::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.mobile-filter-body::-webkit-scrollbar-thumb {
    background: var(--bs-primary);
    border-radius: 3px;
}

.mobile-filter-body::-webkit-scrollbar-thumb:hover {
    background: rgba(var(--bs-primary-rgb), 0.8);
}

/* Prevent body scroll when sidebar is open */
body.mobile-sidebar-open {
    overflow: hidden;
}

/* Animation for filter button */
#mobileFilterToggle {
    transition: all 0.2s ease;
}

#mobileFilterToggle:active {
    transform: scale(0.95);
}

/* Better mobile filter styling */
@media (max-width: 576px) {
    .mobile-filter-content {
        width: 280px;
        max-width: 90vw;
    }
}

/* Ensure desktop filters are completely hidden on mobile */
@media (max-width: 991px) {
    .filters-wrapper,
    .filters-sidebar,
    .d-lg-block {
        display: none !important;
    }
    
    /* Hide any remaining filter elements that might appear in content */
    .collapse:not(.mobile-filter-sidebar *),
    .filter-container:not(.mobile-filter-sidebar *),
    .offcanvas:not(.mobile-filter-sidebar *) {
        display: none !important;
    }
    
    /* Ensure mobile sidebar is the only filter mechanism on mobile */
    .mobile-filter-sidebar {
        display: none !important; /* Hidden by default */
    }
    
    .mobile-filter-sidebar.active {
        display: block !important; /* Only show when active */
    }
}
</style>
@endpush

@section('scripts')
<script>
$(document).ready(function() {
    let currentPage = 1;
    let isLoading = false;
    let hasMorePages = {{ $products->hasMorePages() ? 'true' : 'false' }};
    let activeFiltersCount = 0;
    
    // Show products immediately and hide skeleton
    setTimeout(function() {
        $('#productsSkeletonGrid').fadeOut(300, function() {
            $('#productsGrid').removeClass('d-none').hide().fadeIn(300);
            if (hasMorePages) {
                $('#loadMoreContainer').show();
            }
        });
    }, 500);
    
    // Initialize price range slider
    initializePriceSlider();
    
    // Mobile Filter Sidebar Controls
    initializeMobileFilterSidebar();
    
    // Filter change handlers with proper delegation
    $(document).on('change', '.category-filter, .brand-filter, .stock-filter', function() {
        console.log('Filter changed:', $(this).attr('class'), $(this).val(), $(this).is(':checked'));
        applyFilters();
    });

    // Special handling for rating filter (radio buttons)
    $(document).on('change', '.rating-filter', function() {
        const selectedRating = $(this).val();
        console.log('Rating filter changed:', selectedRating, 'checked:', $(this).is(':checked'));
        
        // Highlight selected rating
        $('.rating-filter-container .form-check').removeClass('bg-light');
        if (selectedRating) {
            $(this).closest('.form-check').addClass('bg-light rounded p-2');
        }
        
        applyFilters();
    });
    
    // Price range filters with debounce
    let priceTimeout;
    $(document).on('input', '.price-filter', function() {
        clearTimeout(priceTimeout);
        priceTimeout = setTimeout(() => {
            console.log('Price filter changed:', $('#minPrice').val(), $('#maxPrice').val());
            applyFilters();
        }, 800);
    });
    
    // Sort and pagination
    $('#sortBy, #perPage').on('change', function() {
        console.log('Sort/pagination changed:', $(this).attr('id'), $(this).val());
        applyFilters();
    });
    
    // Search input with debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        searchTimeout = setTimeout(() => {
            console.log('Search changed:', query);
            applyFilters();
        }, 600);
    });
    
    // View toggle
    $('label[data-view]').on('click', function() {
        const view = $(this).data('view');
        
        if (view === 'list') {
            $('#productsGrid').addClass('list-view');
        } else {
            $('#productsGrid').removeClass('list-view');
        }
    });
    
    // Load more functionality
    $('#loadMoreBtn').on('click', function() {
        if (isLoading || !hasMorePages) return;
        
        loadMoreProducts();
    });
    
    // Clear filters
    $('#clearFilters, #resetFilters').on('click', function() {
        clearAllFilters();
    });
    
    function initializePriceSlider() {
        // This would initialize a price range slider library like noUiSlider or similar
        // For now, we'll use simple number inputs
        $('#minPrice, #maxPrice').on('input', function() {
            const minPrice = $('#minPrice').val();
            const maxPrice = $('#maxPrice').val();
            
            if (minPrice && maxPrice && parseInt(minPrice) > parseInt(maxPrice)) {
                $(this).val('');
            }
        });
    }
    
    function applyFilters(resetPagination = true) {
        if (isLoading) return;
        
        isLoading = true;
        showLoadingOverlay();
        
        if (resetPagination) {
            currentPage = 1;
        }
        
        const formData = collectFilterData();
        
        // Update active filters count
        updateActiveFiltersCount(formData);
        
        $.ajax({
            url: '{{ route("products.new_arrivals.filter") }}',
            method: 'GET',
            data: formData,
            success: function(response) {
                if (resetPagination) {
                    $('#productsGrid').html(response.html);
                } else {
                    $('#productsGrid').append(response.html);
                }
                
                // Update UI elements
                $('#totalProducts').text(response.total);
                hasMorePages = response.has_more;
                
                if (hasMorePages) {
                    $('#loadMoreContainer').show();
                } else {
                    $('#loadMoreContainer').hide();
                }
                
                // Show no products message if needed
                if (response.total === 0) {
                    $('#noProducts').removeClass('d-none');
                    $('#productsGrid').addClass('d-none');
                } else {
                    $('#noProducts').addClass('d-none');
                    $('#productsGrid').removeClass('d-none');
                }
                
                // Animate new products
                animateNewProducts();
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                let errorMessage = 'Failed to load products';
                if (xhr.status === 404) {
                    errorMessage = 'Products not found';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error occurred';
                } else if (xhr.status === 0) {
                    errorMessage = 'Network connection error';
                }
                
                showToast(errorMessage, 'danger');
                
                // Show error state in products area
                $('#productsGrid').html(`
                    <div class="col-12">
                        <div class="alert alert-danger text-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Error loading products</strong>
                            <p class="mb-2">${errorMessage}</p>
                            <button class="btn btn-sm btn-outline-danger" onclick="applyFilters()">Try Again</button>
                        </div>
                    </div>
                `);
            },
            complete: function() {
                isLoading = false;
                hideLoadingOverlay();
            }
        });
    }
    
    function loadMoreProducts() {
        currentPage++;
        
        const $btn = $('#loadMoreBtn');
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        $btn.prop('disabled', true);
        isLoading = true;
        
        // Get current products count to identify new ones
        const currentProductsCount = $('#productsGrid .product-card').length;
        
        const formData = collectFilterData();
        formData.page = currentPage;
        formData.load_more = true;
        
        $.ajax({
            url: '{{ route("products.new_arrivals.filter") }}',
            method: 'GET',
            data: formData,
            success: function(response) {
                $('#productsGrid').append(response.html);
                hasMorePages = response.has_more;
                
                if (!hasMorePages) {
                    $('#loadMoreContainer').hide();
                }
                
                animateNewProducts();
                
                // Smooth scroll to the first newly loaded product
                setTimeout(() => {
                    const $newProducts = $('#productsGrid .product-card').slice(currentProductsCount);
                    
                    if ($newProducts.length > 0) {
                        const firstNewProduct = $newProducts.first();
                        
                        // Smooth scroll to show the newly loaded products
                        $('html, body').animate({
                            scrollTop: firstNewProduct.offset().top - 120 // 120px offset from top for navbar
                        }, 600, 'swing');
                        
                        // Add a subtle highlight effect to new products
                        $newProducts.addClass('newly-loaded');
                        setTimeout(() => {
                            $newProducts.removeClass('newly-loaded');
                        }, 2000);
                    }
                }, 200); // Reduced delay for better UX
            },
            error: function() {
                currentPage--; // Revert page increment
                showToast('Failed to load more products', 'danger');
            },
            complete: function() {
                $btn.find('.btn-text').removeClass('d-none');
                $btn.find('.btn-loading').addClass('d-none');
                $btn.prop('disabled', false);
                isLoading = false;
            }
        });
    }
    
    function collectFilterData() {
        const formData = {};
        
        // Search
        const search = $('#searchInput').val().trim();
        if (search) {
            formData.q = search;
            console.log('Added search:', search);
        }
        
        // Categories
        const categories = [];
        $('.category-filter:checked').each(function() {
            categories.push(parseInt($(this).val()));
        });
        if (categories.length) {
            formData.categories = categories;
            console.log('Added categories:', categories);
        }
        
        // Brands
        const brands = [];
        $('.brand-filter:checked').each(function() {
            brands.push(parseInt($(this).val()));
        });
        if (brands.length) {
            formData.brands = brands;
            console.log('Added brands:', brands);
        }
        
        // Price range
        const minPrice = $('#minPrice').val();
        const maxPrice = $('#maxPrice').val();
        if (minPrice && minPrice > 0) {
            formData.min_price = parseInt(minPrice);
            console.log('Added min price:', minPrice);
        }
        if (maxPrice && maxPrice > 0) {
            formData.max_price = parseInt(maxPrice);
            console.log('Added max price:', maxPrice);
        }
        
        // Rating
        const ratingElement = $('.rating-filter:checked[name="ratingFilter"]');
        if (ratingElement.length > 0) {
            const rating = ratingElement.val();
            if (rating && rating !== '') {
                formData.rating = parseFloat(rating);
                console.log('Added rating filter:', rating);
            }
        }
        
        // In stock
        if ($('#inStockOnly').is(':checked')) {
            formData.in_stock = 1;
            console.log('Added in stock filter');
        }
        
        // Sort and pagination
        formData.sort = $('#sortBy').val() || 'created_at';
        formData.per_page = $('#perPage').val() || '12';
        
        console.log('Final filter data:', formData);
        return formData;
    }
    
    function updateActiveFiltersCount(formData) {
        let count = 0;
        
        // If no formData provided, collect current filter data
        if (!formData) {
            formData = collectFilterData();
        }
        
        // Count active filters
        if (formData.q) count++;
        if (formData.categories && formData.categories.length) count += formData.categories.length;
        if (formData.brands && formData.brands.length) count += formData.brands.length;
        if (formData.min_price || formData.max_price) count++;
        if (formData.rating && formData.rating > 0) count++;
        if (formData.in_stock) count++;
        
        activeFiltersCount = count;
        
        // Update both desktop and mobile counters
        const $counter = $('#activeFiltersCount');
        if (count > 0) {
            $counter.text(count).show();
        } else {
            $counter.hide();
        }
    }

    function clearAllFilters() {
        console.log('Clearing all filters...');
        
        // Clear all form inputs
        $('.category-filter, .brand-filter').prop('checked', false);
        
        // Reset rating filter to "All Ratings"
        $('#ratingAll').prop('checked', true);
        $('.rating-filter[name="ratingFilter"]').prop('checked', false);
        $('.rating-filter-container .form-check').removeClass('bg-light');
        $('#searchInput').val('');
        $('#minPrice, #maxPrice').val('');
        $('#inStockOnly').prop('checked', false);
        $('#sortBy').val('created_at');
        $('#perPage').val('12');
        $('.quick-filter').removeClass('active');
        
        // Reset active filters count
        activeFiltersCount = 0;
        $('#activeFiltersCount').hide();
        
        // Reset and apply filters
        applyFilters();
    }

    // Helper function to clear price range
    window.clearPriceRange = function() {
        $('#minPrice, #maxPrice').val('');
        applyFilters();
    };

    // Debug function to check current filter state
    function debugFilters() {
        console.log('=== CURRENT FILTER STATE ===');
        console.log('Categories:', $('.category-filter:checked').map(function() { return $(this).val(); }).get());
        console.log('Brands:', $('.brand-filter:checked').map(function() { return $(this).val(); }).get());
        console.log('Price:', $('#minPrice').val(), '-', $('#maxPrice').val());
        
        const selectedRating = $('.rating-filter:checked[name="ratingFilter"]');
        console.log('Rating element found:', selectedRating.length);
        console.log('Rating value:', selectedRating.length > 0 ? selectedRating.val() : 'none');
        console.log('All rating filters:', $('.rating-filter').map(function() { 
            return $(this).attr('id') + ':' + $(this).is(':checked'); 
        }).get());
        
        console.log('In Stock:', $('#inStockOnly').is(':checked'));
        console.log('Search:', $('#searchInput').val());
        console.log('==============================');
    }

    // Add visual feedback for rating selection
    function updateRatingVisualFeedback() {
        $('.rating-filter-container .form-check').removeClass('bg-light border border-primary');
        
        const checkedRating = $('.rating-filter:checked[name="ratingFilter"]');
        if (checkedRating.length > 0) {
            checkedRating.closest('.form-check').addClass('bg-light border border-primary rounded p-2');
        }
    }

    // Call visual feedback update after DOM ready
    setTimeout(function() {
        updateRatingVisualFeedback();
    }, 100);

    // Add debug button (remove in production)
    @if(config('app.debug'))
    $('<button class="btn btn-sm btn-info mt-2" onclick="debugFilters()">Debug Filters</button>').appendTo('.card-header');
    @endif
    
    function animateNewProducts() {
        // Add animation class to newly loaded products
        $('#productsGrid .product-card:not(.animated)').each(function(index) {
            const $card = $(this);
            $card.addClass('animated');
            
            setTimeout(() => {
                $card.addClass('new-product');
            }, index * 100);
        });
    }
    
    function showLoadingOverlay() {
        $('#loadingOverlay').removeClass('d-none');
    }
    
    function hideLoadingOverlay() {
        $('#loadingOverlay').addClass('d-none');
    }
    
    function showToast(message, type = 'info') {
        // Reuse the toast function from the main page
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        }
    }
    
    // Mobile Filter Sidebar Functions
    function initializeMobileFilterSidebar() {
        const $sidebar = $('#mobileFilterSidebar');
        const $backdrop = $('#mobileFilterBackdrop');
        const $toggleBtn = $('#mobileFilterToggle');
        const $closeBtn = $('#closeMobileFilter');
        const $applyBtn = $('#applyFiltersMobile');
        const $clearBtn = $('#clearFiltersMobile');
        
        console.log('Initializing mobile filter sidebar...', {
            sidebar: $sidebar.length,
            toggleBtn: $toggleBtn.length,
            closeBtn: $closeBtn.length
        });
        
        // Open sidebar
        $toggleBtn.on('click', function() {
            console.log('Mobile filter toggle clicked');
            openMobileFilterSidebar();
        });
        
        // Close sidebar
        $closeBtn.on('click', function() {
            closeMobileFilterSidebar();
        });
        
        // Close on backdrop click
        $backdrop.on('click', function() {
            closeMobileFilterSidebar();
        });
        
        // Apply filters and close
        $applyBtn.on('click', function() {
            applyFilters();
            closeMobileFilterSidebar();
        });
        
        // Clear filters
        $clearBtn.on('click', function() {
            clearAllFilters();
            closeMobileFilterSidebar();
        });
        
        // Close on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $sidebar.hasClass('active')) {
                closeMobileFilterSidebar();
            }
        });
        
        // Prevent content scroll when sidebar is open
        function preventBodyScroll(prevent) {
            if (prevent) {
                $('body').addClass('mobile-sidebar-open');
            } else {
                $('body').removeClass('mobile-sidebar-open');
            }
        }
        
        // Open sidebar function
        window.openMobileFilterSidebar = function() {
            console.log('Opening mobile filter sidebar');
            $sidebar.show(); // Ensure it's displayed
            setTimeout(() => {
                $sidebar.addClass('active');
                console.log('Mobile filter sidebar activated');
            }, 10); // Small delay to ensure display:block is applied first
            preventBodyScroll(true);
            
            // Sync filter count
            updateActiveFiltersCount();
        };
        
        // Close sidebar function
        window.closeMobileFilterSidebar = function() {
            console.log('Closing mobile filter sidebar');
            $sidebar.removeClass('active');
            preventBodyScroll(false);
            
            // Hide after animation completes
            setTimeout(() => {
                $sidebar.hide();
                console.log('Mobile filter sidebar hidden');
            }, 400);
        };
    }
    
    // Quick filter buttons
    $('.quick-filter').on('click', function() {
        $(this).toggleClass('active');
        applyFilters();
    });
    
    // Initialize animations for initial products
    setTimeout(function() {
        animateNewProducts();
    }, 800);
});
</script>
@endsection