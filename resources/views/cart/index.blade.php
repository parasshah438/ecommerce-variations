@extends('layouts.frontend')

@section('title', 'Shopping Cart - ' . config('app.name'))

@section('breadcrumb')
<div class="bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Shopping Cart</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid px-md-5">
    <div class="row">
        <div class="col-lg-8 col-md-12 mb-4">
            <!-- Save For Later Section -->
            @if($saveForLaterItems->count() > 0)
            <div class="card shadow-sm mb-4" id="save-for-later-section">
                <div class="card-header bg-warning bg-opacity-10 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="save-for-later-header">
                            <i class="bi bi-bookmark-heart text-warning me-2"></i>
                            Saved For Later (<span id="save-count">{{ $saveForLaterItems->count() }}</span>)
                            <small class="text-muted">[DB Count: {{ $saveForLaterItems->count() }}, User: {{ Auth::user() ? Auth::user()->id : 'Guest' }}]</small>
                        </h5>
                        <button class="btn btn-outline-warning btn-sm" onclick="toggleSaveForLater()">
                            <i class="bi bi-chevron-up" id="save-toggle-icon"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" id="save-for-later-content">
                    <div class="row g-3">
                        @foreach($saveForLaterItems as $item)
                        @if($item->productVariation && $item->productVariation->product)
                        <div class="col-lg-6 col-md-6 col-12" data-save-item-id="{{ $item->id }}">
                            <div class="card border-0 bg-light">
                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        <div class="col-4">
                                            @php
                                                $product = $item->productVariation?->product;
                                                // Try variation images first, then fallback to product images
                                                $image = $item->productVariation?->images->first() ?? $product?->images->first();
                                            @endphp
                                            @if($image)
                                                <!-- Debug: {{ $image->path }} -->
                                                @php
                                                    // Check if it's an external URL or local path
                                                    $imageSrc = str_starts_with($image->path, 'http') 
                                                        ? $image->path 
                                                        : asset('storage/' . $image->path);
                                                @endphp
                                                <img src="{{ $imageSrc }}" 
                                                     class="img-fluid rounded" 
                                                     alt="{{ $product->name }}"
                                                     style="aspect-ratio: 1; object-fit: cover;"
                                                     onerror="console.log('Save-for-later image failed:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="bg-secondary rounded align-items-center justify-content-center" style="aspect-ratio: 1; display: none;">
                                                    <i class="bi bi-image text-white"></i>
                                                    <small class="text-white">No image</small>
                                                </div>
                                            @else
                                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="aspect-ratio: 1;">
                                                    <i class="bi bi-image text-white"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-8">
                                            <h6 class="mb-2">{{ Str::limit($product?->name ?? 'Product Unavailable', 40) }}</h6>
                                            <p class="text-muted small mb-1">{{ $item->productVariation?->sku ?? 'N/A' }}</p>
                                            <p class="fw-bold text-primary mb-2">{{ $item->formatted_price }} × {{ $item->quantity }}</p>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <button class="btn btn-primary btn-sm move-to-cart-btn" data-save-id="{{ $item->id }}">
                                                    <i class="bi bi-cart-plus"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm remove-saved-btn" data-save-id="{{ $item->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Shopping Cart Section -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary bg-opacity-10 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0" id="cart-header">
                            <i class="bi bi-cart3 text-primary me-2"></i>
                            Shopping Cart
                            @if($cartItems->count() > 0)
                                <span class="badge bg-primary" id="cart-header-badge">{{ $cartItems->count() }} {{ Str::plural('item', $cartItems->count()) }}</span>
                            @endif
                        </h4>
                        @if($cartItems->count() > 0)
                        <button class="btn btn-outline-danger btn-sm" onclick="clearCart()">
                            <i class="bi bi-trash me-1"></i>Clear Cart
                        </button>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    {{-- Cart Issues/Warnings --}}
                    @if(isset($cartIssues) && count($cartIssues) > 0)
                    <div class="alert alert-warning mb-4">
                        <h6><i class="bi bi-exclamation-triangle me-2"></i>Cart Issues</h6>
                        <ul class="mb-0">
                            @foreach($cartIssues as $issue)
                            <li>{{ $issue['message'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if($cartItems->count() > 0)
                        <div id="cart-items-container">
                            @foreach($cartItems as $item)
                            @if($item->productVariation && $item->productVariation->product)
                            <div class="cart-item border-bottom py-4" data-cart-item-id="{{ $item->id }}">
                                <div class="row g-4">
                                    <!-- Product Image -->
                                    <div class="col-lg-2 col-md-3 col-4">
                                        @php
                                            $product = $item->productVariation?->product;
                                            // Try variation images first, then fallback to product images
                                            $image = $item->productVariation?->images->first() ?? $product?->images->first();
                                        @endphp
                                        @if($image)
                                            <!-- Debug: {{ $image->path }} -->
                                            @php
                                                // Check if it's an external URL or local path
                                                $imageSrc = str_starts_with($image->path, 'http') 
                                                    ? $image->path 
                                                    : asset('storage/' . $image->path);
                                            @endphp
                                            <!-- Debug full URL: {{ $imageSrc }} -->
                                            <img src="{{ $imageSrc }}" 
                                                 class="img-fluid rounded shadow-sm" 
                                                 alt="{{ $product->name }}"
                                                 style="aspect-ratio: 1; object-fit: cover;"
                                                 onerror="console.log('Image failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="bg-light rounded align-items-center justify-content-center shadow-sm" style="aspect-ratio: 1; display: none;">
                                                <i class="bi bi-image text-muted fs-1"></i>
                                                <small class="text-muted">Image not found</small>
                                            </div>
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" style="aspect-ratio: 1;">
                                                <i class="bi bi-image text-muted fs-1"></i>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Product Details -->
                                    <div class="col-lg-4 col-md-5 col-8">
                                        <h5 class="mb-2">
                                            @if($product?->slug)
                                            <a href="{{ route('products.show', $product->slug) }}" 
                                               class="text-decoration-none text-dark">
                                                {{ $product->name }}
                                            </a>
                                            @else
                                                Product Unavailable
                                            @endif
                                        </h5>
                                        <div class="text-muted small mb-2">
                                            <p class="mb-1"><strong>SKU:</strong> {{ $item->productVariation?->sku ?? 'N/A' }}</p>
                                            @if($product?->brand)
                                            <p class="mb-1"><strong>Brand:</strong> {{ $product->brand->name }}</p>
                                            @endif
                                            
                                            <!-- Variation Attributes -->
                                            @if($item->productVariation?->attribute_value_ids)
                                                @php
                                                    $attributeIds = $item->productVariation->attribute_value_ids;
                                                    $attributeValues = \App\Models\AttributeValue::whereIn('id', $attributeIds)->with('attribute')->get();
                                                @endphp
                                                @foreach($attributeValues as $attrValue)
                                                <span class="badge bg-light text-dark me-1 mb-1">
                                                    {{ $attrValue->attribute->name }}: {{ $attrValue->value }}
                                                </span>
                                                @endforeach
                                            @endif
                                        </div>
                                        
                                        <!-- Stock Status -->
                                        @php $stock = $item->productVariation?->stock?->quantity ?? 0; @endphp
                                        <div class="stock-status mb-2">
                                            @if($stock > 0)
                                                <small class="text-success">
                                                    <i class="bi bi-check-circle me-1"></i>In Stock ({{ $stock }} available)
                                                </small>
                                            @else
                                                <small class="text-danger">
                                                    <i class="bi bi-x-circle me-1"></i>Out of Stock
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Quantity Controls -->
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <div class="quantity-section">
                                            <label class="form-label small">Quantity</label>
                                            <div class="input-group">
                                                <button class="btn btn-outline-secondary btn-sm qty-minus" 
                                                        data-cart-item-id="{{ $item->id }}">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" 
                                                       class="form-control form-control-sm text-center qty-input" 
                                                       value="{{ $item->quantity }}" 
                                                       min="1" 
                                                       max="{{ min(50, $stock) }}"
                                                       data-cart-item-id="{{ $item->id }}"
                                                       {{ $stock <= 0 ? 'disabled' : '' }}>
                                                <button class="btn btn-outline-secondary btn-sm qty-plus" 
                                                        data-cart-item-id="{{ $item->id }}">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Price and Actions -->
                                    <div class="col-lg-4 col-md-12 col-12">
                                        <div class="d-flex flex-column h-100">
                                            <div class="price-section mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small class="text-muted">Price:</small>
                                                        @php
                                                            $variation = $item->productVariation;
                                                            $originalPrice = $variation->price;
                                                            $salePrice = $item->price;
                                                            $hasSale = $variation->hasActiveSale() && $salePrice < $originalPrice;
                                                        @endphp
                                                        
                                                        @if($hasSale)
                                                            <!-- Sale Price Display -->
                                                            <div class="fw-bold text-primary">₹{{ number_format($salePrice, 2) }}</div>
                                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                                <span class="text-muted text-decoration-line-through small">₹{{ number_format($originalPrice, 2) }}</span>
                                                                <span class="badge bg-danger small">{{ round((($originalPrice - $salePrice) / $originalPrice) * 100) }}% OFF</span>
                                                            </div>
                                                            <small class="text-success">
                                                                <i class="bi bi-fire me-1"></i>Save ₹{{ number_format($originalPrice - $salePrice, 2) }}
                                                            </small>
                                                        @else
                                                            <!-- Regular Price Display -->
                                                            <div class="fw-bold text-primary">₹{{ number_format($salePrice, 2) }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="text-muted">Total:</small>
                                                        <div class="fw-bold fs-5 item-total" data-price="{{ $salePrice }}">
                                                            ₹{{ number_format($salePrice * $item->quantity, 2) }}
                                                        </div>
                                                        @if($hasSale)
                                                            <small class="text-muted text-decoration-line-through">
                                                                ₹{{ number_format($originalPrice * $item->quantity, 2) }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="mt-auto">
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <button class="btn btn-outline-warning btn-sm save-for-later-btn" 
                                                            data-cart-item-id="{{ $item->id }}">
                                                        <i class="bi bi-bookmark-heart me-1"></i>Save for Later
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm remove-item-btn" 
                                                            data-cart-item-id="{{ $item->id }}">
                                                        <i class="bi bi-trash me-1"></i>Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        
                        <!-- Continue Shopping -->
                        <div class="text-center mt-4">
                            <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    @else
                        <!-- Empty Cart -->
                        <div class="text-center py-5" id="empty-cart">
                            <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-muted">Your cart is empty</h4>
                            <p class="text-muted">Add some products to get started!</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-shop me-2"></i>Start Shopping
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Cart Summary Sidebar -->
        <div class="col-lg-4 col-md-12">
            <div class="sticky-top" style="top: 120px;">
                @if($cartItems->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calculator me-2"></i>Order Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal ({{ $cartSummary['items'] ?? 0 }} items):</span>
                            <span class="fw-bold" id="cart-subtotal">₹{{ number_format($cartSummary['subtotal'] ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span class="text-success" id="cart-shipping">
                                @if(($cartSummary['subtotal'] ?? 0) >= 500)
                                    Free
                                @else
                                    ₹{{ number_format($cartSummary['shipping_cost'] ?? 50, 2) }}
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Tax (GST):</span>
                            <span id="cart-tax">₹{{ number_format($cartSummary['tax_amount'] ?? 0, 2) }}</span>
                        </div>
                        
                        <!-- Applied Coupon Discount -->
                        @if(isset($cartSummary['coupon']) && $cartSummary['coupon'])
                        <div class="d-flex justify-content-between mb-3 text-success" id="coupon-discount-row">
                            <span>
                                <i class="bi bi-tag me-1"></i>
                                Coupon Discount ({{ $cartSummary['coupon']['code'] }}):
                                <button class="btn btn-sm btn-outline-danger ms-2" id="remove-coupon-btn" title="Remove coupon">
                                    <i class="bi bi-x"></i>
                                </button>
                            </span>
                            <span class="fw-bold" id="cart-discount">
                                -₹{{ number_format($cartSummary['discount_amount'] ?? 0, 2) }}
                            </span>
                        </div>
                        @endif
                        
                        <hr>
                        <div class="d-flex justify-content-between mb-3 fs-5">
                            <strong>Total:</strong>
                            <strong class="text-primary" id="cart-total">
                                ₹{{ number_format($cartSummary['total'] ?? 0, 2) }}
                            </strong>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                            </a>
                            <button class="btn btn-outline-secondary" id="apply-coupon-btn">
                                <i class="bi bi-tag me-2"></i>Apply Coupon
                            </button>
                        </div>
                        
                        <!-- Coupon Section -->
                        <div class="mt-3 d-none" id="coupon-section">
                            <div class="input-group">
                                <input type="text" class="form-control" id="coupon-code" placeholder="Enter coupon code">
                                <button class="btn btn-outline-primary" id="apply-coupon">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Security Features -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Secure Shopping</h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <i class="bi bi-shield-check text-success fs-4"></i>
                                <small class="d-block mt-1">Secure Payment</small>
                            </div>
                            <div class="col-4">
                                <i class="bi bi-truck text-primary fs-4"></i>
                                <small class="d-block mt-1">Free Shipping</small>
                            </div>
                            <div class="col-4">
                                <i class="bi bi-arrow-return-left text-warning fs-4"></i>
                                <small class="d-block mt-1">Easy Returns</small>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <!-- Suggested Products for Empty Cart -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-lightbulb me-2"></i>Suggested for You
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center text-muted">
                            <i class="bi bi-box-seam fs-1 mb-3"></i>
                            <p>Add items to your cart to see personalized recommendations.</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modals -->
<!-- Remove Item Modal -->
<div class="modal fade" id="removeItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>What would you like to do with this item?</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-warning" id="move-to-wishlist-btn">
                        <i class="bi bi-heart me-2"></i>Move to Wishlist
                    </button>
                    <button class="btn btn-outline-warning" id="save-for-later-modal-btn">
                        <i class="bi bi-bookmark-heart me-2"></i>Save for Later
                    </button>
                    <button class="btn btn-outline-danger" id="remove-completely-btn">
                        <i class="bi bi-trash me-2"></i>Remove Completely
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Clear Cart Modal -->
<div class="modal fade" id="clearCartModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to clear your entire cart?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-clear-cart">
                    <i class="bi bi-trash me-2"></i>Clear Cart
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentCartItemId = null;
    let isProcessing = false; // Flag to prevent multiple AJAX calls
    
    // Fix count on page load to match actual DOM elements
    // Trust server-side count instead of counting DOM elements
    fixCountsOnLoad();
    
    // Quantity controls
    $(document).on('click', '.qty-plus', function() {
        const cartItemId = $(this).data('cart-item-id');
        const $input = $(`.qty-input[data-cart-item-id="${cartItemId}"]`);
        const current = parseInt($input.val());
        const max = parseInt($input.attr('max'));
        const $button = $(this);
        
        if (current < max) {
            // Show spinner
            const originalHtml = $button.html();
            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');
            
            $input.val(current + 1);
            updateCartQuantity(cartItemId, current + 1, $button, originalHtml);
        }
    });
    
    $(document).on('click', '.qty-minus', function() {
        const cartItemId = $(this).data('cart-item-id');
        const $input = $(`.qty-input[data-cart-item-id="${cartItemId}"]`);
        const current = parseInt($input.val());
        const $button = $(this);
        
        if (current > 1) {
            // Show spinner
            const originalHtml = $button.html();
            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');
            
            $input.val(current - 1);
            updateCartQuantity(cartItemId, current - 1, $button, originalHtml);
        }
    });
    
    $(document).on('change', '.qty-input', function() {
        const cartItemId = $(this).data('cart-item-id');
        const quantity = parseInt($(this).val());
        const max = parseInt($(this).attr('max'));
        const $input = $(this);
        
        if (quantity >= 1 && quantity <= max) {
            // Add loading class to input
            $input.addClass('loading');
            updateCartQuantity(cartItemId, quantity, null, null);
            
            // Remove loading class after delay
            setTimeout(() => $input.removeClass('loading'), 1000);
        } else {
            $(this).val(quantity > max ? max : 1);
        }
    });
    
    // Remove item with options
    $(document).on('click', '.remove-item-btn', function() {
        currentCartItemId = $(this).data('cart-item-id');
        $('#removeItemModal').modal('show');
    });
    
    // Save for later
    $(document).on('click', '.save-for-later-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const cartItemId = $(this).data('cart-item-id');
        const $button = $(this);
        
        // Show spinner
        const originalHtml = $button.html();
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span>Saving...');
        
        saveForLater(cartItemId, $button, originalHtml);
    });
    
    // Save for later from modal
    $('#save-for-later-modal-btn').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $button = $(this);
        const originalHtml = $button.html();
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span>Saving...');
        
        saveForLater(currentCartItemId, $button, originalHtml);
        $('#removeItemModal').modal('hide');
    });
    
    // Move to wishlist
    $('#move-to-wishlist-btn').click(function() {
        moveToWishlist(currentCartItemId);
        $('#removeItemModal').modal('hide');
    });
    
    // Remove completely
    $('#remove-completely-btn').click(function() {
        removeItem(currentCartItemId);
        $('#removeItemModal').modal('hide');
    });
    
    // Save for later section controls
    $(document).on('click', '.move-to-cart-btn', function() {
        const saveId = $(this).data('save-id');
        const $button = $(this);
        
        // Show spinner
        const originalHtml = $button.html();
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');
        
        moveToCart(saveId, $button, originalHtml);
    });
    
    $(document).on('click', '.remove-saved-btn', function() {
        const saveId = $(this).data('save-id');
        const $button = $(this);
        
        if (confirm('Remove this item from saved items?')) {
            // Show spinner
            const originalHtml = $button.html();
            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');
            
            removeSaved(saveId, $button, originalHtml);
        }
    });
    
    // Clear cart
    window.clearCart = function() {
        $('#clearCartModal').modal('show');
    };
    
    $('#confirm-clear-cart').click(function() {
        // Clear all cart items
        $('.remove-item-btn').each(function() {
            const cartItemId = $(this).data('cart-item-id');
            removeItem(cartItemId, false);
        });
        $('#clearCartModal').modal('hide');
        location.reload();
    });
    
    // Coupon functionality
    $('#apply-coupon-btn').click(function() {
        $('#coupon-section').toggleClass('d-none');
    });
    
    $('#apply-coupon').click(function() {
        const couponCode = $('#coupon-code').val();
        if (couponCode) {
            applyCoupon(couponCode);
        }
    });
    
    // Remove coupon functionality (using event delegation)
    $(document).on('click', '#remove-coupon-btn', function() {
        removeCoupon();
    });
    
    // Allow Enter key to apply coupon
    $('#coupon-code').keypress(function(e) {
        if (e.which == 13) {
            $('#apply-coupon').click();
        }
    });
    
    // Toggle save for later section
    window.toggleSaveForLater = function() {
        const $content = $('#save-for-later-content');
        const $icon = $('#save-toggle-icon');
        
        $content.slideToggle();
        $icon.toggleClass('bi-chevron-up bi-chevron-down');
    };
    
    // AJAX Functions
    function updateCartQuantity(cartItemId, quantity, $button = null, originalHtml = null) {
        $.ajax({
            url: '{{ route("cart.update") }}',
            method: 'POST',
            data: {
                cart_item_id: cartItemId,
                quantity: quantity,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                
                // Re-enable button
                if ($button) {
                    $button.prop('disabled', false).html(originalHtml);
                }
                
                if (response.success) {
                    // Update summary with proper data
                    if (response.summary) {
                        updateCartSummary(response.summary);
                    }
                    
                    // Update individual item total
                    updateItemTotal(cartItemId, quantity);
                    
                    // Update all counts
                    updateAllCounts();
                    
                    toastr.success(response.message || 'Quantity updated');
                } else {
                    toastr.error(response.message || 'Failed to update quantity');
                }
            },
            error: function(xhr) {
                // Re-enable button
                if ($button) {
                    $button.prop('disabled', false).html(originalHtml);
                }
                toastr.error('Failed to update quantity');
                location.reload();
            }
        });
    }
    
    function saveForLater(cartItemId, $button = null, originalHtml = null) {
        if (isProcessing) {
            if ($button) {
                $button.prop('disabled', false).html(originalHtml);
            }
            return;
        }
        
        isProcessing = true;
        
        $.ajax({
            url: '{{ route("cart.save_for_later") }}',
            method: 'POST',
            data: {
                cart_item_id: cartItemId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                isProcessing = false;
                
                if (response.success) {
                    const $cartItem = $(`[data-cart-item-id="${cartItemId}"]`);
                    
                    // Get item details before removing from cart
                    const itemData = extractItemDataFromCartItem($cartItem);
                    
                    $cartItem.fadeOut(400, function() {
                        $(this).remove();
                        
                        // Update cart summary first
                        if (response.summary) {
                            updateCartSummary(response.summary);
                        }
                         
                        // Add item to save for later section
                        if (response.saved_item) {
                            addItemToSaveForLater(response.saved_item);
                        } else if (itemData) {
                            addItemToSaveForLater(itemData);
                        }
                        
                        // Update cart count only (save for later count is updated in addItemToSaveForLater)
                        const cartCount = $('.cart-item').length;
                        if (cartCount > 0) {
                            const itemText = cartCount === 1 ? 'item' : 'items';
                            $('#cart-header-badge').text(cartCount + ' ' + itemText).show();
                        } else {
                            $('#cart-header-badge').hide();
                        }
                        
                        // Check empty cart
                        checkEmptyCart();
                        
                        toastr.success(response.message || 'Item saved for later');
                    });
                } else {
                    // Re-enable button on error
                    if ($button) {
                        $button.prop('disabled', false).html(originalHtml);
                    }
                    toastr.error(response.message || 'Failed to save item');
                }
            },
            error: function(xhr) {
                isProcessing = false;
                // Re-enable button on error
                if ($button) {
                    $button.prop('disabled', false).html(originalHtml);
                }
                toastr.error('Failed to save item for later');
            }
        });
    }
    
    function moveToWishlist(cartItemId) {
        $.ajax({
            url: '{{ route("cart.move_to_wishlist") }}',
            method: 'POST',
            data: {
                cart_item_id: cartItemId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $(`[data-cart-item-id="${cartItemId}"]`).fadeOut(400, function() {
                        $(this).remove();
                        
                        // Update summary
                        if (response.summary) {
                            updateCartSummary(response.summary);
                        }
                        
                        // Check empty cart
                        checkEmptyCart();
                        
                        toastr.success(response.message || 'Item moved to wishlist');
                    });
                } else {
                    toastr.error(response.message || 'Failed to move item to wishlist');
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    toastr.error('Please login to add items to wishlist');
                } else {
                    toastr.error('Failed to move item to wishlist');
                }
            }
        });
    }
    
    function removeItem(cartItemId, showToast = true) {
        $.ajax({
            url: '{{ route("cart.remove") }}',
            method: 'POST',
            data: {
                cart_item_id: cartItemId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Remove the item with animation
                    $(`[data-cart-item-id="${cartItemId}"]`).fadeOut(400, function() {
                        $(this).remove();
                        
                        // Update summary first
                        if (response.summary) {
                            updateCartSummary(response.summary);
                        }
                        
                        // Check if cart is empty and update UI accordingly
                        checkEmptyCart();
                        
                        // Show success message
                        if (showToast) {
                            toastr.success(response.message || 'Item removed from cart');
                        }
                    });
                } else {
                    toastr.error(response.message || 'Failed to remove item');
                }
            },
            error: function(xhr) {
                toastr.error('Failed to remove item. Please try again.');
            }
        });
    }
    
    function moveToCart(saveId, $button = null, originalHtml = null) {
        $.ajax({
            url: '{{ route("cart.move_to_cart") }}',
            method: 'POST',
            data: {
                save_item_id: saveId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    const $saveItem = $(`[data-save-item-id="${saveId}"]`);
                    
                    // Get item details before removing from saved items
                    const itemData = extractItemDataFromSaveItem($saveItem);
                    
                    $saveItem.fadeOut(400, function() {
                        $(this).remove();
                        
                        // Update cart summary
                        if (response.summary) {
                            updateCartSummary(response.summary);
                        }
                        
                        // Add item to cart section
                        if (response.cart_item) {
                            addItemToCart(response.cart_item);
                        }
                        
                        // Update save for later count (cart count is updated in addItemToCart)
                        updateSaveForLaterCount();
                        
                        // Update save for later section
                        checkEmptySaveForLater();
                        
                        toastr.success(response.message);
                    });
                } else {
                    // Re-enable button on error
                    if ($button) {
                        $button.prop('disabled', false).html(originalHtml);
                    }
                    toastr.error(response.message);
                }
            },
            error: function() {
                // Re-enable button on error
                if ($button) {
                    $button.prop('disabled', false).html(originalHtml);
                }
                toastr.error('Failed to move item to cart');
            }
        });
    }
    
    function removeSaved(saveId, $button = null, originalHtml = null) {
        $.ajax({
            url: '{{ route("cart.remove_saved") }}',
            method: 'POST',
            data: {
                save_item_id: saveId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $(`[data-save-item-id="${saveId}"]`).fadeOut(function() {
                        $(this).remove();
                        checkEmptySaveForLater();
                        updateSaveForLaterCount();
                    });
                    toastr.success(response.message);
                } else {
                    // Re-enable button on error
                    if ($button) {
                        $button.prop('disabled', false).html(originalHtml);
                    }
                    toastr.error(response.message);
                }
            },
            error: function() {
                // Re-enable button on error
                if ($button) {
                    $button.prop('disabled', false).html(originalHtml);
                }
                toastr.error('Failed to remove saved item');
            }
        });
    }
    
    function applyCoupon(code) {
        if (!code.trim()) {
            toastr.error('Please enter a coupon code');
            return;
        }

        const $applyButton = $('#apply-coupon');
        $applyButton.prop('disabled', true).text('Applying...');

        $.ajax({
            url: '{{ route("coupon.apply") }}',
            method: 'POST',
            data: {
                code: code,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Coupon applied successfully!');
                    
                    // Update the cart summary with new totals
                    updateCartSummary(response.summary);
                    
                    // Show coupon discount row
                    showAppliedCoupon(response.coupon);
                    
                    // Hide coupon input section
                    $('#coupon-section').addClass('d-none');
                    $('#coupon-code').val('');
                    
                    // Change apply coupon button to show it's applied
                    $('#apply-coupon-btn').addClass('d-none');
                    
                } else {
                    toastr.error(response.message || 'Invalid coupon code');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                toastr.error(response?.message || 'Failed to apply coupon');
            },
            complete: function() {
                $applyButton.prop('disabled', false).text('Apply');
            }
        });
    }

    function removeCoupon() {
        const $removeButton = $('#remove-coupon-btn');
        $removeButton.prop('disabled', true);

        $.ajax({
            url: '{{ route("coupon.remove") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Coupon removed successfully');
                    
                    // Update cart summary
                    updateCartSummary(response.summary);
                    
                    // Hide coupon discount row
                    $('#coupon-discount-row').remove();
                    
                    // Show apply coupon button again
                    $('#apply-coupon-btn').removeClass('d-none');
                    
                } else {
                    toastr.error(response.message || 'Failed to remove coupon');
                }
            },
            error: function() {
                toastr.error('Failed to remove coupon');
            },
            complete: function() {
                $removeButton.prop('disabled', false);
            }
        });
    }

    function showAppliedCoupon(coupon) {
        // Remove existing coupon row if any
        $('#coupon-discount-row').remove();
        
        // Create new coupon discount row
        const couponRow = `
            <div class="d-flex justify-content-between mb-3 text-success" id="coupon-discount-row">
                <span>
                    <i class="bi bi-tag me-1"></i>
                    Coupon Discount (${coupon.code}):
                    <button class="btn btn-sm btn-outline-danger ms-2" id="remove-coupon-btn" title="Remove coupon">
                        <i class="bi bi-x"></i>
                    </button>
                </span>
                <span class="fw-bold" id="cart-discount">
                    -₹${coupon.discount_amount.toFixed(2)}
                </span>
            </div>
        `;
        
        // Insert before the hr element
        $('hr:last').before(couponRow);
    }
    
    function updateCartSummary(summary) {
        // Update subtotal
        $('#cart-subtotal').text('₹' + parseFloat(summary.subtotal || 0).toFixed(2));
        
        // Update shipping
        const shipping = summary.shipping_cost || (summary.subtotal >= 500 ? 0 : 50);
        $('#cart-shipping').text(shipping === 0 ? 'Free' : '₹' + shipping.toFixed(2));
        
        // Update tax
        const tax = summary.tax_amount || 0;
        $('#cart-tax').text('₹' + tax.toFixed(2));
        
        // Update discount (if exists)
        if (summary.discount_amount && summary.discount_amount > 0) {
            $('#cart-discount').text('-₹' + parseFloat(summary.discount_amount).toFixed(2));
        }
        
        // Update total
        const total = summary.total || 0;
        $('#cart-total').text('₹' + total.toFixed(2));
        
        // Update cart badge in navigation
        $('#cart-badge').text(summary.items || 0);
        
        // Update cart header item count
        const itemText = (summary.items || 0) === 1 ? 'item' : 'items';
        $('#cart-header-badge').text((summary.items || 0) + ' ' + itemText);
        
        // Show/hide cart header badge
        if ((summary.items || 0) === 0) {
            $('#cart-header-badge').hide();
        } else {
            $('#cart-header-badge').show();
        }
        
        // Update item count text in summary sidebar
        const summaryItemText = `Subtotal (${summary.items || 0} items):`;
        $('#cart-subtotal').parent().find('span:first').text(summaryItemText);
        
        // Hide/show checkout button based on cart status
        if ((summary.items || 0) === 0) {
            $('.btn[href*="checkout"]').addClass('d-none');
            $('#apply-coupon-btn').addClass('d-none');
        } else {
            $('.btn[href*="checkout"]').removeClass('d-none');
            $('#apply-coupon-btn').removeClass('d-none');
        }
    }
    
    function updateItemTotal(cartItemId, quantity) {
        const $itemTotal = $(`[data-cart-item-id="${cartItemId}"] .item-total`);
        const price = parseFloat($itemTotal.data('price'));
        const total = price * quantity;
        $itemTotal.text('₹' + total.toFixed(2));
    }
    
    function checkEmptyCart() {
        if ($('.cart-item').length === 0) {
            // Update cart items container
            $('#cart-items-container').html(`
                <div class="text-center py-5" id="empty-cart">
                    <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">Your cart is empty</h4>
                    <p class="text-muted">Add some products to get started!</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-shop me-2"></i>Start Shopping
                    </a>
                </div>
            `);
            
            // Update cart header
            $('.card-header h4').html(`
                <i class="bi bi-cart3 text-primary me-2"></i>Shopping Cart
            `);
            
            // Hide clear cart button
            $('.card-header .btn-outline-danger').hide();
            
            // Update summary with empty values
            updateCartSummary({
                items: 0,
                subtotal: 0,
                shipping_cost: 50,
                tax_amount: 0,
                total: 0
            });
            
            // Hide summary sidebar content for empty cart
            $('.card').has('#cart-subtotal').fadeOut();
        }
    }
    
    function checkEmptySaveForLater() {
        const count = $('[data-save-item-id]').length;
        if (count === 0) {
            $('#save-for-later-section').fadeOut();
        } else {
            updateSaveForLaterCount();
        }
    }
    
    // Helper function to extract item data from cart item
    function extractItemDataFromCartItem($cartItem) {
        const $img = $cartItem.find('img').first();
        const $title = $cartItem.find('h5 a').first();
        const $sku = $cartItem.find('.text-muted:contains("SKU:")').first();
        const $price = $cartItem.find('.fw-bold.text-primary').first();
        const $qty = $cartItem.find('.qty-input').first();
        
        return {
            image_url: $img.attr('src') || '',
            alt_text: $img.attr('alt') || '',
            product_name: $title.text() || '',
            product_url: $title.attr('href') || '#',
            sku: $sku.text().replace('SKU:', '').trim() || '',
            price: $price.text() || '',
            quantity: $qty.val() || 1
        };
    }
    
    // Helper function to extract item data from save item
    function extractItemDataFromSaveItem($saveItem) {
        const $img = $saveItem.find('img').first();
        const $title = $saveItem.find('h6').first();
        const $sku = $saveItem.find('.text-muted.small').first();
        const $price = $saveItem.find('.fw-bold.text-primary').first();
        
        return {
            image_url: $img.attr('src') || '',
            alt_text: $img.attr('alt') || '',
            product_name: $title.text() || '',
            sku: $sku.text() || '',
            price: $price.text() || ''
        };
    }
    
    // Add item to save for later section
    function addItemToSaveForLater(itemData) {
        // Create save for later section if it doesn't exist
        if ($('#save-for-later-section').length === 0) {
            const saveSection = `
                <div class="card shadow-sm mb-4" id="save-for-later-section">
                    <div class="card-header bg-warning bg-opacity-10 border-warning">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-bookmark-heart text-warning me-2"></i>
                                Saved For Later (<span id="save-count">0</span>)
                            </h5>
                            <button class="btn btn-outline-warning btn-sm" onclick="toggleSaveForLater()">
                                <i class="bi bi-chevron-up" id="save-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" id="save-for-later-content">
                        <div class="row g-3" id="save-items-grid"></div>
                    </div>
                </div>
            `;
            $('.col-lg-8 .card').first().before(saveSection);
        } else {
            $('#save-for-later-section').show();
        }
        
        // Create the save item HTML
        const saveItemHtml = `
            <div class="col-lg-6 col-md-6 col-12" data-save-item-id="${itemData.id || 'new-' + Date.now()}">
                <div class="card border-0 bg-light">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-4">
                                ${itemData.image_url ? 
                                    `<img src="${itemData.image_url}" class="img-fluid rounded" alt="${itemData.alt_text}" style="aspect-ratio: 1; object-fit: cover;">` :
                                    `<div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="aspect-ratio: 1;"><i class="bi bi-image text-white"></i></div>`
                                }
                            </div>
                            <div class="col-8">
                                <h6 class="mb-2">${itemData.product_name}</h6>
                                <p class="text-muted small mb-1">${itemData.sku}</p>
                                <p class="fw-bold text-primary mb-2">${itemData.price}</p>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button class="btn btn-primary btn-sm move-to-cart-btn" data-save-id="${itemData.id || 'new-' + Date.now()}">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm remove-saved-btn" data-save-id="${itemData.id || 'new-' + Date.now()}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add to save items grid
        if ($('#save-items-grid').length === 0) {
            $('#save-for-later-content .row').attr('id', 'save-items-grid');
        }
        
        // Check if item already exists to prevent duplicates
        const itemId = itemData.id || 'new-' + Date.now();
        if ($(`[data-save-item-id="${itemId}"]`).length > 0) {
            return;
        }
        
        $('#save-items-grid').prepend(saveItemHtml);
        
        // Update count
        updateSaveForLaterCount();
        
        // Animate the new item
        $(`[data-save-item-id="${itemId}"]`).hide().fadeIn(400);
    }
    
    // Add item to cart section  
    function addItemToCart(itemData) {
        // If cart is empty, we need to rebuild it
        if ($('#empty-cart').length > 0) {
            $('#cart-items-container').html('');
            
            // Show cart header with items
            $('#cart-header').html(`
                <i class="bi bi-cart3 text-primary me-2"></i>Shopping Cart
                <span class="badge bg-primary" id="cart-header-badge">1 item</span>
            `);
            
            // Show clear cart button
            $('.card-header .btn-outline-danger').show();
            
            // Show summary sidebar
            $('.card').has('#cart-subtotal').fadeIn();
        }
        
        // Create cart item HTML structure and add it
        if (itemData) {
            const cartItemHtml = createCartItemHTML(itemData);
            $('#cart-items-container').prepend(cartItemHtml);
            
            // Update cart count after adding item
            const cartCount = $('.cart-item').length;
            if (cartCount > 0) {
                const itemText = cartCount === 1 ? 'item' : 'items';
                $('#cart-header-badge').text(cartCount + ' ' + itemText).show();
            }
            
            // Animate the new item
            $(`[data-cart-item-id="${itemData.id}"]`).hide().fadeIn(400);
        }
    }
    
    // Helper function to create cart item HTML
    function createCartItemHTML(itemData) {
        return `
            <div class="cart-item border-bottom py-4" data-cart-item-id="${itemData.id}">
                <div class="row g-4">
                    <!-- Product Image -->
                    <div class="col-lg-2 col-md-3 col-4">
                        ${itemData.image_url ? 
                            `<img src="${itemData.image_url}" class="img-fluid rounded shadow-sm" alt="${itemData.alt_text}" style="aspect-ratio: 1; object-fit: cover;">` :
                            `<div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" style="aspect-ratio: 1;"><i class="bi bi-image text-muted fs-1"></i></div>`
                        }
                    </div>
                    
                    <!-- Product Details -->
                    <div class="col-lg-4 col-md-5 col-8">
                        <h5 class="mb-2">
                            <a href="#" class="text-decoration-none text-dark">
                                ${itemData.product_name}
                            </a>
                        </h5>
                        <div class="text-muted small mb-2">
                            <p class="mb-1"><strong>SKU:</strong> ${itemData.sku}</p>
                        </div>
                        
                        <!-- Stock Status -->
                        <div class="stock-status mb-2">
                            ${itemData.stock > 0 ? 
                                `<small class="text-success"><i class="bi bi-check-circle me-1"></i>In Stock (${itemData.stock} available)</small>` :
                                `<small class="text-danger"><i class="bi bi-x-circle me-1"></i>Out of Stock</small>`
                            }
                        </div>
                    </div>
                    
                    <!-- Quantity Controls -->
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="quantity-section">
                            <label class="form-label small">Quantity</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary btn-sm qty-minus" data-cart-item-id="${itemData.id}">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control form-control-sm text-center qty-input" 
                                       value="${itemData.quantity}" min="1" max="${Math.min(50, itemData.stock)}"
                                       data-cart-item-id="${itemData.id}">
                                <button class="btn btn-outline-secondary btn-sm qty-plus" data-cart-item-id="${itemData.id}">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Price and Actions -->
                    <div class="col-lg-4 col-md-12 col-12">
                        <div class="d-flex flex-column h-100">
                            <div class="price-section mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">Price:</small>
                                        <div class="fw-bold text-primary">${itemData.price}</div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">Total:</small>
                                        <div class="fw-bold fs-5 item-total" data-price="${itemData.price.replace('₹', '').replace(',', '')}">
                                            ₹${(parseFloat(itemData.price.replace('₹', '').replace(',', '')) * itemData.quantity).toFixed(2)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="mt-auto">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-outline-warning btn-sm save-for-later-btn" data-cart-item-id="${itemData.id}">
                                        <i class="bi bi-bookmark-heart me-1"></i>Save for Later
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm remove-item-btn" data-cart-item-id="${itemData.id}">
                                        <i class="bi bi-trash me-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Update save for later count
    function updateSaveForLaterCount() {
        const count = $('[data-save-item-id]').length;
        $('#save-count').text(count);
        
        // If count is 0, hide the section
        if (count === 0) {
            $('#save-for-later-section').fadeOut();
        }
    }
    
    // Update all counts manually (cart and save-for-later)
    function updateAllCounts() {
        // Update save for later count
        const saveCount = $('[data-save-item-id]').length;
        $('#save-count').text(saveCount);
        
        // Update cart items count
        const cartCount = $('.cart-item').length;
        
        // Update cart header badge
        if (cartCount > 0) {
            const itemText = cartCount === 1 ? 'item' : 'items';
            $('#cart-header-badge').text(cartCount + ' ' + itemText).show();
        } else {
            $('#cart-header-badge').hide();
        }
    }
    
    // Fix counts on page load to match actual DOM elements
    function fixCountsOnLoad() {
        setTimeout(function() {
            // Remove any duplicate save-for-later items first
            removeDuplicateSaveItems();
            
            // Use server-side count for save for later (most reliable)
            const serverSaveCount = {{ $saveForLaterItems->count() }};
            const domSaveCount = $('[data-save-item-id]').length;
            const actualCartCount = $('.cart-item').length;
         
            // Always use server count for save for later to prevent JavaScript errors
            $('#save-count').text(serverSaveCount);
            
            // If DOM count doesn't match server count, log warning
            if (domSaveCount !== serverSaveCount) {
                toastr.warning(`Save for later count mismatch! Server: ${serverSaveCount}, DOM: ${domSaveCount}`);
            }
            
            // Update cart count based on DOM (this should be accurate)
            if (actualCartCount > 0) {
                const itemText = actualCartCount === 1 ? 'item' : 'items';
                $('#cart-header-badge').text(actualCartCount + ' ' + itemText).show();
            } else {
                $('#cart-header-badge').hide();
            }
            
            // Hide save for later section if no items
            if (serverSaveCount === 0) {
                $('#save-for-later-section').hide();
            }
            
            // Also sync with server counts and clean duplicates
            syncWithServerCounts();
        }, 100); // Small delay to ensure DOM is fully loaded
    }
    
    // Remove duplicate save-for-later items based on data-save-item-id
    function removeDuplicateSaveItems() {
        const seen = new Set();
        $('[data-save-item-id]').each(function() {
            const id = $(this).attr('data-save-item-id');
            if (seen.has(id)) {
                $(this).remove();
            } else {
                seen.add(id);
            }
        });
    }
    
    // Sync counts with server and clean duplicates
    function syncWithServerCounts() {
        $.ajax({
            url: '{{ route("cart.sync_counts") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const serverSaveCount = response.save_for_later_count;
                    const serverCartCount = response.cart_count;
                    const cleanedDuplicates = response.cleaned_duplicates;
                    
                   
                    if (cleanedDuplicates > 0) {
                        
                        toastr.info(`Cleaned ${cleanedDuplicates} duplicate saved items`);
                        
                        // Reload page to refresh the cleaned data
                        setTimeout(() => location.reload(), 2000);
                    }
                }
            },
            error: function() {
              toastr.error('Failed to sync counts with server');
            }
        });
    }
});
</script>
@endsection