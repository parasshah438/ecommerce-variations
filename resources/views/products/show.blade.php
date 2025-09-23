@extends('layouts.frontend')

@section('title', $product->name . ' - ' . config('app.name'))

@section('breadcrumb')
<div class="bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                @if($product->category)
                <li class="breadcrumb-item"><a href="#">{{ $product->category->name }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ $product->name }}</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid px-md-5">
    <div class="row">
        <!-- Product Images Gallery -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="sticky-top" style="top: 120px;">
                <!-- Main Image Display -->
                <div id="product-gallery" class="mb-3 position-relative">
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="min-height: 450px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <!-- Image Navigation Arrows -->
                    <button class="btn btn-dark btn-sm position-absolute top-50 start-0 translate-middle-y ms-2 d-none" id="prev-image">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn btn-dark btn-sm position-absolute top-50 end-0 translate-middle-y me-2 d-none" id="next-image">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                
                <!-- Thumbnail Navigation -->
                <div id="thumbnails-container" class="position-relative">
                    <div id="thumbnails" class="d-flex gap-2 overflow-auto pb-2" style="scroll-behavior: smooth;"></div>
                </div>
                
                <!-- Image Counter -->
                <div class="text-center mt-2">
                    <small class="text-muted" id="image-counter">1 / 1</small>
                </div>
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="col-lg-6 col-md-12">
            <div class="product-details">
                <!-- Product Header -->
                <div class="product-header mb-4">
                    <h1 class="h2 fw-bold mb-3 text-dark">{{ $product->name }}</h1>
                    
                    <!-- Brand and Rating Row -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                        <div class="brand-info">
                            @if($product->brand)
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-award me-1"></i>{{ $product->brand->name }}
                            </span>
                            @endif
                        </div>
                        <div class="rating-info">
                            <span class="text-warning me-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= 4 ? '-fill' : '' }}"></i>
                                @endfor
                            </span>
                            <small class="text-muted">(4.2) 156 reviews</small>
                        </div>
                    </div>
                    
                    <!-- SKU -->
                    <div class="sku-info mb-3">
                        <small class="text-muted">
                            <i class="bi bi-upc-scan me-1"></i>SKU: 
                            <span id="selected-sku" class="fw-semibold text-dark">Select variation</span>
                        </small>
                    </div>

                    <!-- Debug Info (only in debug mode) -->
                    @if(config('app.debug'))
                    <div class="debug-info mb-3">
                        <small class="text-muted">
                            <strong>Debug:</strong> 
                            {{ count($variations) }} variations, 
                            {{ count($attributeGroups) }} attribute groups
                            @if(count($attributeGroups) > 0)
                                ({{ implode(', ', array_keys($attributeGroups)) }})
                            @endif
                        </small>
                    </div>
                    @endif
                </div>
                
                <!-- Price Section -->
                <div class="price-section mb-4 p-3 bg-light rounded-3">
                    <div class="d-flex align-items-baseline gap-3 flex-wrap">
                        <h3 id="product-price" class="text-primary fw-bold mb-0 h2">₹{{ number_format($product->price, 2) }}</h3>
                        
                        @if($product->mrp && $product->mrp > $product->price)
                        <span class="text-muted text-decoration-line-through h5" id="product-mrp">₹{{ number_format($product->mrp, 2) }}</span>
                        <span class="badge bg-success fs-6" id="discount-percentage">
                            {{ round((($product->mrp - $product->price) / $product->mrp) * 100) }}% OFF
                        </span>
                        @endif
                    </div>
                    
                    @if($product->mrp && $product->mrp > $product->price)
                    <small class="text-success fw-semibold mt-2 d-block" id="savings-amount">
                        <i class="bi bi-piggy-bank me-1"></i>You save ₹{{ number_format($product->mrp - $product->price, 2) }}
                    </small>
                    @endif
                </div>
                
                <!-- Stock Status Alert -->
                <div id="product-stock" class="alert d-none mb-4" role="alert"></div>
                
                <!-- Product Attributes Selection -->
                <div id="attribute-selectors" class="mb-4">
                    @if(count($attributeGroups) > 0)
                        @foreach($attributeGroups as $attrName => $options)
                        <div class="attribute-group mb-4">
                            <label class="form-label fw-semibold text-dark mb-3">
                                Choose {{ $attrName }}: <span class="selected-value text-primary" data-attr="{{ strtolower($attrName) }}"></span>
                            </label>
                            
                            @if(strtolower($attrName) === 'color')
                                {{-- Color variation with image preview (Amazon style) --}}
                                <div class="color-options d-flex flex-wrap gap-3">
                                    @foreach($options as $opt)
                                    <div class="color-option-wrapper">
                                        <button class="btn btn-outline-secondary attr-option color-option position-relative p-1" 
                                                data-attr-id="{{ $opt['attribute_id'] }}" 
                                                data-attr-name="{{ strtolower($attrName) }}"
                                                data-opt-id="{{ $opt['id'] }}"
                                                data-opt-value="{{ $opt['value'] }}"
                                                style="width: 70px; height: 70px; border-radius: 8px;"
                                                title="Select {{ $opt['value'] }}">
                                            {{-- Color preview image will be populated by JS --}}
                                            <div class="color-preview-img w-100 h-100 rounded" 
                                                 style="background: #f8f9fa; display: flex; align-items: center; justify-content: center; font-size: 10px; text-align: center;">
                                                {{ $opt['value'] }}
                                            </div>
                                            <i class="bi bi-check-circle-fill position-absolute top-0 start-100 translate-middle text-success d-none selected-check"></i>
                                        </button>
                                        <small class="d-block text-center mt-1 text-muted" style="font-size: 11px;">{{ $opt['value'] }}</small>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                {{-- Regular text-based options (Size, etc.) --}}
                                <div class="attribute-options d-flex flex-wrap gap-2">
                                    @foreach($options as $opt)
                                    <button class="btn btn-outline-secondary attr-option position-relative" 
                                            data-attr-id="{{ $opt['attribute_id'] }}" 
                                            data-attr-name="{{ strtolower($attrName) }}"
                                            data-opt-id="{{ $opt['id'] }}"
                                            data-opt-value="{{ $opt['value'] }}"
                                            title="Select {{ $opt['value'] }}">
                                        {{ $opt['value'] }}
                                        <i class="bi bi-check-circle-fill position-absolute top-0 start-100 translate-middle text-success d-none selected-check"></i>
                                    </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <!-- No Variations Available -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Single Product:</strong> This product has no variations. 
                            @if(count($variations) > 0)
                                SKU: {{ $variations[0]['sku'] ?? 'N/A' }}
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Available Variations Table (for debugging - can be hidden in production) -->
                @if(count($variations) > 0 && config('app.debug'))
                <div class="variations-debug mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#variationsTable">
                                    <i class="bi bi-table me-2"></i>All Variations ({{ count($variations) }}) - Debug Info
                                </button>
                            </h6>
                        </div>
                        <div class="collapse" id="variationsTable">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>SKU</th>
                                                <th>Price</th>
                                                <th>Stock</th>
                                                <th>Attributes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($variations as $variation)
                                            <tr class="{{ $variation['in_stock'] ? '' : 'table-secondary' }}">
                                                <td>{{ $variation['sku'] }}</td>
                                                <td>₹{{ number_format($variation['price'], 2) }}</td>
                                                <td>
                                                    @if($variation['in_stock'])
                                                        <span class="badge bg-success">{{ $variation['quantity'] }} in stock</span>
                                                    @else
                                                        <span class="badge bg-danger">Out of stock</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($variation['values']))
                                                        @php
                                                            $attrValues = \App\Models\AttributeValue::whereIn('id', $variation['values'])->with('attribute')->get();
                                                        @endphp
                                                        @foreach($attrValues as $attrValue)
                                                            <small class="badge bg-light text-dark me-1">{{ $attrValue->attribute->name }}: {{ $attrValue->value }}</small>
                                                        @endforeach
                                                    @else
                                                        <small class="text-muted">No attributes</small>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Quantity and Action Buttons -->
                <div class="purchase-section">
                    <!-- Quantity Selector -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-4 col-md-6 col-sm-6">
                            <label for="qty" class="form-label fw-semibold">Quantity</label>
                            <div class="input-group input-group-lg">
                                <button class="btn btn-outline-secondary" type="button" id="qty-minus">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input id="qty" type="number" min="1" max="10" value="1" class="form-control text-center fw-semibold" readonly />
                                <button class="btn btn-outline-secondary" type="button" id="qty-plus">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted mt-1" id="stock-limit"></small>
                        </div>
                        
                        <div class="col-lg-8 col-md-6 col-sm-6">
                            <label class="form-label fw-semibold d-block">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <!-- Add to Cart Button -->
                                <button id="add-to-cart" class="btn btn-primary btn-lg position-relative" disabled>
                                    <span class="btn-text">
                                        <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                    </span>
                                    <span class="btn-loading d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Adding...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons Row -->
                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <button id="buy-now" class="btn btn-success btn-lg w-100" disabled>
                                <i class="bi bi-lightning-fill me-2"></i>Buy Now
                            </button>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                            @auth
                            @php
                                $isWishlisted = \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists();
                            @endphp
                            <button id="wishlist-btn" class="btn btn-lg flex-fill {{ $isWishlisted ? 'btn-danger' : 'btn-outline-danger' }}" 
                                    data-product-id="{{ $product->id }}"
                                    data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}">
                                <i class="bi {{ $isWishlisted ? 'bi-heart-fill' : 'bi-heart' }} me-1 wishlist-icon"></i>
                                {{ $isWishlisted ? 'In Wishlist' : 'Add to Wishlist' }}
                            </button>
                            @else
                            <button class="btn btn-outline-danger btn-lg flex-fill wishlist-btn-guest">
                                <i class="bi bi-heart me-1"></i>Add to Wishlist
                            </button>
                            @endauth
                                <button class="btn btn-outline-secondary btn-lg" data-bs-toggle="modal" data-bs-target="#shareModal">
                                    <i class="bi bi-share"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Product Features -->
                <div class="features-section mb-4">
                    <h6 class="fw-semibold mb-3">Why buy from us?</h6>
                    <div class="row g-3">
                        <div class="col-6 col-lg-3">
                            <div class="feature-item text-center p-2">
                                <i class="bi bi-truck text-success fs-4 mb-2"></i>
                                <div>
                                    <small class="fw-semibold d-block">Free Delivery</small>
                                    <small class="text-muted">On orders ₹500+</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="feature-item text-center p-2">
                                <i class="bi bi-arrow-return-left text-warning fs-4 mb-2"></i>
                                <div>
                                    <small class="fw-semibold d-block">Easy Returns</small>
                                    <small class="text-muted">7 days return</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="feature-item text-center p-2">
                                <i class="bi bi-shield-check text-info fs-4 mb-2"></i>
                                <div>
                                    <small class="fw-semibold d-block">Warranty</small>
                                    <small class="text-muted">Manufacturer</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="feature-item text-center p-2">
                                <i class="bi bi-headset text-primary fs-4 mb-2"></i>
                                <div>
                                    <small class="fw-semibold d-block">24/7 Support</small>
                                    <small class="text-muted">Customer care</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Variation Selection Guide -->
                <div id="selection-guide" class="alert alert-warning d-none">
                    <i class="bi bi-info-circle me-2"></i>
                    <span class="guide-text">Please select all product options to continue</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Information Tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                                <i class="bi bi-file-text me-2"></i>Description
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab">
                                <i class="bi bi-list-check me-2"></i>Specifications
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                                <i class="bi bi-star me-2"></i>Reviews (156)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab">
                                <i class="bi bi-truck me-2"></i>Shipping
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="productTabsContent">
                        <div class="tab-pane fade show active" id="description" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="mb-3">Product Description</h5>
                                    <p class="text-muted lh-lg">{{ $product->description ?: 'No description available for this product.' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="mb-3">Key Features</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Premium Quality</li>
                                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Durable Materials</li>
                                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Modern Design</li>
                                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Easy to Use</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="specifications" role="tabpanel">
                            <h5 class="mb-4">Technical Specifications</h5>
                            
                            {{-- Dynamic Variation-Specific Details --}}
                            <div id="dynamic-specifications" class="mb-4">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Select a variation to see specific details
                                </div>
                            </div>
                            
                            {{-- Static Product Details --}}
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr><td class="fw-semibold">Brand</td><td>{{ $product->brand->name ?? 'N/A' }}</td></tr>
                                        <tr><td class="fw-semibold">Category</td><td>{{ $product->category->name ?? 'N/A' }}</td></tr>
                                        <tr><td class="fw-semibold">Available Variations</td><td id="spec-variations">{{ count($variations) }}</td></tr>
                                        <tr><td class="fw-semibold">SKU</td><td id="spec-sku">Select variation</td></tr>
                                        <tr><td class="fw-semibold">Material Composition</td><td id="spec-material">Premium Quality Materials</td></tr>
                                        <tr><td class="fw-semibold">Care Instructions</td><td id="spec-care">Machine wash cold, tumble dry low</td></tr>
                                        <tr><td class="fw-semibold">Country of Origin</td><td>India</td></tr>
                                        <tr><td class="fw-semibold">Weight</td><td id="spec-weight">400g (approx)</td></tr>
                                        <tr><td class="fw-semibold">Dimensions</td><td id="spec-dimensions">Standard sizing</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Amazon-style "About this item" section --}}
                            <div class="mt-4">
                                <h6 class="mb-3">About this item</h6>
                                <ul class="list-unstyled" id="about-this-item">
                                    <li class="mb-2">• <strong>Product type:</strong> <span id="item-type">{{ $product->category->name ?? 'Fashion Item' }}</span></li>
                                    <li class="mb-2">• <strong>Pattern:</strong> <span id="item-pattern">Solid/Textured</span></li>
                                    <li class="mb-2">• <strong>Occasion:</strong> <span id="item-occasion">Casual/Formal</span></li>
                                    <li class="mb-2">• <strong>Fit:</strong> <span id="item-fit">Regular/Slim Fit</span></li>
                                    <li class="mb-2">• <strong>Quality:</strong> Premium materials with attention to detail</li>
                                </ul>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="reviews" role="tabpanel">
                            <div class="reviews-section">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5>Customer Reviews</h5>
                                    <button class="btn btn-primary btn-sm">Write a Review</button>
                                </div>
                                <div class="review-placeholder text-center py-5 text-muted">
                                    <i class="bi bi-chat-quote fs-1 mb-3"></i>
                                    <p>No reviews yet. Be the first to review this product!</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="shipping" role="tabpanel">
                            <h5 class="mb-4">Shipping Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="shipping-option mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-truck text-success fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1">Standard Delivery</h6>
                                                <p class="text-muted mb-0">5-7 business days • Free on orders ₹500+</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="shipping-option mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-lightning text-warning fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1">Express Delivery</h6>
                                                <p class="text-muted mb-0">2-3 business days • ₹99 extra</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-3">Delivery Locations</h6>
                                    <p class="text-muted">We deliver to all major cities across India. Check pincode availability at checkout.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="btn btn-primary btn-sm"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="btn btn-info btn-sm"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="btn btn-success btn-sm"><i class="bi bi-whatsapp"></i></a>
                    <button class="btn btn-secondary btn-sm" onclick="copyToClipboard()"><i class="bi bi-link-45deg"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Wishlist animations for product detail page */
.wishlist-ripple-large {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(220, 53, 69, 0.2);
    transform: translate(-50%, -50%);
    animation: rippleEffectLarge 0.8s ease-out;
}

@keyframes rippleEffectLarge {
    0% {
        width: 0;
        height: 0;
        opacity: 1;
    }
    100% {
        width: 60px;
        height: 60px;
        opacity: 0;
    }
}

        #wishlist-btn.processing {
            pointer-events: none;
        }
        
        /* Complete wishlist button animations */
        #wishlist-btn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        #wishlist-btn:hover {
            transform: scale(1.05);
        }

        /* Spinning animation for loading */
        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Floating hearts animation */
        .floating-heart {
            animation: floatUp 1.2s ease-out forwards;
        }

        @keyframes floatUp {
            0% {
                opacity: 1;
                transform: scale(0.5);
            }
            50% {
                opacity: 1;
                transform: scale(1.2);
            }
            100% {
                opacity: 0;
                transform: scale(0.8) translateY(-40px);
            }
        }

        /* Wishlist counter animations */
        .wishlist-badge {
            transition: all 0.3s ease;
        }

        /* Amazon-style Color Options */
        .color-option-wrapper {
            text-align: center;
        }
        
        .color-option {
            border: 2px solid #ddd !important;
            transition: all 0.2s ease;
        }
        
        .color-option:hover {
            border-color: #007bff !important;
            transform: scale(1.05);
        }
        
        .color-option.active {
            border-color: #007bff !important;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        .color-preview-img {
            transition: all 0.2s ease;
        }
        
        /* Dynamic Specifications Styling */
        #dynamic-specifications .table {
            margin-bottom: 0;
        }
        
        #dynamic-specifications .table-light th {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        /* Animate.css integration */
        .animate__animated {
            animation-duration: 0.6s;
            animation-fill-mode: both;
        }

        .animate__pulse {
            animation-name: pulse;
        }

        .animate__bounceIn {
            animation-name: bounceIn;
        }

        @keyframes pulse {
            from {
                transform: scale3d(1, 1, 1);
            }
            50% {
                transform: scale3d(1.1, 1.1, 1.1);
            }
            to {
                transform: scale3d(1, 1, 1);
            }
        }

        @keyframes bounceIn {
            from, 20%, 40%, 60%, 80%, to {
                animation-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
            }
            0% {
                opacity: 0;
                transform: scale3d(.3, .3, .3);
            }
            20% {
                transform: scale3d(1.1, 1.1, 1.1);
            }
            40% {
                transform: scale3d(.9, .9, .9);
            }
            60% {
                opacity: 1;
                transform: scale3d(1.03, 1.03, 1.03);
            }
            80% {
                transform: scale3d(.97, .97, .97);
            }
            to {
                opacity: 1;
                transform: scale3d(1, 1, 1);
            }
        }

.floating-heart {
    animation: floatUp 1.5s ease-out forwards;
}

@keyframes floatUp {
    0% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
    50% {
        transform: scale(1.2) rotate(10deg);
        opacity: 0.8;
    }
    100% {
        transform: scale(0.8) rotate(20deg);
        opacity: 0;
    }
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endpush

@section('scripts')<script>
$(document).ready(function() {
    // Product data from backend
    const product = @json($product);
    const variations = @json($variations);
    const variationImages = @json($variationImages);
    const productImages = @json($productImages);
    
    // State management
    let selectedAttributes = {}; // attribute_id => value_id
    let selectedVariation = null;
    let currentImageIndex = 0;
    let currentImages = productImages;
    
    // Create variation lookup map
    const variationMap = {};
    variations.forEach(v => variationMap[v.id] = v);
    
    // Initialize the product page
    initializeProductPage();
    
    function initializeProductPage() {
        populateColorPreviews();
        renderImageGallery(productImages);
        updateVariationSelection();
        bindEventHandlers();
        
        // Auto-select first available variation if only one attribute group
        if (Object.keys(getAttributeGroups()).length === 1) {
            autoSelectFirstVariation();
        }
    }
    
    function populateColorPreviews() {
        // Populate color option previews with first image from each color variation
        $('.color-option').each(function() {
            const $colorBtn = $(this);
            const optId = $colorBtn.data('opt-id');
            
            // Find variations that have this color option
            const colorVariations = variations.filter(v => {
                const variationValues = v.values.map(val => parseInt(val));
                return variationValues.includes(parseInt(optId));
            });
            
            if (colorVariations.length > 0) {
                // Get the first variation with this color
                const firstVariation = colorVariations[0];
                const varImages = variationImages[firstVariation.id];
                
                if (varImages && varImages.length > 0) {
                    // Use the first image for this color variation
                    const firstImage = varImages[0];
                    $colorBtn.find('.color-preview-img').css({
                        'background-image': `url(${firstImage.path})`,
                        'background-size': 'cover',
                        'background-position': 'center',
                        'color': 'transparent'
                    }).text('');
                } else {
                    // Fallback to product images if no variation images
                    if (productImages && productImages.length > 0) {
                        $colorBtn.find('.color-preview-img').css({
                            'background-image': `url(${productImages[0].path})`,
                            'background-size': 'cover',
                            'background-position': 'center',
                            'color': 'transparent'
                        }).text('');
                    }
                }
            }
        });
    }
    
    function getAttributeGroups() {
        const groups = {};
        $('.attribute-group').each(function() {
            const $group = $(this);
            const attrName = $group.find('.attr-option').first().data('attr-name');
            groups[attrName] = $group.find('.attr-option').length;
        });
        return groups;
    }
    
    function autoSelectFirstVariation() {
        const firstOption = $('.attr-option').first();
        if (firstOption.length) {
            firstOption.click();
        }
    }
    
    function renderImageGallery(images) {
        currentImages = images;
        currentImageIndex = 0;
        
        if (!images || images.length === 0) {
            $('#product-gallery').html(`
                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="min-height: 450px;">
                    <div class="text-center">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No images available</p>
                    </div>
                </div>
            `);
            $('#thumbnails').empty();
            $('#image-counter').text('0 / 0');
            return;
        }
        
        // Render main image
        const mainImage = images[0];
        $('#product-gallery').html(`
            <div class="position-relative">
                <img src="${mainImage.path}" 
                     class="img-fluid rounded-3 w-100 main-product-image" 
                     style="min-height: 450px; object-fit: cover; cursor: zoom-in;"
                     alt="${mainImage.alt || product.name}"
                     onclick="openImageModal('${mainImage.path}')">
                ${images.length > 1 ? `
                    <button class="btn btn-dark btn-sm position-absolute top-50 start-0 translate-middle-y ms-2 image-nav-btn" id="prev-image" ${currentImageIndex === 0 ? 'disabled' : ''}>
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn btn-dark btn-sm position-absolute top-50 end-0 translate-middle-y me-2 image-nav-btn" id="next-image" ${currentImageIndex === images.length - 1 ? 'disabled' : ''}>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                ` : ''}
            </div>
        `);
        
        // Render thumbnails
        if (images.length > 1) {
            const thumbnailsHtml = images.map((img, index) => `
                <img src="${img.path}" 
                     class="img-thumbnail thumbnail-image ${index === 0 ? 'border-primary' : ''}" 
                     style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                     data-index="${index}"
                     alt="${img.alt || product.name}">
            `).join('');
            $('#thumbnails').html(thumbnailsHtml);
            
            $('.image-nav-btn').removeClass('d-none');
        } else {
            $('#thumbnails').empty();
            $('.image-nav-btn').addClass('d-none');
        }
        
        updateImageCounter();
    }
    
    function updateImageCounter() {
        $('#image-counter').text(`${currentImageIndex + 1} / ${currentImages.length}`);
    }
    
    function switchMainImage(index) {
        if (index < 0 || index >= currentImages.length) return;
        
        currentImageIndex = index;
        const image = currentImages[index];
        
        $('.main-product-image').attr('src', image.path);
        $('.thumbnail-image').removeClass('border-primary');
        $(`.thumbnail-image[data-index="${index}"]`).addClass('border-primary');
        
        // Update navigation buttons
        $('#prev-image').prop('disabled', index === 0);
        $('#next-image').prop('disabled', index === currentImages.length - 1);
        
        updateImageCounter();
    }
    
    function updateVariationSelection() {
        const matchingVariations = findMatchingVariations();
        const bestVariation = selectBestVariation(matchingVariations);
        
        selectedVariation = bestVariation;
        updateProductDetails(bestVariation);
        updateOptionStates(matchingVariations);
        updateActionButtons();
        updateSelectionGuide();
    }
    
    function findMatchingVariations() {
        return variations.filter(variation => {
            return Object.entries(selectedAttributes).every(([attrId, valueId]) => {
                // Convert variation.values to integers for comparison
                const variationValues = variation.values.map(v => parseInt(v));
                return valueId === null || variationValues.includes(parseInt(valueId));
            });
        });
    }
    
    function selectBestVariation(matchingVariations) {
        if (matchingVariations.length === 0) return null;
        
        // Prefer in-stock variations
        const inStockVariations = matchingVariations.filter(v => v.in_stock);
        return inStockVariations.length > 0 ? inStockVariations[0] : matchingVariations[0];
    }
    
    function updateProductDetails(variation) {
        if (!variation) {
            $('#selected-sku').text('Select variation');
            $('#product-price').text('₹' + parseFloat(product.price).toFixed(2));
            $('#product-stock').addClass('d-none');
            renderImageGallery(productImages);
            return;
        }
        
        // Update SKU
        $('#selected-sku').text(variation.sku);
        
        // Update price
        $('#product-price').text('₹' + parseFloat(variation.price).toFixed(2));
        
        // Update stock status
        const $stockAlert = $('#product-stock');
        if (variation.in_stock && variation.quantity > 0) {
            $stockAlert
                .removeClass('d-none alert-danger alert-warning')
                .addClass('alert-success')
                .html(`<i class="bi bi-check-circle me-2"></i><strong>In Stock</strong> - ${variation.quantity} items available`);
            
            // Update stock limit text
            $('#stock-limit').text(`Max ${Math.min(10, variation.quantity)} items`);
            $('#qty').attr('max', Math.min(10, variation.quantity));
        } else {
            $stockAlert
                .removeClass('d-none alert-success alert-warning')
                .addClass('alert-danger')
                .html(`<i class="bi bi-x-circle me-2"></i><strong>Out of Stock</strong> - Currently unavailable`);
                
            $('#stock-limit').text('Currently unavailable');
            $('#qty').attr('max', 0);
        }
        
        // Update dynamic specifications (Amazon style)
        updateDynamicSpecifications(variation);
        
        // Update images
        const variationImgs = variationImages[variation.id] || productImages;
        renderImageGallery(variationImgs);
    }
    
    function updateDynamicSpecifications(variation) {
        // Update SKU in specifications
        $('#spec-sku').text(variation.sku);
        
        // Get selected attributes for display
        const selectedAttrs = Object.entries(selectedAttributes);
        let dynamicSpecsHtml = '<div class="table-responsive"><table class="table table-bordered">';
        
        // Add variation-specific details
        dynamicSpecsHtml += '<thead class="table-light"><tr><th colspan="2">Selected Variation Details</th></tr></thead><tbody>';
        
        selectedAttrs.forEach(([attrId, valueId]) => {
            // Find attribute name and value
            $('.attr-option').each(function() {
                if ($(this).data('opt-id') == valueId) {
                    const attrName = $(this).data('attr-name');
                    const attrValue = $(this).data('opt-value');
                    dynamicSpecsHtml += `<tr><td class="fw-semibold">${attrName.charAt(0).toUpperCase() + attrName.slice(1)}</td><td>${attrValue}</td></tr>`;
                }
            });
        });
        
        dynamicSpecsHtml += `<tr><td class="fw-semibold">Price</td><td>₹${parseFloat(variation.price).toFixed(2)}</td></tr>`;
        dynamicSpecsHtml += `<tr><td class="fw-semibold">Stock Status</td><td>${variation.in_stock ? '<span class="badge bg-success">In Stock (' + variation.quantity + ')</span>' : '<span class="badge bg-danger">Out of Stock</span>'}</td></tr>`;
        dynamicSpecsHtml += '</tbody></table></div>';
        
        $('#dynamic-specifications').html(dynamicSpecsHtml);
        
        // Update "About this item" based on selection
        updateAboutThisItem(variation);
    }
    
    function updateAboutThisItem(variation) {
        const colorSelected = selectedAttributes[getColorAttributeId()];
        const sizeSelected = selectedAttributes[getSizeAttributeId()];
        
        if (colorSelected) {
            const colorValue = $('.attr-option[data-opt-id="' + colorSelected + '"]').data('opt-value');
            $('#item-pattern').text(colorValue + ' Pattern');
        }
        
        // Update fit based on size
        if (sizeSelected) {
            const sizeValue = $('.attr-option[data-opt-id="' + sizeSelected + '"]').data('opt-value');
            $('#item-fit').text(getSizeFitType(sizeValue));
        }
    }
    
    function getColorAttributeId() {
        // Find the color attribute ID
        return $('.color-option').first().data('attr-id') || null;
    }
    
    function getSizeAttributeId() {
        // Find the size attribute ID (assuming it's not color)
        let sizeAttrId = null;
        $('.attr-option').not('.color-option').each(function() {
            const attrName = $(this).data('attr-name').toLowerCase();
            if (attrName.includes('size')) {
                sizeAttrId = $(this).data('attr-id');
                return false; // break
            }
        });
        return sizeAttrId;
    }
    
    function getSizeFitType(size) {
        const sizeUpper = size.toUpperCase();
        if (['XS', 'S'].includes(sizeUpper)) return 'Slim Fit';
        if (['M', 'L'].includes(sizeUpper)) return 'Regular Fit';
        if (['XL', 'XXL'].includes(sizeUpper)) return 'Relaxed Fit';
        return 'Standard Fit';
    }
    
    function updateOptionStates(matchingVariations) {
        $('.attr-option').each(function() {
            const $btn = $(this);
            const attrId = parseInt($btn.data('attr-id'));
            const optId = parseInt($btn.data('opt-id'));
            
            // Create test selection with this option
            const testSelection = {...selectedAttributes, [attrId]: optId};
            
            // Find variations that match this test selection
            const testVariations = variations.filter(variation => {
                // For each attribute in our test selection
                return Object.entries(testSelection).every(([testAttrId, testValueId]) => {
                    const attrIdInt = parseInt(testAttrId);
                    const valueIdInt = parseInt(testValueId);
                    // Convert variation.values to integers for comparison
                    const variationValues = variation.values.map(v => parseInt(v));
                    const matches = testValueId === null || variationValues.includes(valueIdInt);
                    return matches;
                });
            });
            
            // Check if there are any in-stock variations with this option
            const hasInStockVariations = testVariations.some(v => v.in_stock);
            
            // If no attributes are selected yet, show all options that have stock
            if (Object.keys(selectedAttributes).length === 0) {
                const optionVariations = variations.filter(v => {
                    // Convert variation.values to integers for comparison
                    const variationValues = v.values.map(val => parseInt(val));
                    const includes = variationValues.includes(optId) && v.in_stock;
                    return includes;
                });
                const isEnabled = optionVariations.length > 0;
                
                $btn.prop('disabled', !isEnabled)
                    .toggleClass('btn-outline-secondary', !isEnabled)
                    .toggleClass('btn-outline-primary', isEnabled);
            } else {
                // Normal logic when some attributes are already selected
                $btn.prop('disabled', !hasInStockVariations)
                    .toggleClass('btn-outline-secondary', !hasInStockVariations)
                    .toggleClass('btn-outline-primary', hasInStockVariations);
            }
        });
    }
    
    function updateActionButtons() {
        const canAddToCart = selectedVariation && selectedVariation.in_stock && selectedVariation.quantity > 0;
        const allAttributesSelected = $('.attribute-group').length === 0 || 
            $('.attribute-group').toArray().every(group => {
                return $(group).find('.attr-option.active').length > 0;
            });
        
        $('#add-to-cart, #buy-now')
            .prop('disabled', !canAddToCart || !allAttributesSelected)
            .toggleClass('btn-secondary', !canAddToCart || !allAttributesSelected)
            .toggleClass('btn-primary', canAddToCart && allAttributesSelected);
            
        // Update button text
        if (!allAttributesSelected) {
            $('#add-to-cart .btn-text').html('<i class="bi bi-exclamation-circle me-2"></i>Select Options');
        } else if (!canAddToCart) {
            $('#add-to-cart .btn-text').html('<i class="bi bi-x-circle me-2"></i>Out of Stock');
        } else {
            $('#add-to-cart .btn-text').html('<i class="bi bi-cart-plus me-2"></i>Add to Cart');
        }
    }
    
    function updateSelectionGuide() {
        const $guide = $('#selection-guide');
        const unselectedGroups = [];
        
        $('.attribute-group').each(function() {
            const $group = $(this);
            const groupName = $group.find('label').text().replace('Choose ', '').replace(':', '');
            if ($group.find('.attr-option.active').length === 0) {
                unselectedGroups.push(groupName);
            }
        });
        
        if (unselectedGroups.length > 0) {
            $guide.removeClass('d-none')
                  .find('.guide-text')
                  .text(`Please select: ${unselectedGroups.join(', ')}`);
        } else {
            $guide.addClass('d-none');
        }
    }
    
    function bindEventHandlers() {
        // Attribute selection
        $(document).on('click', '.attr-option', function() {
            const $btn = $(this);
            const attrId = $btn.data('attr-id');
            const attrName = $btn.data('attr-name');
            const optId = $btn.data('opt-id');
            const optValue = $btn.data('opt-value');
            
            // Deselect all options in the same attribute group (by attribute name)
            const $siblings = $(`.attr-option[data-attr-name="${attrName}"]`);
            $siblings.removeClass('active btn-primary').addClass('btn-outline-secondary');
            $siblings.find('.selected-check').addClass('d-none');
            
            if (selectedAttributes[attrId] === optId) {
                // Deselect current option
                selectedAttributes[attrId] = null;
                $(`.selected-value[data-attr="${attrName}"]`).text('');
            } else {
                // Clear any previous selection for this attribute group
                Object.keys(selectedAttributes).forEach(key => {
                    const $existingBtn = $(`.attr-option[data-attr-id="${key}"][data-attr-name="${attrName}"]`);
                    if ($existingBtn.length > 0) {
                        selectedAttributes[key] = null;
                    }
                });
                
                // Select new option
                selectedAttributes[attrId] = optId;
                $btn.removeClass('btn-outline-secondary').addClass('active btn-primary');
                $btn.find('.selected-check').removeClass('d-none');
                $(`.selected-value[data-attr="${attrName}"]`).text(optValue);
            }
            
            updateVariationSelection();
        });
        
        // Quantity controls
        $('#qty-plus').click(function() {
            const $qty = $('#qty');
            const current = parseInt($qty.val()) || 1;
            const max = parseInt($qty.attr('max')) || 10;
            if (current < max) {
                $qty.val(current + 1);
            }
        });
        
        $('#qty-minus').click(function() {
            const $qty = $('#qty');
            const current = parseInt($qty.val()) || 1;
            if (current > 1) {
                $qty.val(current - 1);
            }
        });
        
        // Image navigation
        $(document).on('click', '#prev-image', function() {
            switchMainImage(currentImageIndex - 1);
        });
        
        $(document).on('click', '#next-image', function() {
            switchMainImage(currentImageIndex + 1);
        });
        
        $(document).on('click', '.thumbnail-image', function() {
            const index = parseInt($(this).data('index'));
            switchMainImage(index);
        });
        
        // Add to Cart AJAX
        $('#add-to-cart').click(function() {
            if (!selectedVariation) {
                toastr.warning('Please select all product options');
                return;
            }
            
            if (!selectedVariation.in_stock) {
                toastr.error('Selected variation is out of stock');
                return;
            }
            
            const $btn = $(this);
            const quantity = parseInt($('#qty').val()) || 1;
            
            // Show loading state
            $btn.prop('disabled', true);
            $btn.find('.btn-text').addClass('d-none');
            $btn.find('.btn-loading').removeClass('d-none');
            
            $.ajax({
                url: '{{ route("cart.add") }}',
                method: 'POST',
                data: {
                    variation_id: selectedVariation.id,
                    quantity: quantity,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Product added to cart successfully!');
                        
                        // Update cart badge
                        if (response.summary && response.summary.items) {
                            $('#cart-badge').text(response.summary.items);
                        }
                        
                        // Optional: Show cart preview
                        showCartPreview();
                        
                    } else {
                        toastr.error(response.message || 'Failed to add product to cart');
                    }
                },
                error: function(xhr) {
                    let message = 'Network error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.status === 422) {
                        message = 'Invalid product selection';
                    }
                    toastr.error(message);
                },
                complete: function() {
                    // Hide loading state
                    $btn.prop('disabled', false);
                    $btn.find('.btn-text').removeClass('d-none');
                    $btn.find('.btn-loading').addClass('d-none');
                    updateActionButtons(); // Restore proper button state
                }
            });
        });
        
        // Buy Now
        $('#buy-now').click(function() {
            if (!selectedVariation) {
                toastr.warning('Please select all product options');
                return;
            }
            
            // First add to cart, then redirect to checkout
            const quantity = parseInt($('#qty').val()) || 1;
            
            $.ajax({
                url: '{{ route("cart.add") }}',
                method: 'POST',
                data: {
                    variation_id: selectedVariation.id,
                    quantity: quantity,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = '{{ route("checkout.index") }}';
                    } else {
                        toastr.error(response.message || 'Failed to add product to cart');
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to proceed to checkout');
                }
            });
        });
        
        // Wishlist toggle for authenticated users
        $('#wishlist-btn').click(function() {
            const $btn = $(this);
            const productId = $btn.data('product-id');
            const isWishlisted = $btn.data('wishlisted') === true || $btn.data('wishlisted') === 'true';
            
            // Prevent double clicks
            if ($btn.hasClass('processing')) {
                return;
            }
            
            $btn.addClass('processing');
            
            // Add loading state
            const $icon = $btn.find('.wishlist-icon');
            const $text = $btn.contents().filter(function() {
                return this.nodeType === 3; // Text nodes
            });
            const originalIcon = $icon.attr('class');
            const originalText = $btn.text().trim();
            
            $icon.removeClass().addClass('bi bi-arrow-repeat spin');
            $btn.html('<i class="bi bi-arrow-repeat spin me-1"></i>Processing...');
            
            $.ajax({
                url: '{{ route("wishlist.toggle") }}',
                method: 'POST',
                data: {
                    product_id: productId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        if (response.added) {
                            $btn.removeClass('btn-outline-danger').addClass('btn-danger');
                            $btn.data('wishlisted', true);
                            $btn.html('<i class="bi bi-heart-fill me-1 wishlist-icon"></i>In Wishlist');
                            
                            // Show floating hearts animation
                            showWishlistAnimation($btn, 'added');
                            toastr.success('Added to wishlist');
                            
                        } else {
                            $btn.removeClass('btn-danger').addClass('btn-outline-danger');
                            $btn.data('wishlisted', false);
                            $btn.html('<i class="bi bi-heart me-1 wishlist-icon"></i>Add to Wishlist');
                            
                            toastr.info('Removed from wishlist');
                        }
                        
                        // Update wishlist counter in navigation
                        updateWishlistCounter(response.wishlist_count);
                        
                    } else {
                        // Restore original state
                        $btn.html(`<i class="${originalIcon} me-1"></i>${originalText}`);
                        toastr.error(response.message || 'Something went wrong');
                    }
                },
                error: function(xhr) {
                   
                    // Restore original state
                    $btn.html(`<i class="${originalIcon} me-1"></i>${originalText}`);
                    
                    if (xhr.status === 401) {
                        toastr.error('Please login to manage your wishlist');
                    } else {
                        toastr.error('Failed to update wishlist');
                    }
                },
                complete: function() {
                    $btn.removeClass('processing');
                }
            });
        });
        
        // Guest wishlist button
        $('.wishlist-btn-guest').click(function() {
            toastr.warning('Please login to add items to your wishlist', 'Login Required', {
                timeOut: 5000,
                onclick: function() {
                    window.location.href = '{{ route("login") }}';
                }
            });
        });
        
        // Wishlist animation functions (reuse from product listing)
        function showWishlistAnimation($btn, action) {
            if (action === 'added') {
                // Create floating hearts animation
                for (let i = 0; i < 8; i++) {
                    createFloatingHeart($btn, i);
                }
                
                // Create ripple effect
                createRippleEffect($btn);
                
                // Button pulse animation
                $btn.addClass('animate__animated animate__pulse');
                setTimeout(() => $btn.removeClass('animate__animated animate__pulse'), 600);
            }
        }
        
        function createFloatingHeart($btn, index) {
            const $heart = $('<i class="bi bi-heart-fill floating-heart"></i>');
            
            const btnOffset = $btn.offset();
            const btnWidth = $btn.outerWidth();
            const btnHeight = $btn.outerHeight();
            
            $heart.css({
                position: 'fixed',
                left: btnOffset.left + btnWidth/2,
                top: btnOffset.top + btnHeight/2,
                color: '#dc3545',
                fontSize: '1.5rem',
                zIndex: 9999,
                pointerEvents: 'none'
            });
            
            $('body').append($heart);
            
            const angle = (index * 45) * Math.PI / 180;
            const distance = 60 + Math.random() * 40;
            const endX = btnOffset.left + btnWidth/2 + Math.cos(angle) * distance;
            const endY = btnOffset.top + btnHeight/2 + Math.sin(angle) * distance - 30;
            
            $heart.animate({
                left: endX,
                top: endY,
                opacity: 0
            }, 1000 + Math.random() * 500, 'swing', function() {
                $heart.remove();
            });
        }
        
        function createRippleEffect($btn) {
            const $ripple = $('<div class="wishlist-ripple-large"></div>');
            $btn.css('position', 'relative').append($ripple);
            
            setTimeout(() => $ripple.remove(), 800);
        }
        
        function updateWishlistCounter(count) {
            const $counter = $('.wishlist-badge');
            
            if ($counter.length) {
                $counter.addClass('animate__animated animate__bounceIn');
                setTimeout(() => $counter.removeClass('animate__animated animate__bounceIn'), 600);
                
                $counter.text(count);
                
                if (count > 0) {
                    $counter.removeClass('d-none');
                } else {
                    $counter.addClass('d-none');
                }
            }
        }
    }
    
    function showCartPreview() {
        // Optional: Implement a cart preview modal or sidebar
    }
    
    function openImageModal(src) {
        const modal = $(`
            <div class="modal fade" id="imageModal" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Product Image</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-0">
                            <img src="${src}" class="img-fluid" style="max-height: 80vh; width: auto;">
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(modal);
        modal.modal('show');
        modal.on('hidden.bs.modal', function() {
            modal.remove();
        });
    }
    
    // Copy to clipboard function
    window.copyToClipboard = function() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            toastr.success('Product link copied to clipboard!');
        }).catch(function() {
            toastr.error('Failed to copy link');
        });
    };
});
</script>@endsection
