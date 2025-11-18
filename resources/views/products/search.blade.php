@extends('layouts.frontend')

@section('title', 'Search: ' . ($searchQuery ?: 'All Products') . ' - ' . config('app.name'))

@section('breadcrumb')
<!-- Enhanced Search Hero Section -->
<div class="category-hero-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 3rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-dark mb-3" style="background: transparent;">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-white-50">Products</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Search Results</li>
                    </ol>
                </nav>
                
                <h1 class="display-5 fw-bold mb-3 text-white">
                    @if($searchQuery)
                        Search Results for "{{ $searchQuery }}"
                    @else
                        All Products
                    @endif
                </h1>
                
                <p class="lead mb-0 text-white" style="opacity: 0.9;">
                    @if($searchQuery)
                        Showing products matching your search query
                    @else
                        Browse our complete collection
                    @endif
                </p>
                
                <!-- Product count badge -->
                <div class="mt-3">
                    <span class="badge bg-white text-primary px-3 py-2">
                        <i class="bi bi-box-seam me-1"></i>
                        {{ $products->total() }} {{ Str::plural('Product', $products->total()) }} Found
                    </span>
                </div>
            </div>
            
            <div class="col-md-4 text-end d-none d-md-block">
                <div class="category-icon-placeholder" style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 1rem; display: inline-block;">
                    <i class="bi bi-search display-3 text-white"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<style>

/* Category Hero Section Styling */
.category-hero-section {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.category-hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.1;
    z-index: 0;
}

.category-hero-section .container {
    position: relative;
    z-index: 1;
}

.breadcrumb-dark {
    padding: 0;
    margin-bottom: 1rem;
}

.breadcrumb-dark .breadcrumb-item + .breadcrumb-item::before {
    color: rgba(255, 255, 255, 0.5);
    content: "›";
    font-size: 1.2rem;
}

.breadcrumb-dark a {
    text-decoration: none;
    transition: all 0.3s ease;
}

.breadcrumb-dark a:hover {
    color: #fff !important;
    text-decoration: underline;
}

.text-white-75 {
    color: rgba(255, 255, 255, 0.75) !important;
}

.text-white-50 {
    color: rgba(255, 255, 255, 0.5) !important;
}

.category-icon-placeholder {
    transition: transform 0.3s ease;
}

.category-icon-placeholder:hover {
    transform: scale(1.05);
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

/* Amazon-Style Price Range Slider */
.price-slider-container {
    margin: 15px 0;
    padding: 0;
}

.price-display-box {
    text-align: center;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    min-width: 90px;
}

.price-display-box strong {
    font-size: 1rem;
    color: #ff6b35;
}

/* Dual Range Slider Styling */
.price-range-input {
    pointer-events: all !important;
}

.price-range-input::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    background: white;
    border: 3px solid #ff6b35;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    transition: all 0.2s ease;
    position: relative;
    z-index: 3;
}

.price-range-input::-webkit-slider-thumb:hover {
    transform: scale(1.15);
    border-color: #e55a2b;
    box-shadow: 0 3px 10px rgba(255, 107, 53, 0.4);
}

.price-range-input::-webkit-slider-thumb:active {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(255, 107, 53, 0.6);
}

.price-range-input::-moz-range-thumb {
    width: 18px;
    height: 18px;
    background: white;
    border: 3px solid #ff6b35;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    transition: all 0.2s ease;
}

.price-range-input::-moz-range-thumb:hover {
    transform: scale(1.15);
    border-color: #e55a2b;
    box-shadow: 0 3px 10px rgba(255, 107, 53, 0.4);
}

/* Manual Price Input Styling */
.input-group-sm .input-group-text {
    background: #f8f9fa;
    border-color: #dee2e6;
    font-weight: 600;
    color: #495057;
}

.input-group-sm .form-control:focus {
    border-color: #ff6b35;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.15);
}

/* Apply Button Styling */
#applyPriceFilter {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

#applyPriceFilter:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
}

/* Price range track animation */
.slider-range {
    transition: all 0.1s ease;
}

/* Border sections */

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
    justify-content: space-between;
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
                <h2 class="h4 mb-0">Search Results</h2>
                
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
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
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
                @if($products->total() > 0)
                    @include('products._list', ['products' => $products])
                @else
                    <!-- No Results Found -->
                    <div class="col-12">
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-search display-1 text-muted"></i>
                            </div>
                            <h3 class="mb-3">No Products Found</h3>
                            <p class="text-muted mb-4">
                                @if($searchQuery)
                                    We couldn't find any products matching "<strong>{{ $searchQuery }}</strong>".
                                @else
                                    No products available at the moment.
                                @endif
                            </p>
                            <div class="d-flex flex-column gap-2 align-items-center">
                                <p class="text-muted mb-3">Try:</p>
                                <ul class="list-unstyled text-start">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Checking your spelling</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Using more general keywords</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Removing some filters</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Browsing our categories</li>
                                </ul>
                                <div class="mt-3">
                                    <a href="{{ route('products.index') }}" class="btn btn-primary me-2">
                                        <i class="bi bi-grid me-2"></i>Browse All Products
                                    </a>
                                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-house-door me-2"></i>Go to Home
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            @if($products->hasMorePages())
            <div class="text-center mt-4">
                <button id="loadMoreBtn" class="btn btn-primary">Load more</button>
            </div>
            @endif
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Search-specific configuration
    const FILTER_URL = '/product-search';
    const SEARCH_QUERY = '{{ $searchQuery }}';
    
    let page = 1;
    let isLoadingMore = false;
    let filterTimeout = null;
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const grid = document.getElementById('product-grid');
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');

    // Set initial search value if exists
    if (searchInput && SEARCH_QUERY) {
        searchInput.value = SEARCH_QUERY;
    }

    // Hide load more button if there are no more products
    @if(!$products->hasMorePages())
        if(loadMoreBtn) {
            loadMoreBtn.style.display = 'none';
        }
    @endif

    // Sort select change handler
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            applyFilters();
        });
    }

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
                }, 800);
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
            this.classList.toggle('active');
            applyFilters();
        });
    });

    // Price range clear functionality
    window.clearPriceRange = function() {
        document.getElementById('minPrice').value = '';
        document.getElementById('maxPrice').value = '';
        const minPriceInput = document.getElementById('minPriceInput');
        const maxPriceInput = document.getElementById('maxPriceInput');
        if (minPriceInput) minPriceInput.value = '';
        if (maxPriceInput) maxPriceInput.value = '';
        
        const minRange = document.getElementById('minPriceRange');
        const maxRange = document.getElementById('maxPriceRange');
        if (minRange && maxRange) {
            minRange.value = minRange.min;
            maxRange.value = maxRange.max;
            document.getElementById('minPriceDisplay').textContent = '₹' + minRange.min;
            document.getElementById('maxPriceDisplay').textContent = '₹' + maxRange.max;
            updateSliderRange();
        }
        
        applyFilters();
    };

    // Price slider implementation (same as category page)
    const minPriceRange = document.getElementById('minPriceRange');
    const maxPriceRange = document.getElementById('maxPriceRange');
    const minPriceInput = document.getElementById('minPriceInput');
    const maxPriceInput = document.getElementById('maxPriceInput');
    const minPriceDisplay = document.getElementById('minPriceDisplay');
    const maxPriceDisplay = document.getElementById('maxPriceDisplay');
    const sliderRange = document.getElementById('sliderRange');
    const applyPriceBtn = document.getElementById('applyPriceFilter');

    function updateSliderRange() {
        if (!minPriceRange || !maxPriceRange || !sliderRange) return;
        
        const min = parseInt(minPriceRange.min);
        const max = parseInt(minPriceRange.max);
        const minVal = parseInt(minPriceRange.value);
        const maxVal = parseInt(maxPriceRange.value);
        
        const percentMin = ((minVal - min) / (max - min)) * 100;
        const percentMax = ((maxVal - min) / (max - min)) * 100;
        
        sliderRange.style.left = percentMin + '%';
        sliderRange.style.right = (100 - percentMax) + '%';
    }

    function formatPrice(price) {
        return parseInt(price).toLocaleString('en-IN');
    }

    if (minPriceRange) {
        minPriceRange.addEventListener('input', function() {
            let minVal = parseInt(this.value);
            let maxVal = parseInt(maxPriceRange.value);
            
            if (minVal >= maxVal) {
                minVal = maxVal - 100;
                this.value = minVal;
            }
            
            if (minPriceDisplay) minPriceDisplay.textContent = '₹' + formatPrice(minVal);
            if (minPriceInput) minPriceInput.value = minVal;
            
            updateSliderRange();
        });
    }

    if (maxPriceRange) {
        maxPriceRange.addEventListener('input', function() {
            let maxVal = parseInt(this.value);
            let minVal = parseInt(minPriceRange.value);
            
            if (maxVal <= minVal) {
                maxVal = minVal + 100;
                this.value = maxVal;
            }
            
            if (maxPriceDisplay) maxPriceDisplay.textContent = '₹' + formatPrice(maxVal);
            if (maxPriceInput) maxPriceInput.value = maxVal;
            
            updateSliderRange();
        });
    }

    if (minPriceInput) {
        minPriceInput.addEventListener('input', function() {
            if (!minPriceRange) return;
            let value = parseInt(this.value) || parseInt(minPriceRange.min);
            const max = parseInt(maxPriceRange.value);
            
            if (value >= max) {
                value = max - 100;
                this.value = value;
            }
            
            minPriceRange.value = value;
            if (minPriceDisplay) minPriceDisplay.textContent = '₹' + formatPrice(value);
            updateSliderRange();
        });
    }

    if (maxPriceInput) {
        maxPriceInput.addEventListener('input', function() {
            if (!maxPriceRange) return;
            let value = parseInt(this.value) || parseInt(maxPriceRange.max);
            const min = parseInt(minPriceRange.value);
            
            if (value <= min) {
                value = min + 100;
                this.value = value;
            }
            
            maxPriceRange.value = value;
            if (maxPriceDisplay) maxPriceDisplay.textContent = '₹' + formatPrice(value);
            updateSliderRange();
        });
    }

    if (applyPriceBtn) {
        applyPriceBtn.addEventListener('click', function() {
            const minVal = minPriceRange.value;
            const maxVal = maxPriceRange.value;
            
            document.getElementById('minPrice').value = minVal;
            document.getElementById('maxPrice').value = maxVal;
            
            applyFilters();
        });
    }

    if (minPriceRange && maxPriceRange) {
        updateSliderRange();
    }

    // Search functionality with debounce
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        });

        searchInput.addEventListener('keypress', function(e){
            if(e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(filterTimeout);
                applyFilters();
            }
        });
    }

    // Apply filters function
    function applyFilters(sort = null, order = null) {
        const loadingOverlay = document.getElementById('filterLoadingOverlay') || createLoadingOverlay();
        
        if (loadingOverlay) {
            document.body.classList.add('filter-loading');
            loadingOverlay.style.display = 'flex';
        }

        const formData = new FormData(filterForm);
        
        // Add search parameter - IMPORTANT for search page
        if(searchInput && searchInput.value.trim()) {
            formData.append('q', searchInput.value.trim());
        }
        
        // Add sort parameter from select dropdown
        if (sortSelect && sortSelect.value) {
            formData.append('sort', sortSelect.value);
        }

        const params = new URLSearchParams(formData);
        const url = FILTER_URL + '?' + params.toString();
        
        console.log('Search Filter URL:', url);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            setTimeout(() => {
                if(data.html) {
                    if (loadingOverlay) {
                        loadingOverlay.style.display = 'none';
                        document.body.classList.remove('filter-loading');
                    }
                    
                    grid.innerHTML = data.html;
                    grid.classList.add('filter-results');
                    
                    setTimeout(() => {
                        grid.classList.remove('filter-results');
                    }, 600);
                    
                    page = 1;
                    
                    if(loadMoreBtn) {
                        if(data.has_more) {
                            loadMoreBtn.style.display = 'block';
                            loadMoreBtn.dataset.page = data.current_page + 1;
                        } else {
                            loadMoreBtn.style.display = 'none';
                        }
                    }

                    if (data.filters_html) {
                        const filtersContainer = document.getElementById('filtersContainer');
                        if (filtersContainer) {
                            filtersContainer.innerHTML = data.filters_html;
                        }
                    }

                    console.log(`Filters applied: ${data.total} products found`);
                }
            }, 300);
        })
        .catch(error => {
            console.error('Filter error:', error);
            
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
                document.body.classList.remove('filter-loading');
            }
            
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
        });
    }

    function createLoadingOverlay() {
        let overlay = document.createElement('div');
        overlay.id = 'filterLoadingOverlay';
        overlay.className = 'filter-loading-overlay';
        overlay.style.display = 'none';
        overlay.innerHTML = '<div class="premium-spinner"></div>';
        document.body.appendChild(overlay);
        return overlay;
    }

    // Clear all filters function
    function clearAllFilters() {
        if (filterForm) filterForm.reset();
        if (searchInput) searchInput.value = SEARCH_QUERY; // Keep the search query
        if (sortSelect) sortSelect.value = 'created_at';
        
        const minPrice = document.getElementById('minPrice');
        const maxPrice = document.getElementById('maxPrice');
        if (minPrice) minPrice.value = '';
        if (maxPrice) maxPrice.value = '';
        
        applyFilters();
    }

    const clearAllFiltersBtn = document.getElementById('clearAllFilters');
    if (clearAllFiltersBtn) {
        clearAllFiltersBtn.addEventListener('click', clearAllFilters);
    }
    
    const clearMobileFilters = document.getElementById('clearMobileFilters');
    if (clearMobileFilters) {
        clearMobileFilters.addEventListener('click', clearAllFilters);
    }

    // Mobile filter panel handlers
    const mobileFilterBtn = document.getElementById('mobileFilterBtn');
    const mobileFilterPanel = document.getElementById('mobileFilterPanel');
    const mobileFilterOverlay = document.getElementById('mobileFilterOverlay');
    const closeMobileFilter = document.getElementById('closeMobileFilter');
    const applyMobileFilters = document.getElementById('applyMobileFilters');

    if (mobileFilterBtn && mobileFilterPanel) {
        mobileFilterBtn.addEventListener('click', function() {
            mobileFilterPanel.style.display = 'block';
            mobileFilterOverlay.style.display = 'block';
            document.body.classList.add('mobile-filter-open');
            
            setTimeout(() => {
                mobileFilterPanel.classList.add('show');
            }, 10);
        });
    }

    function closeMobileFilterPanel() {
        if (!mobileFilterPanel) return;
        
        mobileFilterPanel.classList.remove('show');
        mobileFilterOverlay.style.display = 'none';
        document.body.classList.remove('mobile-filter-open');
        
        setTimeout(() => {
            mobileFilterPanel.style.display = 'none';
        }, 300);
    }

    if (closeMobileFilter) {
        closeMobileFilter.addEventListener('click', closeMobileFilterPanel);
    }
    
    if (mobileFilterOverlay) {
        mobileFilterOverlay.addEventListener('click', closeMobileFilterPanel);
    }

    if (applyMobileFilters) {
        applyMobileFilters.addEventListener('click', function() {
            applyFilters();
            closeMobileFilterPanel();
        });
    }

    // Load more functionality
    if(loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function(){
            if(isLoadingMore) return;
            
            isLoadingMore = true;
            page = parseInt(this.dataset.page) || page + 1;
            loadMoreBtn.classList.add('loading');
            
            const formData = new FormData(filterForm);
            
            if(searchInput && searchInput.value.trim()) {
                formData.append('q', searchInput.value.trim());
            }
            
            if (sortSelect && sortSelect.value) {
                formData.append('sort', sortSelect.value);
            }
            
            formData.append('page', page);
            formData.append('load_more', '1');
            
            const params = new URLSearchParams(formData);
            const url = FILTER_URL + '?' + params.toString();
            
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
                    Array.from(temp.children).forEach(c => {
                        if (c && c.nodeType === 1) {
                            grid.appendChild(c);
                        }
                    });
                    
                    if(!data.has_more) {
                        loadMoreBtn.style.display = 'none';
                    } else {
                        loadMoreBtn.classList.remove('loading');
                        loadMoreBtn.dataset.page = data.current_page + 1;
                    }
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            })
            .catch(err => {
                console.error('Load more error:', err);
                loadMoreBtn.classList.remove('loading');
            })
            .finally(() => {
                isLoadingMore = false;
            });
        });
    }
});
</script>
@endpush
