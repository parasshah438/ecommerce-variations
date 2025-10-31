@if($products->count() > 0)
    <div class="row">
        @foreach($products as $product)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="product-card card border-0 shadow-sm h-100 position-relative">
                @php
                    // Use the best sale price which considers both variations and sales
                    $bestSalePrice = $product->getBestSalePrice();
                    $originalPrice = $product->price;
                    
                    // If product has variations, use the cheapest variation price as base
                    if ($product->variations->count() > 0) {
                        $variationPrices = $product->variations->pluck('price');
                        $originalPrice = $variationPrices->min(); // Use cheapest variation as reference
                        
                        // Calculate best sale price for variations
                        $bestVariationSalePrice = $product->variations->map(function($variation) {
                            return $variation->getBestSalePrice();
                        })->min();
                        
                        $bestSalePrice = $bestVariationSalePrice;
                    }
                    
                    $discountPercent = $bestSalePrice < $originalPrice ? 
                        round((($originalPrice - $bestSalePrice) / $originalPrice) * 100) : 0;
                @endphp
                
                @if($discountPercent > 0)
                    <div class="discount-badge">
                        <span class="badge bg-danger">{{ $discountPercent }}% OFF</span>
                    </div>
                @endif

                <!-- Product Image -->
                <div class="product-image position-relative overflow-hidden">
                    @if($product->getThumbnailImage())
                        <img src="{{ Storage::url($product->getThumbnailImage()->image_path) }}" 
                             class="card-img-top" alt="{{ $product->name }}" 
                             style="height: 200px; object-fit: cover;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        </div>
                    @endif
                    
                    <!-- Quick view overlay -->
                    <div class="product-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center opacity-0">
                        <a href="{{ route('products.show', $product->slug) }}" 
                           class="btn btn-white btn-sm fw-bold">
                            <i class="bi bi-eye me-1"></i> Quick View
                        </a>
                    </div>
                </div>

                <div class="card-body p-3">
                    <!-- Product Brand -->
                    @if($product->brand)
                        <small class="text-muted text-uppercase fw-bold">{{ $product->brand->name }}</small>
                    @endif
                    
                    <!-- Product Name -->
                    <h6 class="card-title mb-2">
                        <a href="{{ route('products.show', $product->slug) }}" 
                           class="text-decoration-none text-dark">
                            {{ Str::limit($product->name, 50) }}
                        </a>
                    </h6>

                    <!-- Price Section -->
                    <div class="price-section mb-3">
                        <div class="sale-price text-danger fw-bold">₹{{ number_format($bestSalePrice, 0) }}</div>
                        @if($bestSalePrice < $originalPrice)
                            <div class="d-flex align-items-center gap-2">
                                <span class="original-price small">₹{{ number_format($originalPrice, 0) }}</span>
                                <span class="text-success small fw-bold">Save ₹{{ number_format($originalPrice - $bestSalePrice, 0) }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Rating -->
                    @if($product->average_rating)
                        <div class="rating mb-2">
                            <div class="d-flex align-items-center">
                                <div class="stars text-warning me-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $product->average_rating)
                                            <i class="bi bi-star-fill"></i>
                                        @else
                                            <i class="bi bi-star"></i>
                                        @endif
                                    @endfor
                                </div>
                                <small class="text-muted">({{ $product->reviews_count ?? 0 }})</small>
                            </div>
                        </div>
                    @endif

                    <!-- Add to Cart Button -->
                    <div class="d-grid">
                        <button class="btn btn-primary btn-sm add-to-cart" 
                                data-product-id="{{ $product->id }}">
                            <i class="bi bi-cart-plus me-1"></i> Add to Cart
                        </button>
                    </div>
                </div>

                <!-- Sale Timer for Product -->
                <div class="card-footer bg-light border-0 py-2">
                    <div class="sale-timer text-center" data-end-date="{{ $sale->end_date->toISOString() }}">
                        <small class="text-danger fw-bold">
                            <i class="bi bi-clock me-1"></i>
                            <span class="timer-display">Loading...</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="text-center py-5">
        <img src="{{ asset('images/no-products.png') }}" alt="No Products" class="img-fluid mb-4" style="max-width: 200px;">
        <h4 class="text-muted">No Products Found</h4>
        <p class="text-muted">Try adjusting your filters or check back later.</p>
    </div>
@endif

<style>
.product-card:hover .product-overlay {
    opacity: 1 !important;
    background: rgba(0,0,0,0.5);
    transition: all 0.3s ease;
}

.product-image {
    overflow: hidden;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

.stars {
    font-size: 0.875rem;
}
</style>

<script>
// Initialize product card timers
document.querySelectorAll('.sale-timer').forEach(timer => {
    const endDate = new Date(timer.dataset.endDate);
    const display = timer.querySelector('.timer-display');
    
    function updateTimer() {
        const now = new Date();
        const difference = endDate - now;
        
        if (difference > 0) {
            const hours = Math.floor(difference / (1000 * 60 * 60));
            const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
            
            if (hours > 24) {
                const days = Math.floor(hours / 24);
                display.innerHTML = `${days}d ${hours % 24}h left`;
            } else {
                display.innerHTML = `${hours}h ${minutes}m left`;
            }
        } else {
            display.innerHTML = 'Sale Ended';
            timer.classList.add('text-muted');
        }
    }
    
    updateTimer();
    setInterval(updateTimer, 60000); // Update every minute for performance
});

// Add to cart functionality
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        // Add your cart functionality here
        console.log('Add product to cart:', productId);
    });
});
</script>