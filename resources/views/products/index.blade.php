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

/* Color swatch styling */
.color-swatch {
    transition: all 0.2s ease;
}

.color-swatch:hover {
    transform: scale(1.2);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

/* Size and color filter styling */
.size-filter:checked + label,
.color-filter:checked + label {
    background-color: rgba(0, 123, 255, 0.1);
    font-weight: 600;
    border-radius: 8px;
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

/* Active Filter Tags Styling */
.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #e3f2fd;
    color: #1976d2;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    border: 1px solid #bbdefb;
    transition: all 0.2s ease;
}

.filter-tag:hover {
    background: #bbdefb;
    border-color: #90caf9;
}

.filter-tag .remove-filter {
    background: none;
    border: none;
    color: #1976d2;
    font-size: 14px;
    font-weight: bold;
    line-height: 1;
    padding: 2px;
    margin: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    transition: all 0.2s ease;
    margin-left: 4px;
}

.filter-tag .remove-filter:hover {
    background: #1976d2;
    color: white;
    transform: scale(1.1);
}

#activeFiltersSection {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
}

#clearAllFilters {
    border-radius: 20px;
    font-size: 0.8rem;
    padding: 4px 12px;
}

/* Mobile Filter Panel Styles */
.mobile-filter-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    backdrop-filter: blur(2px);
}

.mobile-filter-panel {
    position: fixed;
    top: 0;
    left: -100%;
    width: 90%;
    max-width: 400px;
    height: 100%;
    background: white;
    z-index: 1050;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
    transition: left 0.3s ease-in-out;
    display: flex;
    flex-direction: column;
}

.mobile-filter-panel.show {
    left: 0;
}

.mobile-filter-header {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
}

.mobile-filter-content {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.mobile-filter-footer {
    padding: 20px;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 10px;
    background: #f8f9fa;
}

.mobile-filter-footer .btn {
    flex: 1;
    border-radius: 8px;
    font-weight: 600;
}

/* Mobile Filter Button */
#mobileFilterBtn {
    font-weight: 600;
    border-radius: 8px;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 6px;
}

#mobileFilterBtn:hover {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

/* Mobile optimizations */
@media (max-width: 767.98px) {
    .container {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .mobile-filter-panel {
        width: 95%;
    }
    
    /* Adjust product grid for mobile */
    #product-grid .col {
        padding: 0.5rem;
    }
    
    /* Make filter sections more compact on mobile */
    .mobile-filter-content .filter-section {
        margin-bottom: 1.5rem;
    }
    
    .mobile-filter-content .filter-section h6 {
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .mobile-filter-content .form-check {
        margin-bottom: 0.5rem;
    }
}

/* Animation for smooth mobile experience */
.mobile-filter-panel,
.mobile-filter-overlay {
    will-change: transform, opacity;
}

body.mobile-filter-open {
    overflow: hidden;
}
</style>

<div class="container">
    <!-- Mobile-First Layout -->
    <div class="row">
        <!-- Desktop Sidebar (Hidden on Mobile) -->
        <div class="col-lg-3 col-md-4 d-none d-md-block mb-4">
            <!-- Advanced Filters Sidebar -->
            @include('products._product_filter')
            <!-- Advanced Filters Sidebar End -->
        </div>        
        
        <!-- Main Content Area -->
        <div class="col-lg-9 col-md-8 col-12">
            <!-- Mobile Header with Filter Button -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 mb-0">All Products</h2>
                
                <!-- Mobile/Desktop Controls -->
                <div class="d-flex gap-2 align-items-center">
                    <!-- Mobile Filter Button (Only visible on mobile) -->
                    <button class="btn btn-outline-primary d-md-none" type="button" id="mobileFilterBtn">
                        <i class="bi bi-funnel"></i> Filters
                    </button>
                    
                    <!-- Sort Dropdown -->
                    <select class="form-select form-select-sm" style="width: auto;" id="sortSelect">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Sort by: Featured</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                        <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Sort by: Highest Rated</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Sort by: Newest</option>
                        <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>Sort by: Featured</option>
                        <option value="in_stock" {{ request('sort') == 'in_stock' ? 'selected' : '' }}>Sort by: In Stock</option>
                        <option value="best_selling" {{ request('sort') == 'best_selling' ? 'selected' : '' }}>Sort by: Best Selling</option>
                        <option value="brand" {{ request('sort') == 'brand' ? 'selected' : '' }}>Sort by: Brand</option>
                        <option value="discount" {{ request('sort') == 'discount' ? 'selected' : '' }}>Sort by: Discount</option>
                    </select>
                </div>
            </div>

            <!-- Active Filters Section -->
            <div id="activeFiltersSection" class="mb-3" style="display: none;">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="fw-semibold text-muted me-2">Active Filters:</span>
                    <div id="activeFilterTags" class="d-flex flex-wrap gap-2">
                        <!-- Filter tags will be dynamically added here -->
                    </div>
                    <button type="button" id="clearAllFilters" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="bi bi-x-circle"></i> Clear All
                    </button>
                </div>
            </div>

            <!-- Mobile Filter Panel (Hidden by default) -->
            <div id="mobileFilterPanel" class="d-md-none mobile-filter-panel" style="display: none;">
                <div class="mobile-filter-header">
                    <h5 class="mb-0">Filters</h5>
                    <button type="button" class="btn-close" id="closeMobileFilter" aria-label="Close"></button>
                </div>
                <div class="mobile-filter-content">
                    @include('products._product_filter')
                </div>
                <div class="mobile-filter-footer">
                    <button type="button" class="btn btn-outline-secondary" id="clearMobileFilters">Clear All</button>
                    <button type="button" class="btn btn-primary" id="applyMobileFilters">Apply Filters</button>
                </div>
            </div>

            <!-- Overlay for mobile filter -->
            <div id="mobileFilterOverlay" class="mobile-filter-overlay d-md-none" style="display: none;"></div>
            
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
    const searchInputs = document.querySelectorAll('input[name="q"]'); // Get all search inputs (desktop + mobile)
    const sortSelect = document.getElementById('sortSelect');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');

    // Auto-apply filters on form change (checkboxes, radio buttons, price inputs)
    if(filterForm) {
        // Handle all input changes
        filterForm.addEventListener('change', function(e) {
            if(e.target.type === 'checkbox' || e.target.type === 'radio') {
                // Add specific handling for size and color filters
                if(e.target.classList.contains('size-filter') || e.target.classList.contains('color-filter')) {
                    console.log('Size/Color filter changed:', e.target.name, e.target.value, e.target.checked);
                }
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

    // Search functionality with debounce - handle both desktop and mobile search inputs
    searchInputs.forEach(function(searchInput) {
        searchInput.addEventListener('input', function(e) {
            // Sync all search inputs with the same value
            const searchValue = e.target.value;
            searchInputs.forEach(input => {
                if (input !== e.target) {
                    input.value = searchValue;
                }
            });
            
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                applyFilters();
            }, 500); // Wait 500ms after user stops typing
        });

        // Also handle Enter key for immediate search
        searchInput.addEventListener('keypress', function(e){
            if(e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(filterTimeout);
                applyFilters();
            }
        });
    });

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
        
        // Add search parameter from any search input - with debug logging
        let searchValue = '';
        searchInputs.forEach(input => {
            if (input.value.trim()) {
                searchValue = input.value.trim();
            }
        });
        
        if(searchValue) {
            console.log('Adding search parameter:', searchValue);
            formData.append('q', searchValue);
        } else {
            console.log('No search value found');
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
        
        // Debug logging
        console.log('FormData contents:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }
        console.log('Final URL:', url);

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

    // Active Filter Tags Management
    function updateActiveFilterTags() {
        const activeFiltersSection = document.getElementById('activeFiltersSection');
        const activeFilterTags = document.getElementById('activeFilterTags');
        const filterTags = [];

        // Get selected categories
        const selectedCategories = document.querySelectorAll('input[name="categories[]"]:checked');
        selectedCategories.forEach(input => {
            const label = input.closest('.form-check').querySelector('label').textContent.trim();
            filterTags.push({
                type: 'category',
                value: input.value,
                label: label.replace(/\(\d+\)$/, '').trim(),
                displayName: 'Category: ' + label.replace(/\(\d+\)$/, '').trim()
            });
        });

        // Get selected brands
        const selectedBrands = document.querySelectorAll('input[name="brands[]"]:checked');
        selectedBrands.forEach(input => {
            const label = input.closest('.form-check').querySelector('label').textContent.trim();
            filterTags.push({
                type: 'brand',
                value: input.value,
                label: label.replace(/\(\d+\)$/, '').trim(),
                displayName: 'Brand: ' + label.replace(/\(\d+\)$/, '').trim()
            });
        });

        // Get selected sizes
        const selectedSizes = document.querySelectorAll('input[name="sizes[]"]:checked');
        selectedSizes.forEach(input => {
            const label = input.closest('.form-check').querySelector('label').textContent.trim();
            filterTags.push({
                type: 'size',
                value: input.value,
                label: label.replace(/\(\d+\)$/, '').trim(),
                displayName: label.replace(/\(\d+\)$/, '').trim()
            });
        });

        // Get selected colors
        const selectedColors = document.querySelectorAll('input[name="colors[]"]:checked');
        selectedColors.forEach(input => {
            const label = input.closest('.form-check').querySelector('.color-name').textContent.trim();
            filterTags.push({
                type: 'color',
                value: input.value,
                label: label,
                displayName: label
            });
        });

        // Get price range
        const minPrice = document.getElementById('minPrice').value;
        const maxPrice = document.getElementById('maxPrice').value;
        if (minPrice || maxPrice) {
            let priceLabel = 'Price: ';
            if (minPrice && maxPrice) {
                priceLabel += `₹${minPrice} - ₹${maxPrice}`;
            } else if (minPrice) {
                priceLabel += `₹${minPrice}+`;
            } else if (maxPrice) {
                priceLabel += `Under ₹${maxPrice}`;
            }
            filterTags.push({
                type: 'price',
                value: 'price_range',
                label: priceLabel,
                displayName: priceLabel
            });
        }

        // Get search query from any search input
        let currentSearchValue = '';
        searchInputs.forEach(input => {
            if (input.value.trim()) {
                currentSearchValue = input.value.trim();
            }
        });
        
        if (currentSearchValue) {
            filterTags.push({
                type: 'search',
                value: 'search_query',
                label: currentSearchValue,
                displayName: 'Search: "' + currentSearchValue + '"'
            });
        }

        // Generate HTML for filter tags
        if (filterTags.length > 0) {
            activeFilterTags.innerHTML = filterTags.map(tag => `
                <span class="filter-tag" data-filter-type="${tag.type}" data-filter-value="${tag.value}">
                    ${tag.displayName}
                    <button type="button" class="remove-filter" onclick="removeFilter('${tag.type}', '${tag.value}')">
                        ✕
                    </button>
                </span>
            `).join('');
            activeFiltersSection.style.display = 'block';
        } else {
            activeFiltersSection.style.display = 'none';
        }
    }

    // Remove individual filter
    window.removeFilter = function(type, value) {
        switch(type) {
            case 'category':
                const categoryInput = document.querySelector(`input[name="categories[]"][value="${value}"]`);
                if (categoryInput) categoryInput.checked = false;
                break;
            case 'brand':
                const brandInput = document.querySelector(`input[name="brands[]"][value="${value}"]`);
                if (brandInput) brandInput.checked = false;
                break;
            case 'size':
                const sizeInput = document.querySelector(`input[name="sizes[]"][value="${value}"]`);
                if (sizeInput) sizeInput.checked = false;
                break;
            case 'color':
                const colorInput = document.querySelector(`input[name="colors[]"][value="${value}"]`);
                if (colorInput) colorInput.checked = false;
                break;
            case 'price':
                document.getElementById('minPrice').value = '';
                document.getElementById('maxPrice').value = '';
                break;
            case 'search':
                searchInputs.forEach(input => {
                    input.value = '';
                });
                break;
        }
        applyFilters();
    };

    // Clear all filters function
    function clearAllFilters() {
        // Clear all form inputs
        filterForm.reset();
        
        // Clear all search inputs
        searchInputs.forEach(input => {
            input.value = '';
        });
        
        // Clear sort select
        sortSelect.value = 'created_at';
        
        // Clear price inputs
        document.getElementById('minPrice').value = '';
        document.getElementById('maxPrice').value = '';
        
        // Apply filters (which will be empty, showing all products)
        applyFilters();
    }

    // Clear all filters - main button in active filters section
    document.getElementById('clearAllFilters').addEventListener('click', clearAllFilters);
    
    // Clear all filters - button in sidebar (if exists)
    const clearAllFiltersBtn = document.getElementById('clearAllFiltersBtn');
    if (clearAllFiltersBtn) {
        clearAllFiltersBtn.addEventListener('click', clearAllFilters);
    }

    // Override the existing applyFilters function to include active filter tags update
    const originalApplyFilters = applyFilters;
    applyFilters = function() {
        updateActiveFilterTags();
        originalApplyFilters();
    };

    // Initial load - update active filter tags
    updateActiveFilterTags();

    // Mobile Filter Panel Management
    const mobileFilterBtn = document.getElementById('mobileFilterBtn');
    const mobileFilterPanel = document.getElementById('mobileFilterPanel');
    const mobileFilterOverlay = document.getElementById('mobileFilterOverlay');
    const closeMobileFilter = document.getElementById('closeMobileFilter');
    const applyMobileFilters = document.getElementById('applyMobileFilters');
    const clearMobileFilters = document.getElementById('clearMobileFilters');

    // Open mobile filter panel
    if (mobileFilterBtn) {
        mobileFilterBtn.addEventListener('click', function() {
            mobileFilterPanel.style.display = 'flex';
            mobileFilterOverlay.style.display = 'block';
            document.body.classList.add('mobile-filter-open');
            
            // Trigger animation
            setTimeout(() => {
                mobileFilterPanel.classList.add('show');
            }, 10);
        });
    }

    // Close mobile filter panel
    function closeMobileFilterPanel() {
        mobileFilterPanel.classList.remove('show');
        document.body.classList.remove('mobile-filter-open');
        
        setTimeout(() => {
            mobileFilterPanel.style.display = 'none';
            mobileFilterOverlay.style.display = 'none';
        }, 300);
    }

    // Close filter panel events
    if (closeMobileFilter) {
        closeMobileFilter.addEventListener('click', closeMobileFilterPanel);
    }
    
    if (mobileFilterOverlay) {
        mobileFilterOverlay.addEventListener('click', closeMobileFilterPanel);
    }

    // Apply mobile filters
    if (applyMobileFilters) {
        applyMobileFilters.addEventListener('click', function() {
            applyFilters();
            closeMobileFilterPanel();
        });
    }

    // Clear mobile filters
    if (clearMobileFilters) {
        clearMobileFilters.addEventListener('click', function() {
            clearAllFilters();
            closeMobileFilterPanel();
        });
    }

    // Handle escape key for mobile filter
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileFilterPanel.classList.contains('show')) {
            closeMobileFilterPanel();
        }
    });

    // Prevent body scroll when mobile filter is open
    mobileFilterPanel.addEventListener('touchmove', function(e) {
        e.stopPropagation();
    });

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
            // if(searchBox.value.trim()) {
            //     formData.append('q', searchBox.value.trim());
            // }
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