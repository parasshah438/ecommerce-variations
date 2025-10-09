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
            <!-- Filters Sidebar -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm">
                        <!-- Price Range -->
                        <div class="mb-4">
                            <h6 class="fw-semibold">Price Range</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control form-control-sm" 
                                           placeholder="Min" value="{{ request('min_price') }}">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control form-control-sm" 
                                           placeholder="Max" value="{{ request('max_price') }}">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Categories -->
                        @if($categories->count() > 0)
                        <div class="mb-4">
                            <h6 class="fw-semibold">Categories</h6>
                            @foreach($categories as $category)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="categories[]" value="{{ $category->id }}" 
                                       id="category_{{ $category->id }}"
                                       {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="category_{{ $category->id }}">
                                    {{ $category->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        
                        <!-- Brands -->
                        @if($brands->count() > 0)
                        <div class="mb-4">
                            <h6 class="fw-semibold">Brands</h6>
                            @foreach($brands as $brand)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="brands[]" value="{{ $brand->id }}" 
                                       id="brand_{{ $brand->id }}"
                                       {{ in_array($brand->id, request('brands', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="brand_{{ $brand->id }}">
                                    {{ $brand->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        
                        <button type="button" class="btn btn-outline-secondary w-100 mt-2" id="clearFiltersBtn">Clear All</button>
                    </form>
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

    // Auto-apply filters on form change (checkboxes, price inputs)
    if(filterForm) {
        // Handle checkbox changes
        filterForm.addEventListener('change', function(e) {
            if(e.target.type === 'checkbox') {
                applyFilters();
            }
        });

        // Handle price input changes with debounce
        filterForm.addEventListener('input', function(e) {
            if(e.target.type === 'number') {
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

    // Clear filters functionality
    if(clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // Clear all form inputs
            filterForm.reset();
            searchBox.value = '';
            sortSelect.value = 'created_at';
            
            // Apply filters (which will be empty, showing all products)
            applyFilters();
        });
    }

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
        
        // Add search and sort parameters
        if(searchBox.value.trim()) {
            formData.append('q', searchBox.value.trim());
        }
        if(sortSelect.value) {
            formData.append('sort', sortSelect.value);
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