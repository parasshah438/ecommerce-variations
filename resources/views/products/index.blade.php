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
                    <!-- Price Range -->
                    <div class="mb-4">
                        <h6 class="fw-semibold">Price Range</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm" placeholder="Min">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm" placeholder="Max">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categories -->
                    <div class="mb-4">
                        <h6 class="fw-semibold">Categories</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="electronics">
                            <label class="form-check-label" for="electronics">Electronics</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="clothing">
                            <label class="form-check-label" for="clothing">Clothing</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="home">
                            <label class="form-check-label" for="home">Home & Garden</label>
                        </div>
                    </div>
                    
                    <!-- Brands -->
                    <div class="mb-4">
                        <h6 class="fw-semibold">Brands</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="brand1">
                            <label class="form-check-label" for="brand1">Brand A</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="brand2">
                            <label class="form-check-label" for="brand2">Brand B</label>
                        </div>
                    </div>
                    
                    <button class="btn btn-primary w-100">Apply Filters</button>
                    <button class="btn btn-outline-secondary w-100 mt-2">Clear All</button>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9 col-md-8">
            <!-- Sort and View Options -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">All Products</h2>
                <div class="d-flex gap-2">
                    <input id="searchBox" class="form-control" placeholder="Search products..." style="min-width:250px;">
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option>Sort by: Featured</option>
                        <option>Price: Low to High</option>
                        <option>Price: High to Low</option>
                        <option>Newest First</option>
                        <option>Rating</option>
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

<!-- Wishlist Success Animation Container -->
<div id="wishlist-animation-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999;"></div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    let page = 1;
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const grid = document.getElementById('product-grid');

    if(loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function(){
            page++;
            loadMoreBtn.disabled = true;
            loadMoreBtn.innerText = 'Loading...';
            fetch(`/products/load-more?page=${page}`)
              .then(r => r.text())
              .then(html => {
                if(html.trim().length === 0) {
                  loadMoreBtn.innerText = 'No more products';
                  loadMoreBtn.disabled = true;
                  return;
                }
                const temp = document.createElement('div');
                temp.innerHTML = html;
                Array.from(temp.children).forEach(c => grid.appendChild(c));
                loadMoreBtn.disabled = false;
                loadMoreBtn.innerText = 'Load more';
              })
              .catch(err => {
                
                loadMoreBtn.disabled = false;
                loadMoreBtn.innerText = 'Load more';
              });
        });
    }

    const searchBox = document.getElementById('searchBox');
    if(searchBox){
        searchBox.addEventListener('keypress', function(e){
            if(e.key === 'Enter') {
                const q = this.value.trim();
                if(q.length) window.location = `/products?q=${encodeURIComponent(q)}`;
            }
        });
    }
});
</script>@endsection