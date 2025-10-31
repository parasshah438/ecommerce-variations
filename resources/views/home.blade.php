@extends('layouts.frontend')

@section('title', 'Home - ' . config('app.name'))

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Welcome to {{ config('app.name') }}</h1>
                <p class="lead mb-4">Discover amazing products at unbeatable prices. Shop from thousands of items with fast, free shipping.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('products.index') }}" class="btn btn-light btn-lg">
                        <i class="bi bi-shop me-2"></i>Shop Now
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-info-circle me-2"></i>Learn More
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="https://via.placeholder.com/500x350/0d6efd/ffffff?text=Hero+Image" 
                     class="img-fluid rounded-3" alt="Hero Image">
            </div>
        </div>
    </div>
</section>

<!-- Sales Banner Section -->
@include('components.sale-banner')

<!-- Features Section -->
<section id="features" class="py-5 mb-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-5 fw-bold">Why Shop With Us?</h2>
                <p class="lead text-muted">Experience the best in online shopping</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 text-center h-100">
                    <div class="card-body">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-truck text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title">Free Shipping</h5>
                        <p class="card-text text-muted">Free shipping on all orders over ₹500. Fast and reliable delivery.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 text-center h-100">
                    <div class="card-body">
                        <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-shield-check text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title">Secure Payment</h5>
                        <p class="card-text text-muted">Your payment information is safe and secure with our encryption.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 text-center h-100">
                    <div class="card-body">
                        <div class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-arrow-return-left text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title">Easy Returns</h5>
                        <p class="card-text text-muted">Not satisfied? Return your items within 7 days for a full refund.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 text-center h-100">
                    <div class="card-body">
                        <div class="bg-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-headset text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title">24/7 Support</h5>
                        <p class="card-text text-muted">Our customer support team is here to help you anytime, anywhere.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold">Featured Products</h2>
                <p class="lead text-muted">Check out our most popular items</p>
            </div>
        </div>
        
        <div class="row g-4">
            @for($i = 1; $i <= 8; $i++)
            <div class="col-lg-3 col-md-6">
                <div class="card product-card h-100 shadow-sm">
                    <div class="position-relative">
                        <img src="https://via.placeholder.com/300x300/f8f9fa/6c757d?text=Product+{{ $i }}" 
                             class="card-img-top product-image" alt="Product {{ $i }}">
                        <button class="btn btn-outline-danger btn-sm position-absolute top-0 end-0 m-2">
                            <i class="bi bi-heart"></i>
                        </button>
                        @if($i % 3 == 0)
                        <span class="badge bg-success position-absolute top-0 start-0 m-2">20% OFF</span>
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">Sample Product {{ $i }}</h6>
                        <small class="text-muted mb-2">Brand Name</small>
                        <div class="mb-2">
                            <span class="text-warning">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star"></i>
                            </span>
                            <small class="text-muted">(4.0)</small>
                        </div>
                        <div class="mb-3">
                            <h5 class="text-primary fw-bold mb-0">₹{{ number_format(rand(500, 5000), 2) }}</h5>
                            @if($i % 3 == 0)
                            <small class="text-muted text-decoration-line-through">₹{{ number_format(rand(6000, 8000), 2) }}</small>
                            @endif
                        </div>
                        <div class="mt-auto">
                            <div class="d-grid gap-2">
                                <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </a>
                                <button class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-cart-plus me-1"></i>Quick Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endfor
        </div>
        
        <div class="text-center mt-5">
            <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-grid me-2"></i>View All Products
            </a>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h3 class="fw-bold mb-3">Stay Updated</h3>
                <p class="text-muted mb-4">Subscribe to our newsletter for exclusive offers and new product updates.</p>
                <form class="row g-2 justify-content-center">
                    <div class="col-md-8">
                        <input type="email" class="form-control" placeholder="Enter your email address">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@if (session('status'))
<div class="position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050;">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Newsletter subscription
    $('form').on('submit', function(e) {
        e.preventDefault();
        const email = $(this).find('input[type="email"]').val();
        if (email) {
            toastr.success('Thank you for subscribing!');
            $(this).find('input[type="email"]').val('');
        } else {
            toastr.error('Please enter a valid email address.');
        }
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 600);
        }
    });
});
</script>
@endsection