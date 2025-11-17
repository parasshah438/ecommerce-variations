@extends('layouts.frontend')
@section('title', 'Search Results' . (!empty($searchMeta['query']) ? ' for "' . $searchMeta['query'] . '"' : ''))

@section('meta')
@if(!empty($searchMeta['query']))
<meta name="description" content="Search results for {{ $searchMeta['query'] }}. Found {{ $searchMeta['total_results'] }} products.">
<meta name="keywords" content="{{ $searchMeta['query'] }}, search, products, online shopping">
@else
<meta name="description" content="Popular products and trending searches. Discover the best products online.">
<meta name="keywords" content="popular products, trending, online shopping, best sellers">
@endif
@endsection

@section('breadcrumb')
<div class="bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                @if(!empty($searchMeta['query']))
                    <li class="breadcrumb-item active">Search: "{{ $searchMeta['query'] }}"</li>
                @else
                    <li class="breadcrumb-item active">Search</li>
                @endif
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
<!-- Search Intent Matches -->
@if(!empty($searchMeta['query']) && (!empty($searchMeta['category_matches']) || !empty($searchMeta['brand_matches'])))
<div class="container mt-3">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>Did you mean:</strong>
                @if(!empty($searchMeta['category_matches']))
                    @foreach($searchMeta['category_matches'] as $category)
                        <a href="{{ route('category.products', $category->slug) }}" class="category-match">
                            {{ $category->name }} (Category)
                        </a>
                    @endforeach
                @endif
                @if(!empty($searchMeta['brand_matches']))
                    @foreach($searchMeta['brand_matches'] as $brand)
                        <a href="{{ route('search.index', ['q' => $brand->name]) }}" class="brand-match">
                            {{ $brand->name }} (Brand)
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Search Suggestions -->
@if(!empty($searchMeta['query']) && !empty($suggestions))
<div class="container mt-3">
    <div class="row">
        <div class="col-12">
            <div class="search-suggestions">
                <h6 class="mb-2">People also searched for:</h6>
                @foreach($suggestions as $suggestion)
                    <a href="{{ route('search.index', ['q' => $suggestion]) }}" class="related-query">{{ $suggestion }}</a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<div class="container">
    <!-- Mobile-First Layout -->
    <div class="row">
        <!-- Desktop Sidebar (Hidden on Mobile) -->
        <div class="col-lg-3 col-md-4 d-none d-md-block mb-4">
            <!-- Advanced Filters Sidebar -->
            <form id="filterForm" class="filters-sidebar">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="bi bi-funnel me-2"></i>Filters
                        </h6>
                        <button type="button" id="clearAllFiltersBtn" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear All
                        </button>
                    </div>
                    <div class="card-body p-3" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                        @include('search._filters', compact('categories', 'brands', 'sizes', 'colors', 'priceRange'))
                    </div>
                </div>
            </form>
            <!-- Advanced Filters Sidebar End -->
        </div>        
        
        <!-- Main Content Area -->
        <div class="col-lg-9 col-md-8 col-12">
            <!-- Mobile Header with Filter Button -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                @if(!empty($searchMeta['query']))
                    <h2 class="h4 mb-0">
                        Search: "{{ $searchMeta['query'] }}"
                        <small class="text-muted d-block">{{ number_format($searchMeta['total_results']) }} products found</small>
                    </h2>
                @else
                    <h2 class="h4 mb-0">
                        Popular Products
                        <small class="text-muted d-block">Discover trending items</small>
                    </h2>
                @endif
                
                <!-- Mobile/Desktop Controls -->
                <div class="d-flex gap-2 align-items-center">
                    <!-- Mobile Filter Button (Only visible on mobile) -->
                    <button class="btn btn-outline-primary d-md-none" type="button" id="mobileFilterBtn">
                        <i class="bi bi-funnel"></i> Filters
                    </button>
                    
                    <!-- Sort Dropdown -->
                    <select class="form-select form-select-sm" style="width: auto;" id="sortSelect">
                        <option value="relevance" {{ request('sort') == 'relevance' || !request('sort') ? 'selected' : '' }}>Sort by: Relevance</option>
                        <option value="popularity" {{ request('sort') == 'popularity' ? 'selected' : '' }}>Sort by: Popularity</option>
                        <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Sort by: Customer Rating</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Sort by: Newest</option>
                        <option value="discount" {{ request('sort') == 'discount' ? 'selected' : '' }}>Sort by: Discount</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name: Z to A</option>
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
                    @include('search._filters', compact('categories', 'brands', 'sizes', 'colors', 'priceRange'))
                </div>
                <div class="mobile-filter-footer">
                    <button type="button" class="btn btn-outline-secondary" id="clearMobileFilters">Clear All</button>
                    <button type="button" class="btn btn-primary" id="applyMobileFilters">Apply Filters</button>
                </div>
            </div>

            <!-- Overlay for mobile filter -->
            <div id="mobileFilterOverlay" class="mobile-filter-overlay d-md-none" style="display: none;"></div>
            
            <div id="product-grid" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            
            @include('search._results', compact('products', 'searchMeta'))
            </div>
            
            <!-- Load More / Pagination -->
            @if($products->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $products->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center mt-4">
                <button id="loadMoreBtn" class="btn btn-primary" style="display: none;">Load more</button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Premium Loading Overlay -->
<div id="filterLoadingOverlay" class="filter-loading-overlay" style="display: none;">
    <div class="premium-spinner"></div>
</div>

<!-- No Search Query - Show Trending -->
@if(empty($searchMeta['query']) && !empty($trendingSearches))
<div class="container mb-5">
    <div class="row">
        <div class="col-12">
            <div class="trending-searches">
                <h4 class="mb-3">Trending Searches</h4>
                @foreach($trendingSearches as $trending)
                    <a href="{{ route('search.index', ['q' => $trending]) }}" class="trending-item">{{ $trending }}</a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Popular Categories -->
@if(empty($searchMeta['query']) && !empty($popularCategories))
<div class="container mb-5">
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4">Popular Categories</h4>
        </div>
        @foreach($popularCategories as $category)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h6 class="card-title">{{ $category->name }}</h6>
                    <p class="card-text text-muted small">{{ number_format($category->products_count) }} products</p>
                    <a href="{{ route('category.products', $category->slug) }}" class="btn btn-outline-primary btn-sm">
                        Explore
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Related Queries -->
@if(!empty($relatedQueries))
<div class="container mb-4">
    <div class="row">
        <div class="col-12">
            <div class="related-queries">
                <h6 class="mb-2">Related searches:</h6>
                @foreach($relatedQueries as $query)
                    <a href="{{ route('search.index', ['q' => $query]) }}" class="related-query">{{ $query }}</a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle sort change
    $('#sortBy').on('change', function() {
        let url = new URL(window.location);
        url.searchParams.set('sort', $(this).val());
        url.searchParams.delete('page'); // Reset to first page
        window.location.href = url.toString();
    });
    
    // Handle filter changes
    $('.filter-checkbox').on('change', function() {
        applyFilters();
    });
    
    // Handle price range filter
    $('#priceRange').on('change', function() {
        applyFilters();
    });
    
    function applyFilters() {
        let url = new URL(window.location);
        
        // Categories
        let categories = [];
        $('input[name="categories[]"]:checked').each(function() {
            categories.push($(this).val());
        });
        
        // Brands
        let brands = [];
        $('input[name="brands[]"]:checked').each(function() {
            brands.push($(this).val());
        });
        
        // Sizes
        let sizes = [];
        $('input[name="sizes[]"]:checked').each(function() {
            sizes.push($(this).val());
        });
        
        // Colors
        let colors = [];
        $('input[name="colors[]"]:checked').each(function() {
            colors.push($(this).val());
        });
        
        // Clear existing params
        url.searchParams.delete('categories');
        url.searchParams.delete('brands');
        url.searchParams.delete('sizes');
        url.searchParams.delete('colors');
        url.searchParams.delete('page');
        
        // Add new params
        categories.forEach(cat => url.searchParams.append('categories[]', cat));
        brands.forEach(brand => url.searchParams.append('brands[]', brand));
        sizes.forEach(size => url.searchParams.append('sizes[]', size));
        colors.forEach(color => url.searchParams.append('colors[]', color));
        
        // Price range
        let minPrice = $('#minPrice').val();
        let maxPrice = $('#maxPrice').val();
        
        if (minPrice) url.searchParams.set('min_price', minPrice);
        else url.searchParams.delete('min_price');
        
        if (maxPrice) url.searchParams.set('max_price', maxPrice);
        else url.searchParams.delete('max_price');
        
        // Other filters
        if ($('#inStock').is(':checked')) {
            url.searchParams.set('in_stock', '1');
        } else {
            url.searchParams.delete('in_stock');
        }
        
        // Apply filters with AJAX
        $.get(url.toString(), function(response) {
            if (response.html) {
                $('#products-container').html(response.html);
            }
            if (response.filters_html) {
                $('.search-filters').html(response.filters_html);
            }
            
            // Update URL without reload
            history.pushState(null, '', url.toString());
        });
    }
    
    // Track search analytics
    @if(!empty($searchMeta['query']))
    $.post('{{ route('search.track') }}', {
        q: '{{ $searchMeta['query'] }}',
        _token: '{{ csrf_token() }}'
    });
    @endif
});
</script>
@endpush