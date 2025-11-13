<div class="filters-sidebar">
                <div class="card border-0 shadow-sm d-md-block">
                    <div class="card-header bg-white border-bottom d-none d-md-block">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-funnel me-2 text-primary"></i>Filters</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <form id="filterForm">
                            <!-- Search Filter -->
                            <div class="filter-section border-bottom px-3 py-3">
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
                            <div class="filter-section border-bottom px-3 py-3">
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
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Brands Filter -->
                            @if($brands->count() > 0)
                            <div class="filter-section border-bottom px-3 py-3">
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
                                            
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Price Range Filter - Amazon Style -->
                            <div class="filter-section border-bottom px-3 py-3">
                                <label class="form-label fw-bold text-dark mb-3">
                                    <i class="bi bi-currency-rupee me-2 text-primary"></i>Price Range
                                </label>
                                
                                <!-- Price Display -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="price-display-box">
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Min</small>
                                        <strong id="minPriceDisplay" class="text-dark">₹{{ request('min_price', isset($priceRange) ? $priceRange->min_price ?? 0 : 0) }}</strong>
                                    </div>
                                    <div class="mx-2 text-muted">—</div>
                                    <div class="price-display-box">
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Max</small>
                                        <strong id="maxPriceDisplay" class="text-dark">₹{{ request('max_price', isset($priceRange) ? $priceRange->max_price ?? 10000 : 10000) }}</strong>
                                    </div>
                                </div>

                                <!-- Dual Range Slider -->
                                <div class="price-slider-container mb-3" style="position: relative; height: 6px;">
                                    <!-- Background Track -->
                                    <div class="slider-track" style="position: absolute; width: 100%; height: 6px; background: #e0e0e0; border-radius: 3px;"></div>
                                    
                                    <!-- Active Range Track -->
                                    <div id="sliderRange" class="slider-range" style="position: absolute; height: 6px; background: linear-gradient(90deg, #ff6b35 0%, #f7931e 100%); border-radius: 3px; left: 0%; right: 0%;"></div>
                                    
                                    <!-- Min Range Input -->
                                    <input type="range" 
                                           id="minPriceRange" 
                                           class="price-range-input" 
                                           min="{{ isset($priceRange) ? $priceRange->min_price ?? 0 : 0 }}" 
                                           max="{{ isset($priceRange) ? $priceRange->max_price ?? 10000 : 10000 }}" 
                                           value="{{ request('min_price', isset($priceRange) ? $priceRange->min_price ?? 0 : 0) }}"
                                           step="100"
                                           style="position: absolute; width: 100%; pointer-events: none; -webkit-appearance: none; background: transparent;">
                                    
                                    <!-- Max Range Input -->
                                    <input type="range" 
                                           id="maxPriceRange" 
                                           class="price-range-input" 
                                           min="{{ isset($priceRange) ? $priceRange->min_price ?? 0 : 0 }}" 
                                           max="{{ isset($priceRange) ? $priceRange->max_price ?? 10000 : 10000 }}" 
                                           value="{{ request('max_price', isset($priceRange) ? $priceRange->max_price ?? 10000 : 10000) }}"
                                           step="100"
                                           style="position: absolute; width: 100%; pointer-events: none; -webkit-appearance: none; background: transparent;">
                                </div>

                                <!-- Hidden Inputs for Form Submission -->
                                <input type="hidden" id="minPrice" name="min_price" value="{{ request('min_price') }}">
                                <input type="hidden" id="maxPrice" name="max_price" value="{{ request('max_price') }}">

                                <!-- Price Input Fields (Manual Entry) -->
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" 
                                                   class="form-control form-control-sm" 
                                                   id="minPriceInput" 
                                                   placeholder="Min" 
                                                   min="{{ isset($priceRange) ? $priceRange->min_price ?? 0 : 0 }}" 
                                                   max="{{ isset($priceRange) ? $priceRange->max_price ?? 10000 : 10000 }}"
                                                   value="{{ request('min_price') }}">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" 
                                                   class="form-control form-control-sm" 
                                                   id="maxPriceInput" 
                                                   placeholder="Max" 
                                                   min="{{ isset($priceRange) ? $priceRange->min_price ?? 0 : 0 }}" 
                                                   max="{{ isset($priceRange) ? $priceRange->max_price ?? 10000 : 10000 }}"
                                                   value="{{ request('max_price') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Go & Clear Buttons -->
                                <div class="d-flex gap-2">
                                    <button type="button" id="applyPriceFilter" class="btn btn-primary btn-sm flex-fill">
                                        <i class="bi bi-check2"></i> Apply
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearPriceRange()">
                                        <i class="bi bi-x"></i> Clear
                                    </button>
                                </div>

                                <!-- Available Range Info -->
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Available: ₹{{ number_format(isset($priceRange) ? $priceRange->min_price ?? 0 : 0) }} - ₹{{ number_format(isset($priceRange) ? $priceRange->max_price ?? 10000 : 10000) }}
                                    </small>
                                </div>
                            </div>

                            <!-- Rating Filter -->
                            <div class="filter-section border-bottom px-3 py-3">
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

                            <!-- Size Filter -->
                            @if(isset($sizes) && $sizes->count() > 0)
                            <div class="filter-section border-bottom px-3 py-3">
                                <label class="form-label fw-bold text-dark mb-3">
                                    <i class="bi bi-rulers me-2 text-primary"></i>Size
                                </label>
                                <div class="filter-scroll-container" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($sizes as $size)
                                    <div class="form-check mb-2 filter-item">
                                        <input class="form-check-input size-filter" type="checkbox" 
                                               name="sizes[]" value="{{ $size->id }}" 
                                               id="size{{ $size->id }}" data-filter-type="size"
                                               {{ in_array($size->id, request('sizes', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="size{{ $size->id }}">
                                            <span>{{ $size->value }}</span>
                                            <small class="text-muted">({{ $size->products_count ?? 0 }})</small>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Color Filter -->
                            @if(isset($colors) && $colors->count() > 0)
                            <div class="filter-section border-bottom px-3 py-3">
                                <label class="form-label fw-bold text-dark mb-3">
                                    <i class="bi bi-palette me-2 text-primary"></i>Color
                                </label>
                                <div class="filter-scroll-container" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($colors as $color)
                                    <div class="form-check mb-2 filter-item">
                                        <input class="form-check-input color-filter" type="checkbox" 
                                               name="colors[]" value="{{ $color->id }}" 
                                               id="color{{ $color->id }}" data-filter-type="color"
                                               {{ in_array($color->id, request('colors', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="color{{ $color->id }}">
                                            <div class="d-flex align-items-center">
                                                @if($color->hex_color)
                                                <span class="color-swatch me-2" style="width: 16px; height: 16px; background-color: {{ $color->hex_color }}; border-radius: 50%; border: 1px solid #ddd; display: inline-block;"></span>
                                                @endif
                                                <span class="color-name">{{ $color->value }}</span>
                                            </div>
                                            <small class="text-muted">({{ $color->products_count ?? 0 }})</small>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Availability Filter -->
                            <div class="filter-section border-bottom px-3 py-3">
                                <label class="form-label fw-bold text-dark mb-3">
                                    <i class="bi bi-box me-2 text-primary"></i>Availability
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input stock-filter in-stock-checkbox" type="checkbox" id="inStockOnly" name="in_stock" data-filter-type="stock" {{ request('in_stock') ? 'checked' : '' }}>
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