@forelse($products as $product)
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="card product-card h-100 shadow-sm">
            <div class="position-relative">
                @php $img = optional($product->images->first())->path ?? null; @endphp
                @if($img)
                    <img src="{{ Storage::url($img) }}" 
                         class="card-img-top product-image" 
                         style="height:200px;object-fit:cover;"
                         alt="{{ $product->name }}"
                         loading="lazy">
                @else
                    <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:200px;">
                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                    </div>
                @endif
                
                    <!-- Wishlist Button -->
                    @auth
                    @php
                        $isWishlisted = \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists();
                    @endphp
                    <button class="btn btn-sm position-absolute top-0 end-0 m-2 wishlist-btn {{ $isWishlisted ? 'btn-danger' : 'btn-outline-danger' }}" 
                            data-product-id="{{ $product->id }}"
                            data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
                            title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}">
                        <i class="bi {{ $isWishlisted ? 'bi-heart-fill' : 'bi-heart' }} wishlist-icon"></i>
                    </button>
                    @else
                    <button class="btn btn-outline-danger btn-sm position-absolute top-0 end-0 m-2 wishlist-btn-guest" 
                            title="Login to add to Wishlist">
                        <i class="bi bi-heart"></i>
                    </button>
                    @endauth
                
                <!-- Discount Badge -->
                @if($product->mrp && $product->mrp > $product->price)
                <span class="badge bg-success position-absolute top-0 start-0 m-2">
                    {{ round((($product->mrp - $product->price) / $product->mrp) * 100) }}% OFF
                </span>
                @endif
            </div>
            
            <div class="card-body d-flex flex-column">
                <h6 class="card-title mb-2">{{ Str::limit($product->name, 50) }}</h6>
                
                <!-- Category -->
                @if($product->category)
                <small class="text-muted mb-2">{{ $product->category->name }}</small>
                @endif
                
                <!-- Rating (placeholder) -->
                <div class="mb-2">
                    <span class="text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= 4 ? '-fill' : '' }}"></i>
                        @endfor
                    </span>
                    <small class="text-muted">(4.0)</small>
                </div>
                
                <!-- Price -->
                <div class="mb-3">
                    <h5 class="text-primary fw-bold mb-0">₹{{ number_format($product->price ?? 0, 2) }}</h5>
                    @if($product->mrp && $product->mrp > $product->price)
                    <small class="text-muted text-decoration-line-through">₹{{ number_format($product->mrp, 2) }}</small>
                    @endif
                </div>
                
                <!-- Actions -->
                <div class="mt-auto">
                    <div class="d-grid gap-2">
                        <a href="{{ route('products.show', $product->slug) }}" 
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-eye me-1"></i>View Details
                        </a>
                        <button class="btn btn-outline-primary btn-sm quick-add-btn" 
                                data-product-id="{{ $product->id }}">
                            <i class="bi bi-cart-plus me-1"></i>Quick Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="text-center py-5">
            <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
            <h4 class="mt-3 text-muted">No products found</h4>
            <p class="text-muted">Try adjusting your search or filter criteria</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">View All Products</a>
        </div>
    </div>
@endforelse

{{-- If paginator, render links in a hidden container used for first render only --}}
@if(method_exists($products, 'links'))
  <div id="_pagination_links" style="display:none">{!! $products->links() !!}</div>
@endif

<script>
// ===== GLOBAL WISHLIST ANIMATION FUNCTIONS =====
// These need to be in global scope so they can be called from anywhere

window.showWishlistAnimation = function($btn, action) {
    
    if (action === 'added') {
        // Ensure button is visible and has proper positioning
        if (!$btn.is(':visible')) {
            return;
        }
        
        const btnOffset = $btn.offset();
        if (!btnOffset) {
            return;
        }

        // Start heart animation
        // Create floating hearts animation
        for (let i = 0; i < 8; i++) {
            setTimeout(() => {
                window.createFloatingHeart($btn, i);
            }, i * 150);
        }
        
        // Create ripple effect
        window.createRippleEffect($btn);
        
        // Add heart explosion effect
        window.createHeartExplosion($btn);
    }
};

window.createFloatingHeart = function($btn, index) {
    // Start heart animation
    // Create floating hearts animation
    for (let i = 0; i < 8; i++) {
        setTimeout(() => {
            window.createFloatingHeart($btn, i);
        }, i * 150);
    }
    
    const $heart = $('<i class="bi bi-heart-fill floating-heart"></i>');
    
    // Get button position
    const btnOffset = $btn.offset();
    const btnWidth = $btn.outerWidth();
    const btnHeight = $btn.outerHeight();
     
    if (!btnOffset) {
        return;
    }
    
    // Random heart size and color variations
    const heartSize = 1.2 + Math.random() * 0.8;
    const colors = ['#dc3545', '#e91e63', '#ff1744', '#f44336', '#ad1457'];
    const randomColor = colors[Math.floor(Math.random() * colors.length)];
    
    $heart.css({
        position: 'fixed',
        left: btnOffset.left + btnWidth/2,
        top: btnOffset.top + btnHeight/2,
        color: randomColor,
        fontSize: heartSize + 'rem',
        zIndex: 10000,
        pointerEvents: 'none',
        textShadow: `0 0 10px ${randomColor}`,
        fontWeight: 'bold',
        transform: 'scale(0.1)',
        opacity: 1
    });
    
    $('body').append($heart);
    
    // Animate heart
    const angle = (index * 45) * Math.PI / 180;
    const distance = 50 + Math.random() * 50;
    const endX = btnOffset.left + btnWidth/2 + Math.cos(angle) * distance;
    const endY = btnOffset.top + btnHeight/2 + Math.sin(angle) * distance - 40;
    const rotation = (Math.random() - 0.5) * 180;
    
    // Initial scale animation
    $heart.animate({
        transform: 'scale(1.5)',
        opacity: 0.9
    }, 200, function() {
        // Then float away
        $heart.animate({
            left: endX,
            top: endY,
            opacity: 0,
            transform: `scale(0.8) rotate(${rotation}deg)`
        }, 1500 + Math.random() * 1000, function() {
            $heart.remove();
        });
    });
};

window.createRippleEffect = function($btn) {
    if (!$btn.length || !$btn.is(':visible')) {
        return;
    }
    
    const $ripple = $('<div class="wishlist-ripple"></div>');
    
    // Ensure button has relative positioning
    if ($btn.css('position') !== 'relative' && $btn.css('position') !== 'absolute') {
        $btn.css('position', 'relative');
    }
    
    $btn.append($ripple);
    
    setTimeout(() => $ripple.remove(), 800);
};

window.createHeartExplosion = function($btn) {
    if (!$btn.length || !$btn.is(':visible')) {
        return;
    }
    
    const btnOffset = $btn.offset();
    const btnWidth = $btn.outerWidth();
    const btnHeight = $btn.outerHeight();
    
    if (!btnOffset) {
        return;
    }
    
    // Create multiple small hearts for explosion effect
    for (let i = 0; i < 12; i++) {
        const $miniHeart = $('<span class="mini-heart">💖</span>');
        
        $miniHeart.css({
            position: 'fixed',
            left: btnOffset.left + btnWidth/2,
            top: btnOffset.top + btnHeight/2,
            fontSize: '0.8rem',
            zIndex: 10001,
            pointerEvents: 'none',
            opacity: 0.8
        });
        
        $('body').append($miniHeart);
        
        const angle = (i * 30) * Math.PI / 180;
        const distance = 20 + Math.random() * 30;
        const endX = btnOffset.left + btnWidth/2 + Math.cos(angle) * distance;
        const endY = btnOffset.top + btnHeight/2 + Math.sin(angle) * distance;
        
        $miniHeart.animate({
            left: endX,
            top: endY,
            opacity: 0
        }, 600 + Math.random() * 400, function() {
            $miniHeart.remove();
        });
    }
};

window.updateWishlistCounter = function(count) {
    
    const $counter = $('.wishlist-badge');
    
    if ($counter.length) {
        $counter.text(count);
        
        if (count > 0) {
            $counter.removeClass('d-none').show();
        } else {
            $counter.addClass('d-none').hide();
        }
        
        // Add bounce animation
        $counter.addClass('animate__animated animate__bounceIn');
        setTimeout(() => $counter.removeClass('animate__animated animate__bounceIn'), 600);
    }
};


</script>

@push('scripts')
<script>
$(document).ready(function() {
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "timeOut": "3000"
        };
    }
});

// Use event delegation on document to ensure it works for dynamically loaded content
$(document).on('click', '.wishlist-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const $btn = $(this);
    const productId = $btn.attr('data-product-id') || $btn.data('product-id');
    const isWishlisted = $btn.attr('data-wishlisted') === 'true' || $btn.data('wishlisted') === 'true';
    
    if (!productId) {
        alert('Error: No product ID found');
        return;
    }
    
    // Prevent double clicks
    if ($btn.hasClass('processing')) {
        return;
    }
    
    $btn.addClass('processing');
    
    // Add loading state
    const $icon = $btn.find('.wishlist-icon, i');
    const originalIcon = $icon.attr('class');
    $icon.removeClass().addClass('bi bi-arrow-repeat');
    $icon.css('animation', 'spin 1s linear infinite');
    
    // AJAX request
    const ajaxData = {
        product_id: productId,
        _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
    };
    
    
    $.ajax({
        url: '{{ route("wishlist.toggle") }}',
        method: 'POST',
        data: ajaxData,
        beforeSend: function(xhr) {
        },
        success: function(response) {
            if (response.success) {
                // Update button state
                if (response.added) {
                    $btn.removeClass('btn-outline-danger').addClass('btn-danger');
                    $btn.attr('data-wishlisted', 'true');
                    $btn.attr('title', 'Remove from Wishlist');
                    $icon.css('animation', '').removeClass().addClass('bi bi-heart-fill wishlist-icon text-white');
                    
                    if (typeof window.showWishlistAnimation === 'function') {
                        window.showWishlistAnimation($btn, 'added');
                    }
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.success('💖 Added to wishlist!');
                    } else {
                        alert('Added to wishlist!');
                    }
                    
                    // Animate the button
                    $btn.addClass('animate__animated animate__pulse');
                    setTimeout(() => $btn.removeClass('animate__animated animate__pulse'), 600);
                    
                } else {
                    
                    $btn.removeClass('btn-danger').addClass('btn-outline-danger');
                    $btn.attr('data-wishlisted', 'false');
                    $btn.attr('title', 'Add to Wishlist');
                    $icon.css('animation', '').removeClass().addClass('bi bi-heart wishlist-icon');
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.info('Removed from wishlist');
                    } else {
                        alert('Removed from wishlist');
                    }
                }
                
                // Update wishlist counter in navigation
                if (typeof window.updateWishlistCounter === 'function') {
                    window.updateWishlistCounter(response.wishlist_count);
                }
                
            } else {
               
                $icon.css('animation', '').removeClass().addClass(originalIcon);
                
                const message = response.message || 'Something went wrong';
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            }
        },
        error: function(xhr, status, error) {
          
            $icon.css('animation', '').removeClass().addClass(originalIcon);
            
            let errorMessage = 'Failed to update wishlist';
            if (xhr.status === 401) {
                errorMessage = 'Please login to manage your wishlist';
            } else if (xhr.status === 419) {
                errorMessage = 'Session expired. Please refresh the page.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            if (typeof toastr !== 'undefined') {
                toastr.error(errorMessage);
            } else {
                alert(errorMessage);
            }
        },
        complete: function() {
           
            $btn.removeClass('processing');
        }
    });
});

// Guest wishlist button handler
$(document).on('click', '.wishlist-btn-guest', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
   
    
    if (typeof toastr !== 'undefined') {
        toastr.warning('Please login to add items to your wishlist', 'Login Required', {
            timeOut: 5000,
            onclick: function() {
                window.location.href = '{{ route("login") }}';
            }
        });
    } else {
        if (confirm('Please login to add items to your wishlist. Go to login page?')) {
            window.location.href = '{{ route("login") }}';
        }
    }
});

// Quick add functionality
$(document).on('click', '.quick-add-btn', function(e) {
    e.preventDefault();
    const productId = $(this).data('product-id');
   
    
    // Redirect to product page for variation selection
    const productSlug = $(this).closest('.product-card').find('a[href*="/products/"]').attr('href');
    if (productSlug) {
        window.location.href = productSlug;
    } else {
        window.location.href = "{{ url('products') }}/" + productId;
    }
});

    // Load more functionality - Fixed for proper event handling
    $(document).on('click', '#load-more-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const originalText = $btn.html();
        
     
        
        $btn.html('<i class="spinner-border spinner-border-sm me-2"></i>Loading...');
        $btn.prop('disabled', true);
        
        $.get('{{ route("products.load_more") }}')
            .done(function(data) {
              
                
                const $newContent = $(data).find('.row.g-4').html();
                $('.row.g-4').append($newContent);
                
                // Debug new content
                setTimeout(function() {
                    const newWishlistBtns = $('.wishlist-btn').length;
                   
                    
                    // Test that new buttons work
                    $('.wishlist-btn').each(function(index) {
                        if (index >= 8) { // New buttons (assuming 8 per page)
                            $(this).css('border', '1px solid blue');
                            
                        }
                    });
                }, 500);
                
                if (typeof toastr !== 'undefined') {
                    toastr.success('More products loaded');
                }
            })
            .fail(function() {
               
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to load more products');
                }
            })
            .always(function() {
                $btn.html(originalText);
                $btn.prop('disabled', false);
            });
    });


</script><style>
/* Wishlist button animations */
.wishlist-btn {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.wishlist-btn:hover {
    transform: scale(1.1);
}

.wishlist-btn.processing {
    pointer-events: none;
}

/* Spinning animation for loading */
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Enhanced floating hearts animation */
.floating-heart {
    animation: floatUp 1.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    font-weight: bold;
    will-change: transform, opacity;
}

@keyframes floatUp {
    0% {
        opacity: 1;
        transform: scale(0.1) rotate(0deg);
    }
    15% {
        opacity: 1;
        transform: scale(1.3) rotate(15deg);
    }
    30% {
        opacity: 0.9;
        transform: scale(1.1) rotate(-10deg);
    }
    60% {
        opacity: 0.6;
        transform: scale(0.9) translateY(-30px) rotate(20deg);
    }
    100% {
        opacity: 0;
        transform: scale(0.3) translateY(-60px) rotate(45deg);
    }
}

/* Enhanced ripple effect */
.wishlist-ripple {
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(220, 53, 69, 0.6) 0%, rgba(220, 53, 69, 0.1) 70%, transparent 100%);
    transform: translate(-50%, -50%);
    animation: rippleEffect 0.8s cubic-bezier(0.4, 0, 0.6, 1) forwards;
    pointer-events: none;
}

@keyframes rippleEffect {
    0% {
        width: 0;
        height: 0;
        opacity: 1;
        transform: translate(-50%, -50%) scale(0);
    }
    50% {
        opacity: 0.6;
        transform: translate(-50%, -50%) scale(1);
    }
    100% {
        width: 80px;
        height: 80px;
        opacity: 0;
        transform: translate(-50%, -50%) scale(1.5);
    }
}

/* Mini hearts for explosion effect */
.mini-heart {
    font-size: 0.8rem !important;
    animation: miniHeartFloat 0.8s ease-out forwards;
    will-change: transform, opacity;
}

@keyframes miniHeartFloat {
    0% {
        opacity: 0.8;
        transform: scale(1);
    }
    50% {
        opacity: 0.6;
        transform: scale(1.2);
    }
    100% {
        opacity: 0;
        transform: scale(0.5);
    }
}
/* Wishlist counter animations */
.wishlist-badge {
    transition: all 0.3s ease;
}

/* Custom toast styles for wishlist */
.toast-success-heart {
    background-color: #dc3545 !important;
}

.toast-success-heart:before {
    content: "💖";
    font-size: 18px;
}

/* Product card hover effects */
.product-card:hover .wishlist-btn {
    transform: scale(1.05);
}

.product-card:hover .wishlist-btn.btn-danger {
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
}

/* Animate.css integration for micro-interactions */
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

.animate__fadeOut {
    animation-name: fadeOut;
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

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .floating-heart {
        font-size: 1rem;
    }
    
    .wishlist-ripple {
        max-width: 30px;
        max-height: 30px;
    }
}
</style>
@endpush