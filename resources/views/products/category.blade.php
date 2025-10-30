@extends('layouts.frontend')

@section('title', $category->name . ' - ' . config('app.name'))

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
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-white-50">Products</a></li>
                        @php
                            $breadcrumbPath = $category->getBreadcrumbPath();
                        @endphp
                        @foreach($breadcrumbPath as $index => $breadcrumbCategory)
                            @if($loop->last)
                                <li class="breadcrumb-item active text-white">{{ $breadcrumbCategory->name }}</li>
                            @else
                                <li class="breadcrumb-item">
                                    <a href="{{ route('category.products', $breadcrumbCategory->slug) }}" class="text-white-50">
                                        {{ $breadcrumbCategory->name }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
                <h1 class="display-5 fw-bold mb-3">
                    @if($category->parent)
                        <span class="text-white-75 fs-4">{{ $category->parent->name }}</span>
                        <i class="bi bi-chevron-right text-white-50 mx-2"></i>
                    @endif
                    {{ $category->name }}
                </h1>
                <p class="lead mb-0 text-white-75">
                    @if($category->description)
                        {{ $category->description }}
                    @else
                        @if($category->parent)
                            Explore our {{ strtolower($category->name) }} in {{ strtolower($category->parent->name) }} collection
                        @else
                            Explore our {{ strtolower($category->name) }} collection
                        @endif
                    @endif
                </p>
            </div>
            <div class="col-md-4 text-end">
                @if($category->image)
                    <img src="{{ $category->imageUrl }}" alt="{{ $category->name }}" class="img-fluid rounded" style="max-height: 80px; opacity: 0.8;">
                @else
                    <i class="bi bi-grid-3x3-gap display-3 text-white-50"></i>
                @endif
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
                                <!-- Mobile Filter Button -->
                                <button class="btn btn-outline-primary d-lg-none" id="openMobileFilter">
                                    <i class="bi bi-funnel me-1"></i>Filters
                                </button>
                                
                                <!-- Results Count -->
                                <div class="text-muted small d-none d-sm-block">
                                    <i class="bi bi-grid-3x3-gap me-1"></i>
                                    <span id="totalProducts">{{ $products->total() }}</span> products found
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="d-flex align-items-center justify-content-lg-end gap-3">
                                <!-- Search Box -->
                                <div class="input-group" style="max-width: 250px;">
                                    <input type="text" class="form-control form-control-sm" id="searchInput" 
                                           placeholder="Search in {{ $category->name }}..." value="{{ request('q') }}">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" id="searchBtn">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                
                                <!-- Sort Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                                            id="sortDropdown" data-bs-toggle="dropdown">
                                        <i class="bi bi-sort-down me-1"></i>Sort
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item sort-option" href="#" data-sort="created_at" data-order="desc">Latest First</a></li>
                                        <li><a class="dropdown-item sort-option" href="#" data-sort="name" data-order="asc">Name A-Z</a></li>
                                        <li><a class="dropdown-item sort-option" href="#" data-sort="price_low">Price: Low to High</a></li>
                                        <li><a class="dropdown-item sort-option" href="#" data-sort="price_high">Price: High to Low</a></li>
                                        <li><a class="dropdown-item sort-option" href="#" data-sort="rating" data-order="desc">Highest Rated</a></li>
                                    </ul>
                                </div>
                                
                                <!-- View Mode -->
                                <div class="btn-group btn-group-sm d-none d-lg-flex" role="group">
                                    <button type="button" class="btn btn-outline-secondary active" id="gridView">
                                        <i class="bi bi-grid-3x3-gap"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="listView">
                                        <i class="bi bi-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div id="productsContainer">
                <div class="row g-3" id="productsGrid">
                    @include('products._category_list', compact('products'))
                </div>
            </div>

            <!-- Load More Button -->
            @if($products->hasMorePages())
            <div class="text-center mt-5 mb-4" id="loadMoreContainer">
                <button class="btn btn-primary btn-lg load-more-btn" id="loadMoreBtn" 
                        data-page="{{ $products->currentPage() + 1 }}">
                    <span class="btn-text">
                        <i class="bi bi-plus-circle me-2"></i>Load More Products
                    </span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>Loading...
                    </span>
                </button>
                <div class="text-muted small mt-2">
                    Showing <span id="currentCount">{{ $products->count() }}</span> of {{ $products->total() }} products
                </div>
            </div>
            @else
            <div class="text-center mt-4 text-muted">
                <i class="bi bi-check-circle me-2"></i>All products loaded
            </div>
            @endif
        </div>
    </div>
</div>

<!-- No Results Section -->
<div id="noResults" class="text-center py-5 d-none">
    <div class="container">
        <i class="bi bi-search display-1 text-muted mb-3"></i>
        <h3 class="text-muted">No products found</h3>
        <p class="text-muted">Try adjusting your filters or search terms</p>
        <button class="btn btn-primary" id="clearAllFilters">
            <i class="bi bi-x-circle me-2"></i>Clear All Filters
        </button>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Mobile Filter Sidebar Styles */
.mobile-filter-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    overflow: hidden;
}

.mobile-filter-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.mobile-filter-content {
    position: absolute;
    top: 0;
    right: 0;
    width: 85%;
    max-width: 400px;
    height: 100%;
    background: white;
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.mobile-filter-sidebar.show .mobile-filter-content {
    transform: translateX(0);
}

.mobile-filter-header {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    flex-shrink: 0;
}

.mobile-filter-body {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}

.mobile-filter-footer {
    padding: 1rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    flex-shrink: 0;
}

/* Ensure mobile filter works properly */
.mobile-filter-body .card {
    border: none !important;
    box-shadow: none !important;
}

.mobile-filter-body .card-header {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
}

/* Search input focus */
#searchInput:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Sort dropdown styles */
.sort-option:hover {
    background-color: #f8f9fa;
}

.sort-option.active {
    background-color: #0d6efd;
    color: white;
}

/* View mode buttons */
.btn-group .btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

/* Sticky filters on desktop */
@media (min-width: 992px) {
    .filters-wrapper {
        position: sticky;
        top: 2rem;
    }
}

/* Mobile optimizations */
@media (max-width: 991px) {
    .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    
    .mobile-filter-sidebar {
        display: block !important;
    }
}

/* Loading states */
.load-more-btn .btn-loading {
    display: none;
}

.load-more-btn.loading .btn-text {
    display: none;
}

.load-more-btn.loading .btn-loading {
    display: inline-block;
}

/* Breadcrumb dark theme */
.breadcrumb-dark .breadcrumb-item + .breadcrumb-item::before {
    color: rgba(255, 255, 255, 0.5);
}

.breadcrumb-dark a:hover {
    color: white !important;
}

/* Products grid responsive */
@media (max-width: 576px) {
    #productsGrid {
        margin: 0 -0.5rem;
    }
    
    #productsGrid > .col-6 {
        padding: 0 0.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Configuration
    const FILTER_URL = '{{ route("category.products", $category->slug) }}';
    const LOAD_MORE_URL = '{{ route("category.products", $category->slug) }}';
    
    let isLoading = false;
    let currentPage = {{ $products->currentPage() }};
    let totalPages = {{ $products->lastPage() }};
    
    // Initialize
    updateActiveSort();
    
    // Mobile filter sidebar
    $('#openMobileFilter').on('click', function() {
        $('#mobileFilterSidebar').show().addClass('show');
        $('body').addClass('overflow-hidden');
    });
    
    $('#closeMobileFilter, #mobileFilterBackdrop').on('click', function() {
        $('#mobileFilterSidebar').removeClass('show');
        setTimeout(() => {
            $('#mobileFilterSidebar').hide();
            $('body').removeClass('overflow-hidden');
        }, 300);
    });
    
    // Apply filters from mobile sidebar
    $('#applyFiltersMobile').on('click', function() {
        applyFilters();
        $('#closeMobileFilter').click();
    });
    
    // Search functionality
    $('#searchBtn').on('click', function() {
        applyFilters();
    });
    
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            applyFilters();
        }
    });
    
    // Sort functionality
    $('.sort-option').on('click', function(e) {
        e.preventDefault();
        const sort = $(this).data('sort');
        const order = $(this).data('order') || 'desc';
        
        // Update active sort
        $('.sort-option').removeClass('active');
        $(this).addClass('active');
        
        applyFilters(sort, order);
    });
    
    // Filter changes
    $(document).on('change', '.filter-checkbox, .filter-radio', function() {
        applyFilters();
    });
    
    // Price range slider (if implemented)
    $(document).on('change', '#priceRange', function() {
        applyFilters();
    });
    
    // Clear filters
    $('#clearFilters, #clearFiltersMobile, #clearAllFilters').on('click', function() {
        clearAllFilters();
    });
    
    // Load more products
    $('#loadMoreBtn').on('click', function() {
        if (isLoading) return;
        
        isLoading = true;
        const $btn = $(this);
        const nextPage = parseInt($btn.data('page'));
        
        $btn.addClass('loading');
        
        const params = getFilterParams();
        params.page = nextPage;
        params.load_more = 1;
        
        $.ajax({
            url: LOAD_MORE_URL,
            method: 'GET',
            data: params,
            success: function(response) {
                if (response.html) {
                    $('#productsGrid').append(response.html);
                    
                    // Update load more button
                    if (response.has_more) {
                        $btn.data('page', nextPage + 1);
                        $('#currentCount').text($('#productsGrid .product-card').length);
                    } else {
                        $('#loadMoreContainer').html('<div class="text-center mt-4 text-muted"><i class="bi bi-check-circle me-2"></i>All products loaded</div>');
                    }
                }
            },
            error: function() {
                toastr.error('Failed to load more products');
            },
            complete: function() {
                isLoading = false;
                $btn.removeClass('loading');
            }
        });
    });
    
    // View mode toggle
    $('#gridView, #listView').on('click', function() {
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
        
        if ($(this).attr('id') === 'listView') {
            $('#productsGrid').removeClass('row g-3').addClass('list-view');
        } else {
            $('#productsGrid').removeClass('list-view').addClass('row g-3');
        }
    });
    
    // Helper functions
    function applyFilters(sort = null, order = null) {
        if (isLoading) return;
        
        isLoading = true;
        showLoading();
        
        const params = getFilterParams();
        if (sort) {
            params.sort = sort;
            if (order) params.order = order;
        }
        
        $.ajax({
            url: FILTER_URL,
            method: 'GET',
            data: params,
            success: function(response) {
                if (response.html) {
                    $('#productsGrid').html(response.html);
                    $('#totalProducts').text(response.total);
                    $('#currentCount').text($('#productsGrid .product-card').length);
                    
                    // Update filters if provided
                    if (response.filters_html) {
                        $('#filtersContainer').html(response.filters_html);
                    }
                    
                    // Update load more button
                    if (response.has_more) {
                        if ($('#loadMoreContainer').length) {
                            $('#loadMoreBtn').data('page', response.current_page + 1).removeClass('loading');
                            $('#loadMoreContainer').show();
                        }
                    } else {
                        $('#loadMoreContainer').hide();
                    }
                    
                    // Show/hide no results
                    if (response.total === 0) {
                        $('#noResults').removeClass('d-none');
                        $('#productsContainer').addClass('d-none');
                    } else {
                        $('#noResults').addClass('d-none');
                        $('#productsContainer').removeClass('d-none');
                    }
                    
                    // Update URL without refresh
                    const url = new URL(window.location);
                    Object.keys(params).forEach(key => {
                        if (params[key]) {
                            url.searchParams.set(key, params[key]);
                        } else {
                            url.searchParams.delete(key);
                        }
                    });
                    window.history.pushState({}, '', url);
                }
            },
            error: function() {
                toastr.error('Failed to filter products');
            },
            complete: function() {
                isLoading = false;
                hideLoading();
            }
        });
    }
    
    function getFilterParams() {
        const params = {};
        
        // Search
        const search = $('#searchInput').val().trim();
        if (search) params.q = search;
        
        // Categories (brands only, since we're already filtering by category)
        const brands = [];
        $('.filter-checkbox[name="brands[]"]:checked').each(function() {
            brands.push($(this).val());
        });
        if (brands.length) params.brands = brands;
        
        // Price range
        const minPrice = $('#minPrice').val();
        const maxPrice = $('#maxPrice').val();
        if (minPrice) params.min_price = minPrice;
        if (maxPrice) params.max_price = maxPrice;
        
        // In stock
        if ($('#inStock').is(':checked')) {
            params.in_stock = 1;
        }
        
        // Rating
        const rating = $('input[name="rating"]:checked').val();
        if (rating) params.rating = rating;
        
        // Sort
        const activeSort = $('.sort-option.active');
        if (activeSort.length) {
            params.sort = activeSort.data('sort');
            const order = activeSort.data('order');
            if (order) params.order = order;
        }
        
        return params;
    }
    
    function clearAllFilters() {
        $('#searchInput').val('');
        $('.filter-checkbox').prop('checked', false);
        $('.filter-radio').prop('checked', false);
        $('#minPrice, #maxPrice').val('');
        $('.sort-option').removeClass('active');
        $('.sort-option[data-sort="created_at"]').addClass('active');
        
        applyFilters();
    }
    
    function updateActiveSort() {
        const urlParams = new URLSearchParams(window.location.search);
        const sort = urlParams.get('sort') || 'created_at';
        const order = urlParams.get('order') || 'desc';
        
        $('.sort-option').removeClass('active');
        $(`.sort-option[data-sort="${sort}"]`).addClass('active');
    }
    
    function showLoading() {
        $('#productsContainer').append('<div class="loading-overlay"><div class="spinner-border text-primary"></div></div>');
    }
    
    function hideLoading() {
        $('.loading-overlay').remove();
    }
});
</script>
@endpush