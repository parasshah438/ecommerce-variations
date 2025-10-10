<div class="container-fluid px-md-4">
    <div class="row">
        <!-- Product Images Gallery -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="modal-product-gallery">
                <!-- Main Image Display -->
                <div id="product-gallery" class="mb-3 position-relative">
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="min-height: 450px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
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
                                <button class="btn btn-outline-secondary btn-lg">
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
    
    <!-- Product Description Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0">
                <div class="card-body">
                    <h5 class="mb-3">Product Description</h5>
                    <p class="text-muted lh-lg">{{ $product->description ?: 'No description available for this product.' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal specific styles */
.modal-product-gallery img {
    transition: all 0.3s ease;
}

.modal-product-gallery .main-product-image:hover {
    transform: scale(1.02);
}

.modal-thumbnail-image {
    transition: all 0.2s ease;
}

.modal-thumbnail-image:hover {
    transform: scale(1.1);
}

.modal-thumbnail-image.border-primary {
    border-width: 3px !important;
}

/* Color options in modal */
.color-option {
    transition: all 0.2s ease;
}

.color-option:hover {
    transform: scale(1.05);
}

.color-option.active {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
}

/* Attribute options */
.attr-option {
    transition: all 0.2s ease;
}

.attr-option:hover {
    transform: translateY(-2px);
}

.attr-option.active {
    transform: translateY(-2px) scale(1.05);
}

/* Purchase section animations */
.purchase-section button {
    transition: all 0.3s ease;
}

.purchase-section button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Loading states */
.btn-loading {
    pointer-events: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-product-gallery {
        margin-bottom: 2rem;
    }
    
    .color-options {
        justify-content: center;
    }
    
    .attribute-options {
        justify-content: center;
    }
}
</style>