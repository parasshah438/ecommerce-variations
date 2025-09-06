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