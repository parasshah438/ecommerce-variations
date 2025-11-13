<!-- Search Filter -->
<div class="border-bottom px-3 py-3">
    <label class="form-label fw-bold text-dark mb-2">
        <i class="bi bi-search me-2 text-primary"></i>Search Products
    </label>
    <div class="input-group">
        <input type="text" class="form-control search-input" name="q" placeholder="Search products..." value="{{ request('q') }}" data-filter-type="search">
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
            <input class="form-check-input category-filter" type="checkbox" 
                   name="categories[]" value="{{ $category->id }}" 
                   id="category{{ $category->id }}" data-filter-type="category"
                   {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
            <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="category{{ $category->id }}">
                <span>{{ $category->name }}</span>
                @if(isset($category->products_count))
                <span class="badge bg-light text-dark">{{ $category->products_count }}</span>
                @endif
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
            <input class="form-check-input brand-filter" type="checkbox" 
                   name="brands[]" value="{{ $brand->id }}" 
                   id="brand{{ $brand->id }}" data-filter-type="brand"
                   {{ in_array($brand->id, request('brands', [])) ? 'checked' : '' }}>
            <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="brand{{ $brand->id }}">
                <span>{{ $brand->name }}</span>
                @if(isset($brand->products_count))
                <span class="badge bg-light text-dark">{{ $brand->products_count }}</span>
                @endif
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
            <input type="number" class="form-control form-control-sm price-filter min-price-input" 
                   name="min_price" id="minPrice" placeholder="Min ₹" 
                   min="0" max="{{ $priceRange->max_price ?? 10000 }}" 
                   value="{{ request('min_price') }}" data-filter-type="price">
        </div>
        <div class="col-6">
            <input type="number" class="form-control form-control-sm price-filter max-price-input" 
                   name="max_price" id="maxPrice" placeholder="Max ₹" 
                   min="0" max="{{ $priceRange->max_price ?? 10000 }}" 
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
            <input class="form-check-input rating-filter rating-all" type="radio" name="ratingFilterGroup" value="" data-filter-type="rating" checked>
            <label class="form-check-label d-flex align-items-center">
                <span class="me-2">All Ratings</span>
            </label>
        </div>
        @for($i = 5; $i >= 1; $i--)
        <div class="form-check mb-2 filter-item">
            <input class="form-check-input rating-filter rating-{{ $i }}-star" type="radio" name="ratingFilterGroup" value="{{ $i }}" data-filter-type="rating">
            <label class="form-check-label d-flex align-items-center cursor-pointer">
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
        <input class="form-check-input stock-filter in-stock-checkbox" type="checkbox" data-filter-type="stock">
        <label class="form-check-label">
            <i class="bi bi-check-circle text-success me-1"></i>
            In Stock Only
        </label>
    </div>
</div>

<!-- Size Filter -->
@if(isset($sizes) && $sizes->count() > 0)
<div class="border-bottom px-3 py-3">
    <label class="form-label fw-bold text-dark mb-3">
        <i class="bi bi-rulers me-2 text-primary"></i>Sizes
    </label>
    <div class="filter-scroll-container" style="max-height: 150px; overflow-y: auto;">
        @foreach($sizes as $size)
        <div class="form-check mb-2 filter-item">
            <input class="form-check-input size-filter" type="checkbox" 
                   name="sizes[]" value="{{ $size->id }}" data-filter-type="size" 
                   id="size{{ $size->id }}"
                   {{ in_array($size->id, request('sizes', [])) ? 'checked' : '' }}>
            <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="size{{ $size->id }}">
                <span>{{ $size->value }}</span>
                @if(isset($size->products_count))
                <span class="badge bg-light text-dark">{{ $size->products_count }}</span>
                @endif
            </label>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Color Filter -->
@if(isset($colors) && $colors->count() > 0)
<div class="border-bottom px-3 py-3">
    <label class="form-label fw-bold text-dark mb-3">
        <i class="bi bi-palette me-2 text-primary"></i>Colors
    </label>
    <div class="filter-scroll-container" style="max-height: 200px; overflow-y: auto;">
        <div class="row g-2">
            @foreach($colors as $color)
            <div class="col-6">
                <div class="form-check color-filter-item">
                    <input class="form-check-input color-filter" type="checkbox" 
                           name="colors[]" value="{{ $color->id }}" data-filter-type="color" 
                           id="color{{ $color->id }}"
                           {{ in_array($color->id, request('colors', [])) ? 'checked' : '' }}>
                    <label class="form-check-label d-flex align-items-center color-option" for="color{{ $color->id }}">
                        @if($color->hex_color)
                            <span class="color-swatch me-2" style="background-color: {{ $color->hex_color }};"></span>
                        @else
                            <span class="color-swatch me-2 bg-light border"></span>
                        @endif
                        <span class="color-name">{{ $color->value }}</span>
                        @if(isset($color->products_count))
                        <span class="badge bg-light text-dark ms-auto">{{ $color->products_count }}</span>
                        @endif
                    </label>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

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

/* Color Filter Styles */
.color-swatch {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-block;
    border: 2px solid #dee2e6;
    flex-shrink: 0;
}

.color-filter-item {
    margin-bottom: 8px;
}

.color-filter-item .form-check-input:checked + .form-check-label .color-swatch {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.25);
}

.color-option {
    width: 100%;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    transition: background-color 0.2s ease;
}

.color-option:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.color-name {
    font-size: 0.85rem;
    flex: 1;
}

/* Size Filter Styles */
.size-filter-item .form-check-input:checked + .form-check-label {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
    border-radius: 6px;
    font-weight: 600;
}

/* Filter sections spacing */
.border-bottom:not(:last-child) {
    margin-bottom: 0;
}

/* Enhanced scroll container for better UX */
.filter-scroll-container {
    scrollbar-width: thin;
    scrollbar-color: var(--bs-primary) #f1f1f1;
}

.filter-scroll-container::-webkit-scrollbar {
    width: 6px;
}

.filter-scroll-container::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

.filter-scroll-container::-webkit-scrollbar-thumb {
    background: var(--bs-primary);
    border-radius: 3px;
}

.filter-scroll-container::-webkit-scrollbar-thumb:hover {
    background: rgba(var(--bs-primary-rgb), 0.8);
}
</style>