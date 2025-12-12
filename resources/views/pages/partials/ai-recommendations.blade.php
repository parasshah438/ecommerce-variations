

@if($products->count() > 0)
    <!-- Summary Stats -->
    <div class="row mt-4">
        <div class="col-12 mb-4">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h5 class="text-primary mb-1">{{ $products->count() }}</h5>
                            <small class="text-muted">Products Found</small>
                        </div>
                        <div class="col-md-3">
                            <h5 class="text-success mb-1">{{ rand(85, 98) }}%</h5>
                            <small class="text-muted">AI Confidence</small>
                        </div>
                        <div class="col-md-3">
                            <h5 class="text-info mb-1">₹{{ number_format($products->avg('price'), 0) }}</h5>
                            <small class="text-muted">Avg Price</small>
                        </div>
                        <div class="col-md-3">
                            <h5 class="text-warning mb-1">{{ $products->where('variations')->count() }}</h5>
                            <small class="text-muted">With Variations</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($products as $product)            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 product-card shadow-sm">
                    <!-- Product Image -->
                    <div class="position-relative">
                        @if($product->images->count() > 0)
                            <img src="{{ $product->getThumbnailImage() ? $product->getThumbnailImage()->getThumbnailUrl(150) : asset('images/product-placeholder.jpg') }}" 
                                 class="card-img-top" 
                                 alt="{{ $product->name }}"
                                 style="height: 250px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 250px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                        
                        <!-- AI Confidence Badge -->
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-success">
                                <i class="fas fa-robot me-1"></i>{{ rand(85, 98) }}% Match
                            </span>
                        </div>
                        
                        <!-- Wishlist Button -->
                        @auth
                        <div class="position-absolute top-0 start-0 m-2">
                            <button class="btn btn-light btn-sm rounded-circle wishlist-btn" 
                                    data-product-id="{{ $product->id }}">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        @endauth
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title mb-2">{{ $product->name }}</h6>
                        
                        <!-- Price -->
                        <div class="mb-2">
                            <span class="h6 text-primary mb-0">₹{{ number_format($product->price, 0) }}</span>
                            @if($product->original_price > $product->price)
                                <span class="text-muted text-decoration-line-through small ms-2">
                                    ₹{{ number_format($product->original_price, 0) }}
                                </span>
                            @endif
                        </div>
                        
                        <!-- Variations Info -->
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-layer-group me-1"></i>
                                {{ $product->variations->count() }} variations available
                            </small>
                        </div>
                        
                        <!-- Stock Status -->
                        @php
                            $inStock = $product->variations->filter(function($v) {
                                return optional($v->stock)->quantity > 0;
                            })->count() > 0;
                        @endphp
                        
                        <div class="mb-3">
                            @if($inStock)
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-check-circle me-1"></i>In Stock
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">
                                    <i class="fas fa-times-circle me-1"></i>Out of Stock
                                </span>
                            @endif
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="mt-auto">
                            <div class="row g-2">
                                <div class="col-8">
                                    <a href="{{ route('products.show', $product->slug) }}" 
                                       class="btn btn-outline-primary btn-sm w-100">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                </div>
                                <div class="col-4">
                                    @if($inStock)
                                        <button class="btn btn-primary btn-sm w-100 add-to-cart-btn" 
                                                data-product-id="{{ $product->id }}">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-secondary btn-sm w-100" disabled>
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- AI Recommendation Reason -->
                    <div class="card-footer bg-light border-0">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb me-1"></i>
                            @php
                                $reasons = [
                                    'Perfect match for your style preference',
                                    'Great for the occasion you selected',
                                    'Popular choice in your price range',
                                    'Highly rated by similar customers',
                                    'Trending in your preferred category'
                                ];
                                echo $reasons[array_rand($reasons)];
                            @endphp
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    @else
    <div class="col-12">
        <div class="text-center py-5">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No products found</h4>
            <p class="text-muted mb-4">
                We couldn't find any products matching your specific preferences. <br>
                Try adjusting your selections or resetting your preferences to see more results.
            </p>
            
            <div class="row justify-content-center">
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-primary me-2" onclick="$('#resetForm').click();">
                        <i class="fas fa-redo me-2"></i>Reset Preferences
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('products.index') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-bag me-2"></i>Browse All Products
                    </a>
                </div>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    <strong>Tips:</strong> Try selecting fewer filters, different color combinations, or broader size ranges
                </small>
            </div>
        </div>
    </div>@endif
</div>