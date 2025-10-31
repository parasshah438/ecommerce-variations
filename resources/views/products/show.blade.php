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
                
                <!-- Product Video Section -->
                @if($product->video)
                <div class="product-video mt-4">
                    <h6 class="mb-3">
                        <i class="bi bi-play-circle me-2"></i>
                        Product Demo Video
                    </h6>
                    <div class="video-container rounded-3 overflow-hidden">
                        <video 
                            class="w-100" 
                            controls 
                            style="max-height: 300px; object-fit: cover;"
                            poster="{{ $product->images->first()?->path ? asset('storage/' . $product->images->first()->path) : '' }}">
                            <source src="{{ asset('storage/' . $product->video) }}" type="video/mp4">
                            <source src="{{ asset('storage/' . $product->video) }}" type="video/webm">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
                @endif
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
                                    <i class="bi bi-star{{ $i <= ($product->average_rating ?? 4) ? '-fill' : '' }}"></i>
                                @endfor
                            </span>
                            <small class="text-muted">({{ number_format($product->average_rating ?? 4.2, 1) }}) {{ $product->reviews_count ?? 0 }} {{ Str::plural('review', $product->reviews_count ?? 0) }}</small>
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
                    @php
                        $salePrice = $product->getBestSalePrice();
                        $hasActiveSale = $product->hasActiveSale();
                        $discountPercentage = $product->getDiscountPercentage();
                        $originalPrice = $product->price;
                        $mrp = $product->mrp;
                    @endphp
                    
                    <div class="d-flex align-items-baseline gap-3 flex-wrap">
                        <h3 id="product-price" class="text-primary fw-bold mb-0 h2">₹{{ number_format($salePrice, 2) }}</h3>
                        
                        @if($hasActiveSale && $salePrice < $originalPrice)
                        <span class="text-muted text-decoration-line-through h5" id="product-original-price">₹{{ number_format($originalPrice, 2) }}</span>
                        <span class="badge bg-danger fs-6" id="sale-discount-percentage">
                            {{ $discountPercentage }}% OFF
                        </span>
                        @elseif($mrp && $mrp > $salePrice)
                        <span class="text-muted text-decoration-line-through h5" id="product-mrp">₹{{ number_format($mrp, 2) }}</span>
                        <span class="badge bg-success fs-6" id="discount-percentage">
                            {{ round((($mrp - $salePrice) / $mrp) * 100) }}% OFF
                        </span>
                        @endif
                    </div>
                    
                    @if($hasActiveSale && $salePrice < $originalPrice)
                    <div class="mt-2">
                        <small class="text-danger fw-semibold d-block" id="sale-savings-amount">
                            <i class="bi bi-fire me-1"></i>Sale Price! You save ₹{{ number_format($originalPrice - $salePrice, 2) }}
                        </small>
                        @if($product->getActiveSale())
                        <small class="text-muted d-block">
                            <i class="bi bi-clock me-1"></i>{{ $product->getActiveSale()->name }} - Limited time offer
                        </small>
                        @endif
                    </div>
                    @elseif($mrp && $mrp > $salePrice)
                    <small class="text-success fw-semibold mt-2 d-block" id="savings-amount">
                        <i class="bi bi-piggy-bank me-1"></i>You save ₹{{ number_format($mrp - $salePrice, 2) }}
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
                                    @php
                                        $colorName = strtolower($opt['value']);
                                        $colorMap = [
                                            'white' => '#FFFFFF',
                                            'black' => '#000000',
                                            'red' => '#DC2626',
                                            'blue' => '#2563EB',
                                            'green' => '#16A34A',
                                            'yellow' => '#FACC15',
                                            'purple' => '#9333EA',
                                            'pink' => '#EC4899',
                                            'orange' => '#EA580C',
                                            'brown' => '#A3782A',
                                            'gray' => '#6B7280',
                                            'navy' => '#1E3A8A',
                                            'beige' => '#F5F5DC',
                                            'khaki' => '#F0E68C',
                                            'maroon' => '#800000',
                                            'gold' => '#FFD700',
                                            'silver' => '#C0C0C0'
                                        ];
                                        $colorCode = $colorMap[$colorName] ?? '#f8f9fa';
                                        $isWhite = $colorCode === '#FFFFFF';
                                    @endphp
                                    <div class="color-option-wrapper">
                                        <button class="btn btn-outline-secondary attr-option color-option position-relative p-1" 
                                                data-attr-id="{{ $opt['attribute_id'] }}" 
                                                data-attr-name="{{ strtolower($attrName) }}"
                                                data-opt-id="{{ $opt['id'] }}"
                                                data-opt-value="{{ $opt['value'] }}"
                                                style="width: 70px; height: 70px; border-radius: 8px;"
                                                title="Select {{ $opt['value'] }}">
                                            {{-- Color preview with direct styling --}}
                                            <div class="color-preview-img w-100 h-100 rounded" 
                                                 style="background-color: {{ $colorCode }}; background-image: none; border: 2px solid {{ $isWhite ? '#dee2e6' : '#e9ecef' }}; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; color: {{ $isWhite ? '#666' : 'transparent' }};">
                                                @if($isWhite)
                                                    W
                                                @endif
                                            </div>
                                            <i class="bi bi-check-circle-fill position-absolute top-0 start-100 translate-middle text-success d-none selected-check"></i>
                                        </button>
                                        <small class="d-block text-center mt-1 text-muted" style="font-size: 11px;">{{ $opt['value'] }}</small>
                                        @if(config('app.debug'))
                                        <small class="d-block text-center text-info" style="font-size: 9px;">ID: {{ $opt['id'] }}</small>
                                        @endif
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
                                <i class="bi bi-star me-2"></i>Reviews ({{ $product->reviews_count ?? 0 }})
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
                                    @auth
                                        <button class="btn btn-primary btn-sm" id="writeReviewBtn" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                            <i class="bi bi-pencil-square me-1"></i>Write a Review
                                        </button>
                                    @else
                                        <button class="btn btn-outline-primary btn-sm" id="writeReviewGuestBtn">
                                            <i class="bi bi-pencil-square me-1"></i>Login to Write Review
                                        </button>
                                    @endauth
                                </div>
                                
                                @if(($product->reviews_count ?? 0) > 0)
                                <!-- Reviews Summary -->
                                <div class="reviews-summary mb-4 p-3 bg-light rounded">
                                    <div class="row align-items-center">
                                        <div class="col-md-4 text-center">
                                            <h2 class="mb-1">{{ number_format($product->average_rating ?? 0, 1) }}</h2>
                                            <div class="mb-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= ($product->average_rating ?? 0) ? '-fill' : '' }} text-warning"></i>
                                                @endfor
                                            </div>
                                            <small class="text-muted">{{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }}</small>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="rating-breakdown">
                                                @for($star = 5; $star >= 1; $star--)
                                                @php $percentage = rand(10, 90); @endphp
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="me-2">{{ $star }}</span>
                                                    <i class="bi bi-star-fill text-warning me-2"></i>
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <span class="text-muted small">{{ $percentage }}%</span>
                                                </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Reviews will be loaded dynamically via JavaScript -->
                                <div class="reviews-list">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading reviews...</span>
                                        </div>
                                        <p class="text-muted mt-2">Loading reviews...</p>
                                    </div>
                                </div>
                                @else
                                <!-- Reviews will be loaded dynamically via JavaScript -->
                                <div class="reviews-list">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading reviews...</span>
                                        </div>
                                        <p class="text-muted mt-2">Loading reviews...</p>
                                    </div>
                                </div>
                                @endif
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

<!-- Write Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">
                    <i class="bi bi-star me-2"></i>Write a Review for {{ $product->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3 text-center border-end">
                            <img src="{{ $productImages[0]['path'] ?? 'https://via.placeholder.com/150' }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-fluid rounded mb-2" 
                                 style="max-height: 100px; object-fit: cover;">
                            <small class="text-muted">{{ $product->name }}</small>
                        </div>
                        <div class="col-md-9">
                            <!-- Rating Section -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Overall Rating <span class="text-danger">*</span></label>
                                <div class="rating-input d-flex align-items-center gap-2">
                                    <div class="star-rating">
                                        <i class="bi bi-star star-input" data-rating="1"></i>
                                        <i class="bi bi-star star-input" data-rating="2"></i>
                                        <i class="bi bi-star star-input" data-rating="3"></i>
                                        <i class="bi bi-star star-input" data-rating="4"></i>
                                        <i class="bi bi-star star-input" data-rating="5"></i>
                                    </div>
                                    <span class="rating-text text-muted">Select a rating</span>
                                </div>
                                <input type="hidden" name="rating" id="reviewRating" required>
                                <div class="invalid-feedback" id="rating-error"></div>
                            </div>

                            <!-- Review Title -->
                            <div class="mb-3">
                                <label for="reviewTitle" class="form-label fw-semibold">Review Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="reviewTitle" name="title" 
                                       placeholder="Summarize your review in one line" maxlength="255" required>
                                <div class="invalid-feedback" id="title-error"></div>
                            </div>

                            <!-- Review Comment -->
                            <div class="mb-3">
                                <label for="reviewComment" class="form-label fw-semibold">Your Review <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="reviewComment" name="comment" rows="4" 
                                          placeholder="Share your experience with this product..." maxlength="1000" required></textarea>
                                <div class="form-text">
                                    <span id="commentCount">0</span>/1000 characters
                                </div>
                                <div class="invalid-feedback" id="comment-error"></div>
                            </div>

                            <!-- Guidelines -->
                            <div class="alert alert-info">
                                <small>
                                    <i class="bi bi-info-circle me-1"></i>
                                    <strong>Guidelines:</strong> Please keep your review honest, helpful, and relevant. 
                                    Avoid profanity and personal information.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitReviewBtn">
                        <span class="btn-text">
                            <i class="bi bi-send me-2"></i>Submit Review
                        </span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Submitting...
                        </span>
                    </button>
                </div>
            </form>
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
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .color-option:hover .color-preview-img {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .color-option.selected {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
        }
        
        .color-option.selected .color-preview-img {
            border-color: #0d6efd;
        }
        
        .color-option.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .color-option.disabled:hover .color-preview-img {
            transform: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Dynamic Specifications Styling */
        #dynamic-specifications .table {
            margin-bottom: 0;
        }
        
        #dynamic-specifications .table-light th {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        /* Review Rating System */
        .star-rating {
            display: inline-flex;
            font-size: 1.5rem;
            gap: 0.2rem;
        }

        .star-input {
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .star-input.bi-star {
            color: #ddd;
        }

        .star-input.bi-star-fill {
            color: #ffc107;
        }

        .star-input:hover,
        .star-input.hover {
            color: #ffc107;
        }

        .star-input.selected {
            color: #ffc107;
        }

        .rating-input .rating-text {
            font-size: 0.9rem;
            min-width: 120px;
        }

        /* Review Modal Styling */
        #reviewModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        #reviewModal .modal-body .col-md-3 img {
            width: 100%;
            max-width: 120px;
            max-height: 120px;
            object-fit: cover;
        }

        #reviewModal .modal-body .border-end {
            border-color: #e9ecef !important;
            padding-right: 1rem;
        }

        /* Review Cards */
        .review-card {
            border-left: 4px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .review-card:hover {
            border-left-color: #0d6efd;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .review-rating {
            color: #ffc107;
        }

        .verified-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            font-size: 0.75rem;
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
// Global function for image modal (must be outside document.ready for onclick)
function openImageModal(src) {
    // Remove existing modal if any
    $('#imageModal').remove();
    
    const modal = $(`
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-zoom-in me-2"></i>Product Image
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-2" style="background: #f8f9fa;">
                        <div class="position-relative d-inline-block">
                            <img src="${src}" 
                                 class="img-fluid rounded shadow-sm zoom-image" 
                                 style="max-height: 85vh; max-width: 100%; cursor: zoom-in; transition: transform 0.3s ease;"
                                 id="zoomableImage">
                            <div class="position-absolute top-0 end-0 p-2">
                                <button class="btn btn-sm btn-dark rounded-pill opacity-75" 
                                        onclick="toggleZoom()" 
                                        id="zoomToggle" 
                                        title="Toggle Zoom">
                                    <i class="bi bi-zoom-in"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            Click the zoom button or double-click image to zoom
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `);
    
    $('body').append(modal);
    modal.modal('show');
    
    // Add zoom functionality
    let isZoomed = false;
    const img = modal.find('#zoomableImage');
    const toggleBtn = modal.find('#zoomToggle');
    
    // Double click to zoom
    img.on('dblclick', function() {
        toggleZoom();
    });
    
    // Global toggle zoom function
    window.toggleZoom = function() {
        isZoomed = !isZoomed;
        if (isZoomed) {
            img.css({
                'transform': 'scale(2)',
                'cursor': 'zoom-out'
            });
            toggleBtn.html('<i class="bi bi-zoom-out"></i>').attr('title', 'Zoom Out');
        } else {
            img.css({
                'transform': 'scale(1)',
                'cursor': 'zoom-in'
            });
            toggleBtn.html('<i class="bi bi-zoom-in"></i>').attr('title', 'Zoom In');
        }
    };
    
    modal.on('hidden.bs.modal', function() {
        modal.remove();
        // Clean up global function
        delete window.toggleZoom;
    });
}

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
        renderImageGallery(productImages);
        updateVariationSelection();
        bindEventHandlers();
        
        // Populate color previews after DOM is ready
        setTimeout(function() {
            populateColorPreviews();
        }, 100);
        
        // Auto-select first available variation if only one attribute group
        if (Object.keys(getAttributeGroups()).length === 1) {
            autoSelectFirstVariation();
        }
    }
    
    function populateColorPreviews() {
        // Define color mapping for better visual representation
        const colorMap = {
            'white': '#FFFFFF',
            'black': '#000000',
            'red': '#DC2626',
            'blue': '#2563EB',
            'green': '#16A34A',
            'yellow': '#FACC15',
            'purple': '#9333EA',
            'pink': '#EC4899',
            'orange': '#EA580C',
            'brown': '#A3782A',
            'gray': '#6B7280',
            'grey': '#6B7280',
            'navy': '#1E3A8A',
            'beige': '#F5F5DC',
            'khaki': '#F0E68C',
            'maroon': '#800000',
            'gold': '#FFD700',
            'silver': '#C0C0C0'
        };

        // Process each color option
        $('.color-option').each(function() {
            const $colorBtn = $(this);
            const $colorPreview = $colorBtn.find('.color-preview-img');
            const colorName = $colorBtn.data('opt-value').toLowerCase();
            const colorCode = colorMap[colorName] || '#f8f9fa';
            
            // Apply color styling directly
            $colorPreview.css({
                'background-color': colorCode,
                'background-image': 'none',
                'border': colorCode === '#FFFFFF' ? '2px solid #dee2e6' : '2px solid #e9ecef'
            });
            
            // Handle white color visibility
            if (colorCode === '#FFFFFF') {
                $colorPreview.css({
                    'color': '#666',
                    'font-weight': 'bold',
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'center'
                }).text('W');
            } else {
                $colorPreview.text('');
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
            // Show product-level sale price when no variation selected
            @php
                $productSalePrice = $product->getBestSalePrice();
                $productHasSale = $product->hasActiveSale();
                $productOriginalPrice = $product->price;
            @endphp
            $('#product-price').text('₹{{ number_format($productSalePrice, 2) }}');
            @if($productHasSale && $productSalePrice < $productOriginalPrice)
                $('#product-original-price').text('₹{{ number_format($productOriginalPrice, 2) }}').show();
                $('#sale-discount-percentage').text('{{ round((($productOriginalPrice - $productSalePrice) / $productOriginalPrice) * 100) }}% OFF').show();
                $('#sale-savings-amount').text('Sale Price! You save ₹{{ number_format($productOriginalPrice - $productSalePrice, 2) }}').show();
            @else
                $('#product-original-price').hide();
                $('#sale-discount-percentage').hide();
                $('#sale-savings-amount').hide();
            @endif
            $('#product-stock').addClass('d-none');
            renderImageGallery(productImages);
            return;
        }
        
        // Update SKU
        $('#selected-sku').text(variation.sku);
        
        // Update prices - check if variation has sale
        if (variation.has_sale && variation.sale_price < variation.price) {
            // Show sale price
            $('#product-price').text('₹' + parseFloat(variation.sale_price).toFixed(2));
            
            // Show original price with strikethrough
            if ($('#product-original-price').length === 0) {
                $('#product-price').after('<span class="text-muted text-decoration-line-through h5 ms-3" id="product-original-price"></span>');
            }
            $('#product-original-price').text('₹' + parseFloat(variation.price).toFixed(2)).show();
            
            // Show sale discount badge
            if ($('#sale-discount-percentage').length === 0) {
                $('#product-original-price').after('<span class="badge bg-danger fs-6 ms-2" id="sale-discount-percentage"></span>');
            }
            $('#sale-discount-percentage').text(variation.discount_percentage + '% OFF').show();
            
            // Show savings amount
            const savings = variation.price - variation.sale_price;
            if ($('#sale-savings-amount').length === 0) {
                $('.price-section .d-flex').after('<div class="mt-2"><small class="text-danger fw-semibold d-block" id="sale-savings-amount"></small></div>');
            }
            $('#sale-savings-amount').html('<i class="bi bi-fire me-1"></i>Sale Price! You save ₹' + savings.toFixed(2)).show();
            
            // Hide regular MRP elements if they exist
            $('#product-mrp, #discount-percentage, #savings-amount').hide();
        } else {
            // Show regular price
            $('#product-price').text('₹' + parseFloat(variation.price).toFixed(2));
            
            // Hide sale elements
            $('#product-original-price, #sale-discount-percentage, #sale-savings-amount').hide();
            
            // Show MRP if it exists and is higher than variation price
            if (variation.mrp && variation.mrp > variation.price) {
                if ($('#product-mrp').length === 0) {
                    $('#product-price').after('<span class="text-muted text-decoration-line-through h5 ms-3" id="product-mrp"></span>');
                }
                $('#product-mrp').text('₹' + parseFloat(variation.mrp).toFixed(2)).show();
                
                if ($('#discount-percentage').length === 0) {
                    $('#product-mrp').after('<span class="badge bg-success fs-6 ms-2" id="discount-percentage"></span>');
                }
                const discountPerc = Math.round(((variation.mrp - variation.price) / variation.mrp) * 100);
                $('#discount-percentage').text(discountPerc + '% OFF').show();
                
                if ($('#savings-amount').length === 0) {
                    $('.price-section .d-flex').after('<div class="mt-2"><small class="text-success fw-semibold d-block" id="savings-amount"></small></div>');
                }
                const savings = variation.mrp - variation.price;
                $('#savings-amount').html('<i class="bi bi-piggy-bank me-1"></i>You save ₹' + savings.toFixed(2)).show();
            } else {
                $('#product-mrp, #discount-percentage, #savings-amount').hide();
            }
        }
        
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
            const hasInStockVariations = testVariations.some(v => v.in_stock && v.quantity > 0);
            
            if (Object.keys(selectedAttributes).length === 0) {
                // Initial state - enable if any variations exist with this option
                const optionVariations = variations.filter(variation => {
                    const variationValues = variation.values.map(v => parseInt(v));
                    return variationValues.includes(optId);
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
                    if (xhr.status === 401) {
                        handleLoginRequired('add items to your cart');
                        return;
                    }
                    
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
                    if (xhr.status === 401) {
                        handleLoginRequired('proceed with checkout');
                        return;
                    }
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
                        handleLoginRequired('manage your wishlist');
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
    
    // Copy to clipboard function
    window.copyToClipboard = function() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            toastr.success('Product link copied to clipboard!');
        }).catch(function() {
            toastr.error('Failed to copy link');
        });
    };
    
    // =======================
    // REVIEW FUNCTIONALITY
    // =======================
    
    let selectedRating = 0;
    const ratingLabels = {
        1: 'Poor',
        2: 'Fair', 
        3: 'Good',
        4: 'Very Good',
        5: 'Excellent'
    };
    
    // Initialize reviews when page loads
    loadProductReviews();
    loadReviewStatistics();
    
    // Rating selection in modal
    $('.star-input').on('mouseenter', function() {
        const rating = $(this).data('rating');
        highlightStars(rating, 'hover');
        $('.rating-text').text(ratingLabels[rating]).removeClass('text-muted').addClass('text-warning');
    });
    
    $('.star-rating').on('mouseleave', function() {
        highlightStars(selectedRating, 'selected');
        if (selectedRating > 0) {
            $('.rating-text').text(ratingLabels[selectedRating]).removeClass('text-muted').addClass('text-warning');
        } else {
            $('.rating-text').text('Select Rating').removeClass('text-warning').addClass('text-muted');
        }
    });
    
    $('.star-input').on('click', function() {
        selectedRating = $(this).data('rating');
        $('#reviewRating').val(selectedRating);
        highlightStars(selectedRating, 'selected');
        $('.rating-text').text(ratingLabels[selectedRating]).removeClass('text-muted').addClass('text-warning');
    });
    
    function highlightStars(rating, className) {
        $('.star-input').each(function() {
            const starRating = $(this).data('rating');
            $(this).removeClass('bi-star-fill bi-star hover selected');
            
            if (starRating <= rating) {
                $(this).addClass('bi-star-fill ' + className);
            } else {
                $(this).addClass('bi-star');
            }
        });
    }
    
    // Character counter for comment
    $('#reviewComment').on('input', function() {
        const length = $(this).val().length;
        $('#commentCount').text(length);
        
        if (length > 900) {
            $('#commentCount').addClass('text-warning');
        } else if (length > 950) {
            $('#commentCount').addClass('text-danger').removeClass('text-warning');
        } else {
            $('#commentCount').removeClass('text-warning text-danger');
        }
    });
    
    // Guest user trying to write review
    $('#writeReviewGuestBtn').on('click', function() {
        toastr.info('Please login to write a review', 'Login Required');
        // Optionally redirect to login page
        // window.location.href = '/login';
    });


    // Review form submission
    $('#reviewForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.invalid-feedback').empty().hide();
        $('.form-control').removeClass('is-invalid');
        
        const $submitBtn = $('#submitReviewBtn');
        const $btnText = $submitBtn.find('.btn-text');
        const $btnLoading = $submitBtn.find('.btn-loading');
        
        // Show loading state
        $submitBtn.prop('disabled', true);
        $btnText.addClass('d-none');
        $btnLoading.removeClass('d-none');
        
        const formData = {
            rating: $('#reviewRating').val(),
            title: $('#reviewTitle').val().trim(),
            comment: $('#reviewComment').val().trim(),
            _token: $('input[name="_token"]').val()
        };
        
        // Client-side validation
        if (!formData.rating || formData.rating < 1 || formData.rating > 5) {
            showError('rating-error', 'Please select a rating');
            resetSubmitButton();
            return;
        }
        
        if (!formData.title || formData.title.length < 3) {
            showError('title-error', 'Title must be at least 3 characters long');
            $('#reviewTitle').addClass('is-invalid');
            resetSubmitButton();
            return;
        }
        
        if (!formData.comment || formData.comment.length < 10) {
            showError('comment-error', 'Review must be at least 10 characters long');
            $('#reviewComment').addClass('is-invalid');
            resetSubmitButton();
            return;
        }
        
        // Check if we're in edit mode
        const isEditMode = $('#reviewForm').data('edit-mode');
        const reviewId = $('#reviewForm').data('review-id');
        
        // Configure URL and method based on edit mode
        let ajaxConfig = {
            url: `/products/{{ $product->id }}/reviews`,
            method: 'POST',
            data: formData
        };
        
        if (isEditMode) {
            ajaxConfig.url = `/products/{{ $product->id }}/reviews/${reviewId}`;
            ajaxConfig.method = 'PUT';
        }
        
        // Submit review
        $.ajax(ajaxConfig).done(function(response) {
            if (response.success) {
                const message = isEditMode ? 'Review Updated!' : 'Review Submitted!';
                toastr.success(response.message, message);
                $('#reviewModal').modal('hide');
                resetReviewForm();
                loadProductReviews(); // Reload reviews
                loadReviewStatistics(); // Update statistics
            } else {
                toastr.error(response.message || 'Failed to submit review');
            }
            resetSubmitButton();
        }).fail(function(xhr) {
            resetSubmitButton();
            
            if (xhr.status === 401) {
                handleLoginRequired('submit a review');
                return;
            }
            
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors || {};
                if (errors.rating) showError('rating-error', errors.rating[0]);
                if (errors.title) {
                    showError('title-error', errors.title[0]);
                    $('#reviewTitle').addClass('is-invalid');
                }
                if (errors.comment) {
                    showError('comment-error', errors.comment[0]);
                    $('#reviewComment').addClass('is-invalid');
                }
                
                const message = xhr.responseJSON?.message || 'Please check your input and try again';
                toastr.error(message);
            } else {
                toastr.error('Something went wrong. Please try again.');
            }
        
        });
    });
    
    
    
    function showError(elementId, message) {
        $(`#${elementId}`).text(message).show();
    }
    
    function resetSubmitButton() {
        const $submitBtn = $('#submitReviewBtn');
        $submitBtn.prop('disabled', false);
        $submitBtn.find('.btn-text').removeClass('d-none');
        $submitBtn.find('.btn-loading').addClass('d-none');
    }
    
    function resetReviewForm() {
        $('#reviewForm')[0].reset();
        selectedRating = 0;
        $('.star-input').removeClass('hover selected');
        $('.rating-text').text('Select a rating').removeClass('text-warning').addClass('text-muted');
        $('#commentCount').text('0').removeClass('text-warning text-danger');
        $('.invalid-feedback').empty().hide();
        $('.form-control').removeClass('is-invalid');
        $('#reviewForm').removeData('edit-mode').removeData('review-id');
        $('#submitReviewBtn .btn-text').text('Submit Review');
        $('#reviewModalLabel').html('<i class="bi bi-star me-2"></i>Write a Review for {{ $product->name }}');
    }

    // Edit review functionality
    $(document).on('click', '.edit-review-btn', function() {
        const reviewId = $(this).data('review-id');
        const $reviewCard = $(this).closest('.review-card');
        
        // Extract review data from the card
        const title = $reviewCard.find('h6').text();
        const comment = $reviewCard.find('p').text();
        const rating = $reviewCard.find('.bi-star-fill').length;
        
        // Populate the form
        $('#reviewTitle').val(title);
        $('#reviewComment').val(comment);
        selectedRating = rating;
        $('#reviewRating').val(rating);
        
        // Update star display
        highlightStars(rating, 'selected');
        $('.rating-text').text(ratingLabels[rating]).removeClass('text-muted').addClass('text-warning');
        
        // Update comment counter
        $('#commentCount').text(comment.length);
        
        // Set form to edit mode
        $('#reviewForm').data('edit-mode', true).data('review-id', reviewId);
        $('#submitReviewBtn .btn-text').text('Update Review');
        $('#reviewModalLabel').html('<i class="bi bi-pencil me-2"></i>Edit Your Review');
        
        // Show the modal
        $('#reviewModal').modal('show');
    });

    // Delete review functionality
    $(document).on('click', '.delete-review-btn', function() {
        const reviewId = $(this).data('review-id');
        
        if (confirm('Are you sure you want to delete your review? This action cannot be undone.')) {
            $.ajax({
                url: `/products/{{ $product->id }}/reviews/${reviewId}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, 'Review Deleted!');
                        loadProductReviews(); // Reload reviews
                        loadReviewStatistics(); // Update statistics
                    } else {
                        toastr.error(response.message || 'Failed to delete review');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        handleLoginRequired('delete your review');
                    } else if (xhr.status === 403) {
                        toastr.error('You can only delete your own reviews');
                    } else {
                        toastr.error('Failed to delete review. Please try again.');
                    }
                }
            });
        }
    });
    
    // Load reviews for the product
    function loadProductReviews(page = 1) {
        $.get(`/products/{{ $product->id }}/reviews?page=${page}`)
            .done(function(response) {
                if (response.success) {
                    displayReviews(response.reviews, response.pagination);
                }
            })
            .fail(function() {
                console.error('Failed to load reviews');
            });
    }
    
    // Load review statistics
    function loadReviewStatistics() {
        $.get(`/products/{{ $product->id }}/reviews/statistics`)
            .done(function(response) {
                if (response.success) {
                    updateReviewStatistics(response.statistics);
                }
            })
            .fail(function() {
                console.error('Failed to load review statistics');
            });
    }
    
    function displayReviews(reviews, pagination) {
        const $reviewsList = $('.reviews-list');
        
        if (reviews.length === 0) {
            $reviewsList.html(`
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-chat-quote fs-1 mb-3 d-block"></i>
                    <p>No reviews yet. Be the first to review this product!</p>
                </div>
            `);
            return;
        }
        
        let reviewsHtml = '';
        reviews.forEach(review => {
            const canEdit = {{ Auth::check() ? 'true' : 'false' }} && review.user_id === {{ Auth::id() ?? 'null' }};
            const updatedText = review.is_updated ? ' (Updated)' : '';
            
            reviewsHtml += `
                <div class="review-card mb-4 p-3 bg-light rounded" data-review-id="${review.id}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong>${review.user_name}</strong>
                                ${review.verified_purchase ? '<span class="badge verified-badge">Verified Purchase</span>' : ''}
                            </div>
                            <div class="review-rating mb-1">
                                ${generateStarRating(review.rating)}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <small class="text-muted">${review.created_at}${updatedText}</small>
                            ${canEdit ? `
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-sm edit-review-btn" data-review-id="${review.id}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm delete-review-btn" data-review-id="${review.id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    <h6 class="mb-2">${review.title}</h6>
                    <p class="mb-0 text-dark">${review.comment}</p>
                </div>
            `;
        });
        
        // Add pagination if needed
        if (pagination.last_page > 1) {
            reviewsHtml += generatePagination(pagination);
        }
        
        $reviewsList.html(reviewsHtml);
    }
    
    function generateStarRating(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<i class="bi bi-star${i <= rating ? '-fill' : ''} text-warning"></i>`;
        }
        return stars;
    }
    
    function generatePagination(pagination) {
        if (pagination.last_page <= 1) return '';
        
        let paginationHtml = '<nav class="mt-4"><ul class="pagination justify-content-center">';
        
        // Previous button
        if (pagination.current_page > 1) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadProductReviews(${pagination.current_page - 1})">Previous</a>
            </li>`;
        }
        
        // Page numbers
        for (let i = 1; i <= pagination.last_page; i++) {
            const active = i === pagination.current_page ? 'active' : '';
            paginationHtml += `<li class="page-item ${active}">
                <a class="page-link" href="#" onclick="loadProductReviews(${i})">${i}</a>
            </li>`;
        }
        
        // Next button
        if (pagination.current_page < pagination.last_page) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadProductReviews(${pagination.current_page + 1})">Next</a>
            </li>`;
        }
        
        paginationHtml += '</ul></nav>';
        return paginationHtml;
    }
    
    function updateReviewStatistics(statistics) {
        // Update the reviews tab title
        $('#reviews-tab').html(`<i class="bi bi-star me-2"></i>Reviews (${statistics.total_reviews})`);
        
        // Update average rating display
        if (statistics.total_reviews > 0) {
            $('.reviews-summary .col-md-4 h2').text(statistics.average_rating);
            $('.reviews-summary .col-md-4 small').text(`${statistics.total_reviews} ${statistics.total_reviews === 1 ? 'review' : 'reviews'}`);
            
            // Update rating breakdown
            const $breakdown = $('.rating-breakdown');
            $breakdown.empty();
            
            for (let star = 5; star >= 1; star--) {
                const data = statistics.rating_breakdown[star];
                $breakdown.append(`
                    <div class="d-flex align-items-center mb-1">
                        <span class="me-2">${star}</span>
                        <i class="bi bi-star-fill text-warning me-2"></i>
                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: ${data.percentage}%"></div>
                        </div>
                        <span class="text-muted small">${data.percentage}%</span>
                    </div>
                `);
            }
            
            $('.reviews-summary').show();
        } else {
            $('.reviews-summary').hide();
        }
    }
    
    // Handle 401 Unauthorized errors with professional login redirect
    function handleLoginRequired(context = 'access this feature') {
        toastr.warning(`Please login to ${context}`, 'Login Required', {
            timeOut: 4000,
            onclick: function() {
                window.location.href = '{{ route("login") }}';
            }
        });
        
        // Show a more detailed message after a short delay
        setTimeout(() => {
            toastr.info('You will be redirected to login page. Click here to login now.', 'Redirecting...', {
                timeOut: 6000,
                onclick: function() {
                    window.location.href = '{{ route("login") }}';
                }
            });
        }, 1500);
        
        // Auto redirect after 5 seconds
        setTimeout(() => {
            window.location.href = '{{ route("login") }}';
        }, 5000);
    }
    
    // Reset form when modal is closed
    $('#reviewModal').on('hidden.bs.modal', function() {
        resetReviewForm();
    });
});
</script>@endsection
