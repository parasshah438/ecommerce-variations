@extends('layouts.app')

@section('title', $sale->name)

@section('content')
<div class="container-fluid px-0">
    <!-- Sale Header -->
    <div class="sale-header position-relative">
        @if($sale->banner_image)
            <div class="sale-banner-container position-relative">
                <img src="{{ Storage::disk('public')->url($sale->banner_image) }}" 
                     class="w-100" alt="{{ $sale->name }}" 
                     style="height: 400px; object-fit: cover;"
                     loading="lazy"
                     onerror="this.src='{{ asset('images/sale-placeholder.jpg') }}';">>
                <div class="sale-banner-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8">
                                <h1 class="display-3 fw-bold text-white mb-3">{{ $sale->name }}</h1>
                                <p class="lead text-white mb-4">{{ $sale->description }}</p>
                                <div class="sale-stats d-flex flex-wrap gap-4 mb-4">
                                    <div class="stat-item bg-white bg-opacity-20 rounded-pill px-4 py-2 text-white">
                                        <i class="bi bi-percent me-2"></i>
                                        Up to {{ $sale->discount_value }}{{ $sale->type === 'percentage' ? '%' : '₹' }} OFF
                                    </div>
                                    <div class="stat-item bg-white bg-opacity-20 rounded-pill px-4 py-2 text-white">
                                        <i class="bi bi-box-seam me-2"></i>
                                        {{ $products->total() }} Products
                                    </div>
                                    @if($sale->min_order_value)
                                    <div class="stat-item bg-white bg-opacity-20 rounded-pill px-4 py-2 text-white">
                                        <i class="bi bi-cart me-2"></i>
                                        Min Order: ₹{{ $sale->min_order_value }}
                                    </div>
                                    @endif
                                </div>
                                <!-- Sale Timer -->
                                <div class="sale-timer bg-danger text-white rounded-pill px-4 py-3 d-inline-block" data-end-date="{{ $sale->end_date->toISOString() }}">
                                    <i class="bi bi-clock-fill me-2"></i>
                                    <span class="fw-bold">Sale Ends In: </span>
                                    <span class="timer-display fw-bold fs-5">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-gradient-primary text-white py-5">
                <div class="container">
                    <h1 class="display-4 fw-bold mb-3">{{ $sale->name }}</h1>
                    <p class="lead mb-4">{{ $sale->description }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="container mt-5">
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach(App\Models\Category::all() as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Brand</label>
                                <select name="brand_id" class="form-select">
                                    <option value="">All Brands</option>
                                    @foreach(App\Models\Brand::all() as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Min Price</label>
                                <input type="number" name="min_price" class="form-control" placeholder="Min ₹">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Max Price</label>
                                <input type="number" name="max_price" class="form-control" placeholder="Max ₹">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sort By</label>
                                <select name="sort" class="form-select">
                                    <option value="name">Name</option>
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                    <option value="newest">Newest First</option>
                                    <option value="rating">Highest Rated</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div id="productsContainer">
            @include('frontend.sales.product-list', compact('products', 'sale'))
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="d-flex justify-content-center mt-5">
            {{ $products->links() }}
        </div>
    </div>
</div>

@push('styles')
<style>
.sale-banner-overlay {
    background: linear-gradient(45deg, rgba(0,0,0,0.7), rgba(0,0,0,0.3));
}

.timer-display {
    font-family: 'Courier New', monospace;
}

.product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.sale-price {
    font-size: 1.25rem;
    font-weight: bold;
}

.original-price {
    text-decoration: line-through;
    color: #6c757d;
}

.discount-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
}
</style>
@endpush

@push('scripts')
<script>
// Initialize sale timer
function initializeSaleTimers() {
    document.querySelectorAll('.sale-timer').forEach(timer => {
        const endDate = new Date(timer.dataset.endDate);
        const display = timer.querySelector('.timer-display');
        
        function updateTimer() {
            const now = new Date();
            const difference = endDate - now;
            
            if (difference > 0) {
                const days = Math.floor(difference / (1000 * 60 * 60 * 24));
                const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((difference % (1000 * 60)) / 1000);
                
                if (days > 0) {
                    display.innerHTML = `${days}d ${hours}h ${minutes}m`;
                } else {
                    display.innerHTML = `${hours}h ${minutes}m ${seconds}s`;
                }
            } else {
                display.innerHTML = 'Sale Ended';
                timer.classList.add('text-muted');
            }
        }
        
        updateTimer();
        setInterval(updateTimer, 1000);
    });
}

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeSaleTimers();
    
    const filterForm = document.getElementById('filterForm');
    const filterInputs = filterForm.querySelectorAll('select, input');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            loadProducts();
        });
    });
    
    function loadProducts(page = 1) {
        const formData = new FormData(filterForm);
        formData.append('page', page);
        
        const params = new URLSearchParams(formData);
        
        fetch(`{{ route('sales.products', $sale->slug) }}?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('productsContainer').innerHTML = data.products;
            document.getElementById('paginationContainer').innerHTML = data.pagination;
        })
        .catch(error => {
            console.error('Error loading products:', error);
        });
    }
    
    // Handle pagination clicks
    document.addEventListener('click', function(e) {
        if (e.target.matches('.page-link')) {
            e.preventDefault();
            const url = new URL(e.target.href);
            const page = url.searchParams.get('page');
            loadProducts(page);
        }
    });
});
</script>
@endpush
@endsection