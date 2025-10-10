@if($products->count() > 0)
    @foreach($products as $product)
    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100 border-0 shadow-sm product-card">
            <!-- Product Image -->
            <div class="position-relative overflow-hidden">
                @php
                    $mainImage = $product->images->first();
                    $imageUrl = $mainImage ? \Illuminate\Support\Facades\Storage::url($mainImage->path) : 'https://via.placeholder.com/400x300?text=No+Image';
                @endphp
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="card-img-top product-image" style="height: 250px; object-fit: cover;">
                
                <!-- Badges -->
                <div class="position-absolute top-0 start-0 p-2">
                    @if($product->created_at->diffInDays(now()) <= 7)
                        <span class="badge bg-success mb-1 d-block">
                            <i class="bi bi-star-fill me-1"></i>NEW
                        </span>
                    @endif
                    @if($product->mrp && $product->mrp > $product->price)
                        @php $discount = round((($product->mrp - $product->price) / $product->mrp) * 100); @endphp
                        <span class="badge bg-danger">{{ $discount }}% OFF</span>
                    @endif
                </div>
                
                <!-- Quick Actions Overlay -->
                <div class="position-absolute top-0 end-0 p-2 product-actions-overlay opacity-0">
                    <div class="d-flex flex-column gap-2">
                        <button class="btn btn-sm btn-light rounded-circle shadow-sm" onclick="addToWishlist({{ $product->id }})" title="Add to Wishlist">
                            <i class="bi bi-heart"></i>
                        </button>
                        <a href="#" class="btn btn-sm btn-light rounded-circle shadow-sm product-quick-view" data-product-slug="{{ $product->slug }}" title="Quick View">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($product->variations->where('stock.quantity', '>', 0)->count())
                        <button class="btn btn-sm btn-primary rounded-circle shadow-sm" onclick="quickAddToCart({{ $product->id }})" title="Add to Cart">
                            <i class="bi bi-cart-plus"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="card-body d-flex flex-column">
                @if($product->brand)
                <div class="text-muted small text-uppercase fw-bold mb-1 letter-spacing-1">{{ $product->brand->name }}</div>
                @endif
                
                <h6 class="card-title mb-2 fw-bold">
                    <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark stretched-link">
                        {{ Str::limit($product->name, 50) }}
                    </a>
                </h6>
                
                <!-- Rating -->
                <div class="mb-2 d-flex align-items-center">
                    <div class="text-warning me-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= ($product->average_rating ?? 4) ? '-fill' : '' }}"></i>
                        @endfor
                    </div>
                    <small class="text-muted">({{ $product->reviews_count ?? 0 }} reviews)</small>
                </div>
                
                <!-- Price -->
                <div class="mb-3">
                    @if($product->has_variations)
                        <h5 class="text-primary fw-bold mb-0">
                            ₹{{ number_format($product->min_price) }}
                            @if($product->max_price > $product->min_price)
                                <small class="text-muted fw-normal"> - ₹{{ number_format($product->max_price) }}</small>
                            @endif
                        </h5>
                    @else
                        <div class="d-flex align-items-center">
                            <h5 class="text-primary fw-bold mb-0 me-2">₹{{ number_format($product->price) }}</h5>
                            @if($product->mrp && $product->mrp > $product->price)
                                <small class="text-muted text-decoration-line-through">₹{{ number_format($product->mrp) }}</small>
                            @endif
                        </div>
                    @endif
                </div>
                
                <!-- Stock Status -->
                <div class="mt-auto">
                    @if($product->variations->where('stock.quantity', '>', 0)->count())
                        <div class="d-flex align-items-center text-success">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            <small class="fw-bold">In Stock</small>
                        </div>
                    @else
                        <div class="d-flex align-items-center text-danger">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            <small class="fw-bold">Out of Stock</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif