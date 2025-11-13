@extends('layouts.frontend')

@section('title', $category->name . ' - ' . config('app.name'))

@section('breadcrumb')
<!-- Enhanced Category Hero Section -->
<div class="category-hero-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 3rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-dark mb-3" style="background: transparent;">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-white-50">Products</a></li>
                        @php
                            $breadcrumbPath = $category->getBreadcrumbPath();
                        @endphp
                        @foreach($breadcrumbPath as $index => $breadcrumbCategory)
                            @if($loop->last)
                                <li class="breadcrumb-item active text-white" aria-current="page">{{ $breadcrumbCategory->name }}</li>
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
                
                <h1 class="display-5 fw-bold mb-3 text-white">
                    @if($category->parent)
                        <span class="text-white-75 fs-4 fw-normal d-block mb-2">{{ $category->parent->name }}</span>
                    @endif
                    {{ $category->name }}
                </h1>
                
                <p class="lead mb-0 text-white" style="opacity: 0.9;">
                    @if($category->description)
                        {{ Str::limit($category->description, 150) }}
                    @else
                        @if($category->parent)
                            Explore our premium {{ strtolower($category->name) }} collection in {{ strtolower($category->parent->name) }}
                        @else
                            Discover our curated {{ strtolower($category->name) }} collection
                        @endif
                    @endif
                </p>
                
                <!-- Product count badge -->
                <div class="mt-3">
                    <span class="badge bg-white text-primary px-3 py-2">
                        <i class="bi bi-box-seam me-1"></i>
                        {{ $products->total() }} {{ Str::plural('Product', $products->total()) }}
                    </span>
                </div>
            </div>
            
            <div class="col-md-4 text-end d-none d-md-block">
                @if($category->image)
                    <img src="{{ $category->imageUrl }}" 
                         alt="{{ $category->name }}" 
                         class="img-fluid rounded shadow-lg" 
                         style="max-height: 120px; opacity: 0.95; object-fit: cover;">
                @else
                    <div class="category-icon-placeholder" style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 1rem; display: inline-block;">
                        <i class="bi bi-grid-3x3-gap display-3 text-white"></i>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Child Categories Quick Links (if exists) -->
        @if($category->children && $category->children->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex gap-2 flex-wrap">
                    <small class="text-white-50 me-2">Explore:</small>
                    @foreach($category->children as $child)
                        <a href="{{ route('category.products', $child->slug) }}" 
                           class="btn btn-sm btn-outline-light rounded-pill">
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Category-specific configuration
    const CATEGORY_SLUG = '{{ $category->slug }}';
    const FILTER_URL = '/category/' + CATEGORY_SLUG;
    
    let page = 1;
    let isLoadingMore = false;
    let filterTimeout = null;
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const grid = document.getElementById('product-grid');
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');

    // Hide load more button if there are no more products
    @if(!$products->hasMorePages())
        if(loadMoreBtn) {
            loadMoreBtn.style.display = 'none';
        }
    @endif

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
        const minPriceInput = document.getElementById('minPriceInput');
        const maxPriceInput = document.getElementById('maxPriceInput');
        if (minPriceInput) minPriceInput.value = '';
        if (maxPriceInput) maxPriceInput.value = '';
        
        // Reset sliders to full range
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

    // ===== AMAZON-STYLE PRICE RANGE SLIDER =====
    const minPriceRange = document.getElementById('minPriceRange');
    const maxPriceRange = document.getElementById('maxPriceRange');
    const minPriceInput = document.getElementById('minPriceInput');
    const maxPriceInput = document.getElementById('maxPriceInput');
    const minPriceDisplay = document.getElementById('minPriceDisplay');
    const maxPriceDisplay = document.getElementById('maxPriceDisplay');
    const sliderRange = document.getElementById('sliderRange');
    const applyPriceBtn = document.getElementById('applyPriceFilter');

    // Function to update the visual slider range
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

    // Function to format number with commas
    function formatPrice(price) {
        return parseInt(price).toLocaleString('en-IN');
    }

    // Min range slider event
    if (minPriceRange) {
        minPriceRange.addEventListener('input', function() {
            let minVal = parseInt(this.value);
            let maxVal = parseInt(maxPriceRange.value);
            
            // Prevent overlap - min cannot exceed max
            if (minVal >= maxVal) {
                minVal = maxVal - 100;
                this.value = minVal;
            }
            
            // Update displays
            if (minPriceDisplay) minPriceDisplay.textContent = '₹' + formatPrice(minVal);
            if (minPriceInput) minPriceInput.value = minVal;
            
            updateSliderRange();
        });
    }

    // Max range slider event
    if (maxPriceRange) {
        maxPriceRange.addEventListener('input', function() {
            let maxVal = parseInt(this.value);
            let minVal = parseInt(minPriceRange.value);
            
            // Prevent overlap - max cannot be less than min
            if (maxVal <= minVal) {
                maxVal = minVal + 100;
                this.value = maxVal;
            }
            
            // Update displays
            if (maxPriceDisplay) maxPriceDisplay.textContent = '₹' + formatPrice(maxVal);
            if (maxPriceInput) maxPriceInput.value = maxVal;
            
            updateSliderRange();
        });
    }

    // Manual input events (sync with sliders)
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

    // Apply price filter button
    if (applyPriceBtn) {
        applyPriceBtn.addEventListener('click', function() {
            const minVal = minPriceRange.value;
            const maxVal = maxPriceRange.value;
            
            // Update hidden form inputs
            document.getElementById('minPrice').value = minVal;
            document.getElementById('maxPrice').value = maxVal;
            
            // Apply filters
            applyFilters();
        });
    }

    // Initialize slider on page load
    if (minPriceRange && maxPriceRange) {
        updateSliderRange();
    }
    // ===== END PRICE RANGE SLIDER =====

    // Search functionality with debounce
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
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
    }

    // Sort functionality - Handle dropdown sort options
    document.querySelectorAll('.sort-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            
            const sort = this.dataset.sort;
            const order = this.dataset.order || 'desc';
            
            // Update active state
            document.querySelectorAll('.sort-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            // Apply filters with sort
            applyFilters(sort, order);
        });
    });

    // Apply filters function
    function applyFilters(sort = null, order = null) {
        const loadingOverlay = document.getElementById('filterLoadingOverlay') || createLoadingOverlay();
        
        // Show loading overlay with body blur
        if (loadingOverlay) {
            document.body.classList.add('filter-loading');
            loadingOverlay.style.display = 'flex';
        }

        const formData = new FormData(filterForm);
        
        // Add search parameter
        if(searchInput && searchInput.value.trim()) {
            formData.append('q', searchInput.value.trim());
        }
        
        // Add sort parameter
        if (sort) {
            formData.append('sort', sort);
            if (order) formData.append('order', order);
        } else {
            const activeSort = document.querySelector('.sort-option.active');
            if (activeSort) {
                formData.append('sort', activeSort.dataset.sort);
                if (activeSort.dataset.order) {
                    formData.append('order', activeSort.dataset.order);
                }
            }
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
        const url = FILTER_URL + '?' + params.toString();
        
        // Debug logging
        console.log('Category Filter URL:', url);

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
                    
                    // Update product count
                    const totalProducts = document.getElementById('totalProducts');
                    if (totalProducts && data.total !== undefined) {
                        totalProducts.textContent = data.total;
                    }
                    
                    // Show/hide load more button based on availability
                    const loadMoreContainer = document.getElementById('loadMoreContainer');
                    if(loadMoreBtn) {
                        if(data.has_more) {
                            if (loadMoreContainer) loadMoreContainer.style.display = 'block';
                            loadMoreBtn.style.display = 'block';
                            loadMoreBtn.dataset.page = data.current_page + 1;
                        } else {
                            if (loadMoreContainer) {
                                loadMoreContainer.innerHTML = '<div class="text-center mt-4 text-muted"><i class="bi bi-check-circle me-2"></i>All products loaded</div>';
                            }
                        }
                    }

                    // Show/hide no results
                    const noResults = document.getElementById('noResults');
                    const productsContainer = document.getElementById('productsContainer');
                    if (data.total === 0) {
                        if (noResults) noResults.classList.remove('d-none');
                        if (productsContainer) productsContainer.classList.add('d-none');
                    } else {
                        if (noResults) noResults.classList.add('d-none');
                        if (productsContainer) productsContainer.classList.remove('d-none');
                    }

                    // Update filters if provided
                    if (data.filters_html) {
                        const filtersContainer = document.getElementById('filtersContainer');
                        if (filtersContainer) {
                            filtersContainer.innerHTML = data.filters_html;
                        }
                    }

                    // Log results for debugging
                    console.log(`Filters applied: ${data.total} products found in ${CATEGORY_SLUG}`);
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

    // Create loading overlay if it doesn't exist
    function createLoadingOverlay() {
        let overlay = document.createElement('div');
        overlay.id = 'filterLoadingOverlay';
        overlay.className = 'filter-loading-overlay';
        overlay.style.display = 'none';
        overlay.innerHTML = '<div class="premium-spinner"></div>';
        document.body.appendChild(overlay);
        return overlay;
    }

    // Active Filter Tags Management
    function updateActiveFilterTags() {
        const activeFiltersSection = document.getElementById('activeFiltersSection');
        const activeFilterTags = document.getElementById('activeFilterTags');
        if (!activeFiltersSection || !activeFilterTags) return;
        
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
            const colorName = input.closest('.form-check').querySelector('.color-name');
            if (colorName) {
                const label = colorName.textContent.trim();
                filterTags.push({
                    type: 'color',
                    value: input.value,
                    label: label,
                    displayName: label
                });
            }
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

        // Get search query
        if (searchInput && searchInput.value.trim()) {
            filterTags.push({
                type: 'search',
                value: 'search_query',
                label: searchInput.value.trim(),
                displayName: 'Search: "' + searchInput.value.trim() + '"'
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
                const minPriceInput = document.getElementById('minPriceInput');
                const maxPriceInput = document.getElementById('maxPriceInput');
                if (minPriceInput) minPriceInput.value = '';
                if (maxPriceInput) maxPriceInput.value = '';
                break;
            case 'search':
                if (searchInput) searchInput.value = '';
                break;
        }
        applyFilters();
    };

    // Clear all filters function
    function clearAllFilters() {
        // Clear all form inputs
        if (filterForm) filterForm.reset();
        
        // Clear search input
        if (searchInput) searchInput.value = '';
        
        // Clear sort select
        document.querySelectorAll('.sort-option').forEach(opt => opt.classList.remove('active'));
        const defaultSort = document.querySelector('.sort-option[data-sort="created_at"]');
        if (defaultSort) defaultSort.classList.add('active');
        
        // Clear price inputs
        const minPrice = document.getElementById('minPrice');
        const maxPrice = document.getElementById('maxPrice');
        if (minPrice) minPrice.value = '';
        if (maxPrice) maxPrice.value = '';
        
        // Apply filters (which will be empty, showing all products)
        applyFilters();
    }

    // Clear all filters - button handlers
    const clearAllFiltersBtn = document.getElementById('clearAllFilters');
    if (clearAllFiltersBtn) {
        clearAllFiltersBtn.addEventListener('click', clearAllFilters);
    }
    
    const clearFiltersMobile = document.getElementById('clearFiltersMobile');
    if (clearFiltersMobile) {
        clearFiltersMobile.addEventListener('click', clearAllFilters);
    }

    const clearAllFiltersBtnSidebar = document.getElementById('clearAllFiltersBtn');
    if (clearAllFiltersBtnSidebar) {
        clearAllFiltersBtnSidebar.addEventListener('click', clearAllFilters);
    }

    // Initial load - update active filter tags if section exists
    const activeFiltersSection = document.getElementById('activeFiltersSection');
    if (activeFiltersSection) {
        updateActiveFilterTags();
        
        // Override applyFilters to include active filter tags update
        const originalApplyFilters = applyFilters;
        applyFilters = function(sort, order) {
            updateActiveFilterTags();
            originalApplyFilters(sort, order);
        };
    }

    // Mobile Filter Panel Management
    const openMobileFilter = document.getElementById('openMobileFilter');
    const mobileFilterSidebar = document.getElementById('mobileFilterSidebar');
    const mobileFilterBackdrop = document.getElementById('mobileFilterBackdrop');
    const closeMobileFilter = document.getElementById('closeMobileFilter');
    const applyFiltersMobile = document.getElementById('applyFiltersMobile');

    // Open mobile filter panel
    if (openMobileFilter && mobileFilterSidebar) {
        openMobileFilter.addEventListener('click', function() {
            mobileFilterSidebar.style.display = 'block';
            document.body.classList.add('mobile-filter-open');
            
            // Trigger animation
            setTimeout(() => {
                mobileFilterSidebar.classList.add('show');
            }, 10);
        });
    }

    // Close mobile filter panel
    function closeMobileFilterPanel() {
        if (!mobileFilterSidebar) return;
        
        mobileFilterSidebar.classList.remove('show');
        document.body.classList.remove('mobile-filter-open');
        
        setTimeout(() => {
            mobileFilterSidebar.style.display = 'none';
        }, 300);
    }

    // Close filter panel events
    if (closeMobileFilter) {
        closeMobileFilter.addEventListener('click', closeMobileFilterPanel);
    }
    
    if (mobileFilterBackdrop) {
        mobileFilterBackdrop.addEventListener('click', closeMobileFilterPanel);
    }

    // Apply mobile filters
    if (applyFiltersMobile) {
        applyFiltersMobile.addEventListener('click', function() {
            applyFilters();
            closeMobileFilterPanel();
        });
    }

    // Handle escape key for mobile filter
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileFilterSidebar && mobileFilterSidebar.classList.contains('show')) {
            closeMobileFilterPanel();
        }
    });

    // Prevent body scroll when mobile filter is open
    if (mobileFilterSidebar) {
        mobileFilterSidebar.addEventListener('touchmove', function(e) {
            e.stopPropagation();
        });
    }

    // Load more functionality (updated to work with filters)
    if(loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function(){
            if(isLoadingMore) return;
            
            isLoadingMore = true;
            page = parseInt(this.dataset.page) || page + 1;
            loadMoreBtn.classList.add('loading');
            
            // Get current filter parameters
            const formData = new FormData(filterForm);
            
            if(searchInput && searchInput.value.trim()) {
                formData.append('q', searchInput.value.trim());
            }
            
            const activeSort = document.querySelector('.sort-option.active');
            if (activeSort) {
                formData.append('sort', activeSort.dataset.sort);
                if (activeSort.dataset.order) {
                    formData.append('order', activeSort.dataset.order);
                }
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
                    if (!grid) {
                        console.error('Products grid element not found');
                        return;
                    }
                    
                    const temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    Array.from(temp.children).forEach(c => {
                        if (c && c.nodeType === 1) { // Ensure it's an element node
                            grid.appendChild(c);
                        }
                    });
                    
                    // Update current count
                    const currentCount = document.getElementById('currentCount');
                    if (currentCount) {
                        currentCount.textContent = grid.querySelectorAll('.product-card').length;
                    }
                    
                    if(!data.has_more) {
                        if (loadMoreBtn) {
                            loadMoreBtn.style.display = 'none';
                        }
                        // Optionally show "All products loaded" message
                        const loadMoreContainer = loadMoreBtn ? loadMoreBtn.parentElement : null;
                        if (loadMoreContainer) {
                            const allLoadedMsg = document.createElement('div');
                            allLoadedMsg.className = 'text-center mt-4 text-muted';
                            allLoadedMsg.innerHTML = '<i class="bi bi-check-circle me-2"></i>All products loaded';
                            loadMoreContainer.appendChild(allLoadedMsg);
                        }
                    } else {
                        loadMoreBtn.classList.remove('loading');
                        loadMoreBtn.dataset.page = data.current_page + 1;
                    }
                } else {
                    if (loadMoreBtn) {
                        loadMoreBtn.style.display = 'none';
                    }
                    // Show "All products loaded" message
                    const loadMoreContainer = loadMoreBtn ? loadMoreBtn.parentElement : null;
                    if (loadMoreContainer) {
                        const allLoadedMsg = document.createElement('div');
                        allLoadedMsg.className = 'text-center mt-4 text-muted';
                        allLoadedMsg.innerHTML = '<i class="bi bi-check-circle me-2"></i>All products loaded';
                        loadMoreContainer.appendChild(allLoadedMsg);
                    }
                }
            })
            .catch(err => {
                console.error('Load more error:', err);
                loadMoreBtn.classList.remove('loading');
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to load more products');
                }
            })
            .finally(() => {
                isLoadingMore = false;
            });
        });
    }

    // View mode toggle (if exists)
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    
    if (gridView && listView) {
        gridView.addEventListener('click', function() {
            gridView.classList.add('active');
            listView.classList.remove('active');
            grid.classList.remove('list-view');
            grid.classList.add('row', 'g-3');
        });
        
        listView.addEventListener('click', function() {
            listView.classList.add('active');
            gridView.classList.remove('active');
            grid.classList.add('list-view');
            grid.classList.remove('row', 'g-3');
        });
    }
});
</script>
@endpush