@if($similarProducts && $similarProducts->count() > 0)
<section class="similar-products-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Section Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="h4 mb-1">Similar Products</h3>
                        <p class="text-muted mb-0">Products you might also like</p>
                    </div>
                    <div class="d-none d-md-block">
                        <a href="{{ route('products.index') }}?categories[]={{ $product->category_id }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>View All
                        </a>
                    </div>
                </div>

                <!-- Products Slider -->
                <div class="similar-products-slider position-relative">
                    <!-- Navigation Buttons -->
                    <button class="btn btn-light slider-nav slider-prev" type="button" id="similarPrev">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn btn-light slider-nav slider-next" type="button" id="similarNext">
                        <i class="bi bi-chevron-right"></i>
                    </button>

                    <!-- Products Container -->
                    <div class="products-slider-container overflow-hidden">
                        <div class="products-slider-track d-flex transition-all" id="similarProductsTrack">
                            @foreach($similarProducts as $similarProduct)
                                <div class="product-slide flex-shrink-0 px-2">
                                    <div class="card product-card h-100 border-0 shadow-sm">
                                        <!-- Product Image -->
                                        <div class="product-image position-relative">
                                            <a href="{{ route('products.show', $similarProduct->slug) }}" class="d-block">
                                                @php
                                                    $similarThumbnailImage = $similarProduct->getThumbnailImage();
                                                @endphp
                                                @if($similarThumbnailImage && $similarThumbnailImage->path)
                                                    <picture>
                                                        @php
                                                            $pathInfo = pathinfo($similarThumbnailImage->path);
                                                            $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_300.webp';
                                                        @endphp
                                                        @if(Storage::disk('public')->exists($webpPath))
                                                            <source srcset="{{ Storage::disk('public')->url($webpPath) }}" type="image/webp">
                                                        @endif
                                                        <img src="{{ $similarThumbnailImage->getThumbnailUrl(300) }}" 
                                                             alt="{{ $similarProduct->name }}" 
                                                             class="card-img-top product-img"
                                                             loading="lazy"
                                                             onerror="this.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                                                    </picture>
                                                    <div class="card-img-top bg-light align-items-center justify-content-center" style="height: 200px; display: none;">
                                                        <i class="bi bi-image text-muted fs-1"></i>
                                                    </div>
                                                @else
                                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                        <i class="bi bi-image text-muted fs-1"></i>
                                                    </div>
                                                @endif
                                            </a>
                                            
                                            <!-- Discount Badge -->
                                            @if($similarProduct->mrp && $similarProduct->price < $similarProduct->mrp)
                                                @php
                                                    $discountPercent = round((($similarProduct->mrp - $similarProduct->price) / $similarProduct->mrp) * 100);
                                                @endphp
                                                @if($discountPercent > 0)
                                                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                                        {{ $discountPercent }}% OFF
                                                    </span>
                                                @endif
                                            @endif

                                            <!-- Quick View Button -->
                                            <div class="product-actions position-absolute top-0 end-0 m-2">
                                                <button class="btn btn-light btn-sm rounded-circle quick-view-btn" 
                                                        data-product-slug="{{ $similarProduct->slug }}"
                                                        title="Quick View">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Product Info -->
                                        <div class="card-body p-3">
                                            <!-- Brand -->
                                            @if($similarProduct->brand)
                                                <div class="text-muted small mb-1">{{ $similarProduct->brand->name }}</div>
                                            @endif

                                            <!-- Product Name -->
                                            <h6 class="card-title mb-2">
                                                <a href="{{ route('products.show', $similarProduct->slug) }}" 
                                                   class="text-decoration-none text-dark product-title">
                                                    {{ Str::limit($similarProduct->name, 50) }}
                                                </a>
                                            </h6>

                                            <!-- Rating -->
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="stars text-warning me-2">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= floor($similarProduct->average_rating))
                                                            <i class="bi bi-star-fill"></i>
                                                        @elseif($i <= ceil($similarProduct->average_rating))
                                                            <i class="bi bi-star-half"></i>
                                                        @else
                                                            <i class="bi bi-star"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <span class="small text-muted">
                                                    {{ $similarProduct->average_rating }} ({{ $similarProduct->reviews_count }})
                                                </span>
                                            </div>

                                            <!-- Price -->
                                            <div class="price-section mb-3">
                                                @if($similarProduct->has_variations && $similarProduct->min_price != $similarProduct->max_price)
                                                    <div class="current-price fw-bold text-primary">
                                                        ₹{{ number_format($similarProduct->min_price) }} - ₹{{ number_format($similarProduct->max_price) }}
                                                    </div>
                                                @else
                                                    <div class="current-price fw-bold text-primary">
                                                        ₹{{ number_format($similarProduct->price) }}
                                                    </div>
                                                @endif
                                                
                                                @if($similarProduct->mrp && $similarProduct->price < $similarProduct->mrp)
                                                    <div class="original-price">
                                                        <small class="text-muted text-decoration-line-through">
                                                            ₹{{ number_format($similarProduct->mrp) }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Add to Cart Button -->
                                            <div class="d-grid">
                                                <button class="btn btn-outline-primary btn-sm add-to-cart-btn" 
                                                        data-product-id="{{ $similarProduct->id }}">
                                                    <i class="bi bi-cart-plus me-1"></i>Add to Cart
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Slider Indicators -->
                    <div class="slider-indicators d-flex justify-content-center mt-3 d-md-none">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>

                <!-- View All Link for Mobile -->
                <div class="text-center mt-4 d-md-none">
                    <a href="{{ route('products.index') }}?categories[]={{ $product->category_id }}" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-1"></i>View All Similar Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Similar Products Slider Styles */
.similar-products-slider {
    position: relative;
}

.slider-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid #dee2e6;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.slider-nav:hover {
    background: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
    transform: translateY(-50%) scale(1.1);
}

.slider-prev {
    left: -20px;
}

.slider-next {
    right: -20px;
}

.products-slider-track {
    transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    will-change: transform;
}

.product-slide {
    width: 280px;
    min-width: 280px;
}

.product-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.product-image {
    overflow: hidden;
    border-radius: 0.375rem 0.375rem 0 0;
}

.product-img {
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-img {
    transform: scale(1.05);
}

.product-actions {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-actions {
    opacity: 1;
}

.quick-view-btn {
    width: 32px;
    height: 32px;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.product-title {
    transition: color 0.3s ease;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-title:hover {
    color: var(--bs-primary) !important;
}

.stars i {
    font-size: 0.75rem;
}

.add-to-cart-btn {
    transition: all 0.3s ease;
}

.add-to-cart-btn:hover {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
    transform: translateY(-1px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .slider-nav {
        display: none;
    }
    
    .product-slide {
        width: 200px;
        min-width: 200px;
    }
    
    .product-img {
        height: 150px;
    }
}

@media (max-width: 576px) {
    .product-slide {
        width: 160px;
        min-width: 160px;
    }
    
    .card-body {
        padding: 0.75rem !important;
    }
}

/* Loading Animation */
.products-slider-container.loading {
    opacity: 0.6;
    pointer-events: none;
}

.products-slider-container.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid var(--bs-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    transform: translate(-50%, -50%);
    z-index: 1000;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('similarProductsTrack');
    const prevBtn = document.getElementById('similarPrev');
    const nextBtn = document.getElementById('similarNext');
    
    if (!track || !prevBtn || !nextBtn) return;
    
    const slides = track.children;
    const slideWidth = 288; // 280px + 8px padding
    const visibleSlides = Math.floor(track.parentElement.offsetWidth / slideWidth);
    const maxTranslate = -(slides.length - visibleSlides) * slideWidth;
    
    let currentTranslate = 0;
    let autoSlideInterval;
    
    // Update button states
    function updateButtons() {
        prevBtn.disabled = currentTranslate >= 0;
        nextBtn.disabled = currentTranslate <= maxTranslate;
        
        prevBtn.style.opacity = prevBtn.disabled ? '0.5' : '1';
        nextBtn.style.opacity = nextBtn.disabled ? '0.5' : '1';
    }
    
    // Slide function
    function slide(direction) {
        const moveBy = direction === 'next' ? -slideWidth * 2 : slideWidth * 2;
        currentTranslate = Math.max(maxTranslate, Math.min(0, currentTranslate + moveBy));
        
        track.style.transform = `translateX(${currentTranslate}px)`;
        updateButtons();
    }
    
    // Event listeners
    nextBtn.addEventListener('click', () => slide('next'));
    prevBtn.addEventListener('click', () => slide('prev'));
    
    // Auto-slide functionality
    function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
            if (currentTranslate <= maxTranslate) {
                currentTranslate = 0;
            } else {
                slide('next');
            }
        }, 5000); // Slide every 5 seconds
    }
    
    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }
    
    // Touch/swipe support for mobile
    let startX = 0;
    let isDragging = false;
    
    track.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
        stopAutoSlide();
    });
    
    track.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
    });
    
    track.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        isDragging = false;
        
        const endX = e.changedTouches[0].clientX;
        const diffX = startX - endX;
        
        if (Math.abs(diffX) > 50) { // Minimum swipe distance
            if (diffX > 0) {
                slide('next');
            } else {
                slide('prev');
            }
        }
        
        startAutoSlide();
    });
    
    // Pause auto-slide on hover
    track.addEventListener('mouseenter', stopAutoSlide);
    track.addEventListener('mouseleave', startAutoSlide);
    
    // Initialize
    updateButtons();
    startAutoSlide();
    
    // Quick view functionality
    document.querySelectorAll('.quick-view-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const productSlug = this.dataset.productSlug;
            // Add your quick view modal logic here
            console.log('Quick view for:', productSlug);
        });
    });
    
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const productId = this.dataset.productId;
            const originalText = this.innerHTML;
            
            // Visual feedback
            this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Added!';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-success');
            
            // Reset after 2 seconds
            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.remove('btn-success');
                this.classList.add('btn-outline-primary');
            }, 2000);
            
            // Add your actual add to cart logic here
            console.log('Add to cart:', productId);
        });
    });
    
    // Responsive handling
    window.addEventListener('resize', function() {
        currentTranslate = 0;
        track.style.transform = 'translateX(0)';
        updateButtons();
    });
});
</script>
@endif