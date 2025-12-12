@if($products->count() > 0)
    @foreach($products as $product)
    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100 border-0 shadow-sm product-card">
            <!-- Product Image -->
            <div class="position-relative overflow-hidden">
                @php
                    // Try to get image from first variation, then fallback to product images
                    $firstVariation = $product->variations->first();
                    $variationImage = $firstVariation ? $firstVariation->images->first() : null;
                    $productImage = $product->images->first();
                    $selectedImage = $variationImage ?? $productImage;
                    
                    // Use optimized image URLs with proper fallback
                    $thumbnailImage = $product->getThumbnailImage();
                @endphp
                @if($thumbnailImage && $thumbnailImage->path && Storage::disk('public')->exists($thumbnailImage->path))
                    <picture>
                        @php
                            $pathInfo = pathinfo($thumbnailImage->path);
                            $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_300.webp';
                        @endphp
                        @if(Storage::disk('public')->exists($webpPath))
                            <source srcset="{{ Storage::disk('public')->url($webpPath) }}" type="image/webp">
                        @endif
                        <img src="{{ $thumbnailImage->getThumbnailUrl(300) }}" 
                             alt="{{ $product->name }}" 
                             class="card-img-top product-image" 
                             style="height: 250px; object-fit: contain; background-color: #f8f9fa;"
                             loading="lazy"
                             onerror="this.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                    </picture>
                    <div class="card-img-top bg-light align-items-center justify-content-center" style="height: 250px; display: none;">
                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                    </div>
                @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                    </div>
                @endif
                
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
                <!-- Brand & Category -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted text-uppercase fw-semibold">{{ $product->brand->name ?? 'Unknown Brand' }}</small>
                    @if($product->average_rating)
                    <div class="d-flex align-items-center">
                        <div class="text-warning me-1">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $product->average_rating)
                                    <i class="bi bi-star-fill"></i>
                                @else
                                    <i class="bi bi-star"></i>
                                @endif
                            @endfor
                        </div>
                        <small class="text-muted">({{ $product->reviews_count }})</small>
                    </div>
                    @endif
                </div>
                
                <!-- Product Name -->
                <h6 class="card-title mb-2 flex-grow-1">
                    <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark stretched-link">
                        {{ Str::limit($product->name, 60) }}
                    </a>
                </h6>
                
                <!-- Price -->
                <div class="price-section mb-3">
                    @if($product->has_variations && isset($product->min_price) && isset($product->max_price))
                        @if($product->min_price != $product->max_price)
                            <div class="price-range">
                                <span class="h6 text-primary mb-0">₹{{ number_format($product->min_price, 0) }} - ₹{{ number_format($product->max_price, 0) }}</span>
                                @if($product->mrp && $product->mrp > $product->max_price)
                                    <small class="text-muted text-decoration-line-through ms-2">₹{{ number_format($product->mrp, 0) }}</small>
                                @endif
                            </div>
                        @else
                            <div class="price-single">
                                <span class="h6 text-primary mb-0">₹{{ number_format($product->min_price, 0) }}</span>
                                @if($product->mrp && $product->mrp > $product->min_price)
                                    <small class="text-muted text-decoration-line-through ms-2">₹{{ number_format($product->mrp, 0) }}</small>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="price-single">
                            <span class="h6 text-primary mb-0">₹{{ number_format($product->price, 0) }}</span>
                            @if($product->mrp && $product->mrp > $product->price)
                                <small class="text-muted text-decoration-line-through ms-2">₹{{ number_format($product->mrp, 0) }}</small>
                            @endif
                        </div>
                    @endif
                </div>
                
                <!-- Stock Status -->
                @php
                    $inStockVariations = $product->variations->where('stock.quantity', '>', 0)->count();
                    $totalVariations = $product->variations->count();
                @endphp
                
                <div class="stock-info mb-2">
                    @if($totalVariations > 0)
                        @if($inStockVariations > 0)
                            <small class="text-success">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                {{ $inStockVariations }} of {{ $totalVariations }} variants in stock
                            </small>
                        @else
                            <small class="text-danger">
                                <i class="bi bi-x-circle-fill me-1"></i>Out of stock
                            </small>
                        @endif
                    @else
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>Simple product
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="col-12">
        <div class="text-center py-5">
            <i class="bi bi-box-seam display-1 text-muted mb-3"></i>
            <h4 class="text-muted">No products found</h4>
            <p class="text-muted">Try adjusting your filters or browse other categories</p>
        </div>
    </div>
@endif