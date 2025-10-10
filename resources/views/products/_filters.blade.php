<!-- Search Filter -->
<div class="border-bottom px-3 py-3">
    <label class="form-label fw-bold text-dark mb-2">
        <i class="bi bi-search me-2 text-primary"></i>Search Products
    </label>
    <div class="input-group">
        <input type="text" class="form-control" id="searchInput" placeholder="Search products...">
        <button class="btn btn-outline-primary" type="button">
            <i class="bi bi-search"></i>
        </button>
    </div>
</div>

<!-- Categories Filter -->
<div class="border-bottom px-3 py-3">
    <label class="form-label fw-bold text-dark mb-3">
        <i class="bi bi-grid-3x3-gap me-2 text-primary"></i>Categories
    </label>
    <div class="filter-scroll-container" style="max-height: 200px; overflow-y: auto;">
        @forelse($categories as $category)
        <div class="form-check mb-2 filter-item">
            <input class="form-check-input category-filter" type="checkbox" value="{{ $category->id }}" id="category{{ $category->id }}" data-filter-type="category">
            <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="category{{ $category->id }}">
                <span>{{ $category->name }}</span>
                <span class="badge bg-light text-dark">{{ $category->products_count }}</span>
            </label>
        </div>
        @empty
        <div class="text-muted small">No categories available</div>
        @endforelse
    </div>
</div>

<!-- Brands Filter -->
<div class="border-bottom px-3 py-3">
    <label class="form-label fw-bold text-dark mb-3">
        <i class="bi bi-award me-2 text-primary"></i>Brands
    </label>
    <div class="filter-scroll-container" style="max-height: 200px; overflow-y: auto;">
        @forelse($brands as $brand)
        <div class="form-check mb-2 filter-item">
            <input class="form-check-input brand-filter" type="checkbox" value="{{ $brand->id }}" id="brand{{ $brand->id }}" data-filter-type="brand">
            <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="brand{{ $brand->id }}">
                <span>{{ $brand->name }}</span>
                <span class="badge bg-light text-dark">{{ $brand->products_count }}</span>
            </label>
        </div>
        @empty
        <div class="text-muted small">No brands available</div>
        @endforelse
    </div>
</div>

<!-- Price Range Filter -->
<div class="border-bottom px-3 py-3">
    <label class="form-label fw-bold text-dark mb-3">
        <i class="bi bi-currency-rupee me-2 text-primary"></i>Price Range
    </label>
    <div class="row g-2 mb-3">
        <div class="col-6">
            <input type="number" class="form-control form-control-sm price-filter" id="minPrice" placeholder="Min ₹" min="0" max="{{ $priceRange->max_price ?? 10000 }}" data-filter-type="price">
        </div>
        <div class="col-6">
            <input type="number" class="form-control form-control-sm price-filter" id="maxPrice" placeholder="Max ₹" min="0" max="{{ $priceRange->max_price ?? 10000 }}" data-filter-type="price">
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
            <input class="form-check-input rating-filter" type="radio" name="ratingFilter" value="{{ $i }}" id="rating{{ $i }}" data-filter-type="rating">
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
        <input class="form-check-input stock-filter" type="checkbox" id="inStockOnly" data-filter-type="stock">
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
        <button class="btn btn-outline-primary btn-sm quick-filter" data-filter="discount">
            <i class="bi bi-percent me-1"></i>On Sale
        </button>
        <button class="btn btn-outline-success btn-sm quick-filter" data-filter="new">
            <i class="bi bi-star me-1"></i>New Arrivals
        </button>
        <button class="btn btn-outline-warning btn-sm quick-filter" data-filter="trending">
            <i class="bi bi-graph-up me-1"></i>Trending
        </button>
    </div>
</div>

<style>
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
    background: var(--primary-color, #007bff);
    border-radius: 2px;
}

.filter-scroll-container::-webkit-scrollbar-thumb:hover {
    background: var(--primary-dark, #0056b3);
}

.quick-filter {
    border-radius: 15px;
    font-size: 0.8rem;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.quick-filter:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.quick-filter.active {
    background-color: var(--primary-color, #007bff);
    border-color: var(--primary-color, #007bff);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.filter-item {
    transition: background-color 0.2s ease;
    border-radius: 8px;
    padding: 4px 8px;
    margin: 2px 0;
}

.filter-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.form-check-input:focus {
    border-color: var(--primary-color, #007bff);
    box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
}

.rating-filter-container {
    position: relative;
    z-index: 1;
}

/* Ensure filters don't interfere with header */
.border-bottom {
    position: relative;
    z-index: 1;
}

/* Price range buttons styling */
.btn-outline-secondary {
    border-color: #dee2e6;
    transition: all 0.2s ease;
}

.btn-outline-secondary:hover {
    background-color: #f8f9fa;
    border-color: #adb5bd;
}
</style>