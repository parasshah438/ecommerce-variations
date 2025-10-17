<div class="row g-3">
    @foreach($wishlistItems as $item)
    <div class="col-lg-3 col-md-4 col-sm-6 col-12 wishlist-item" data-wishlist-item="{{ $item->id }}">
        <div class="card h-100 shadow-sm border-0 position-relative">
            <!-- Selection Checkbox -->
            <div class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
                <div class="form-check">
                    <input class="form-check-input item-checkbox" type="checkbox" value="{{ $item->id }}" id="item-{{ $item->id }}">
                </div>
            </div>
            
            <!-- Stock Badge -->
            <div class="position-absolute top-0 end-0 m-2" style="z-index: 10;">
                @if($item->is_in_stock)
                    <span class="badge bg-success rounded-pill">
                        <i class="bi bi-check-circle-fill"></i>
                    </span>
                @else
                    <span class="badge bg-danger rounded-pill">
                        <i class="bi bi-x-circle-fill"></i>
                    </span>
                @endif
            </div>

            <!-- Product Image -->
            <div class="position-relative">
                @php
                    // Try to get image from first variation, then fallback to product images
                    $firstVariation = $item->product->variations->first();
                    $variationImage = $firstVariation ? $firstVariation->images->first() : null;
                    $productImage = $item->product->images->first();
                    $image = $variationImage ?? $productImage;
                @endphp
                @if($image)
                    @php
                        $imageSrc = str_starts_with($image->path, 'http') 
                            ? $image->path 
                            : Storage::url($image->path);
                    @endphp
                    <img src="{{ $imageSrc }}" 
                         class="card-img-top" 
                         alt="{{ $item->product->name }}"
                         style="height: 200px; object-fit: cover;">
                @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="bi bi-image text-muted fs-1"></i>
                    </div>
                @endif
                
                <!-- Wishlist Heart -->
                <button class="btn btn-danger btn-sm position-absolute bottom-0 end-0 m-2 remove-item-btn" 
                        data-wishlist-id="{{ $item->id }}" 
                        title="Remove from wishlist">
                    <i class="bi bi-heart-fill"></i>
                </button>
            </div>
            
            <!-- Product Details -->
            <div class="card-body p-3 d-flex flex-column">
                <!-- Product Name -->
                <h6 class="card-title mb-2 fw-semibold lh-sm">
                    <a href="{{ route('products.show', $item->product->slug) }}" 
                       class="text-decoration-none text-dark">
                        {{ Str::limit($item->product->name, 45) }}
                    </a>
                </h6>
                
                <!-- Brand -->
                @if($item->product->brand)
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="bi bi-award me-1"></i>{{ $item->product->brand->name }}
                    </small>
                </div>
                @endif
                
                <!-- Price -->
                <div class="mb-2">
                    <h6 class="text-primary fw-bold mb-1">₹{{ number_format($item->best_price, 2) }}</h6>
                    @if($item->product->mrp && $item->product->mrp > $item->best_price)
                    <div>
                        <small class="text-muted text-decoration-line-through">₹{{ number_format($item->product->mrp, 2) }}</small>
                        <small class="text-success fw-semibold ms-1">
                            {{ round((($item->product->mrp - $item->best_price) / $item->product->mrp) * 100) }}% off
                        </small>
                    </div>
                    @endif
                </div>
                
                <!-- Stock Information -->
                <div class="mb-3">
                    @if($item->is_in_stock)
                        <small class="text-success">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ $item->available_stock > 10 ? 'In Stock' : $item->available_stock . ' left' }}
                        </small>
                    @else
                        <small class="text-danger">
                            <i class="bi bi-x-circle me-1"></i>Out of Stock
                        </small>
                    @endif
                </div>
                
                <!-- Variation Info -->
                @if($item->best_variation && !empty($item->best_variation->attribute_value_ids))
                <div class="mb-2">
                    @php
                        $attributeIds = $item->best_variation->attribute_value_ids;
                        // Ensure it's an array and has valid values
                        if (is_array($attributeIds) && count($attributeIds) > 0) {
                            $attributeValues = \App\Models\AttributeValue::whereIn('id', $attributeIds)->with('attribute')->get();
                        } else {
                            $attributeValues = collect();
                        }
                    @endphp
                    @if($attributeValues->isNotEmpty())
                        @foreach($attributeValues->take(2) as $attrValue)
                        <span class="badge bg-light text-dark me-1 mb-1 small">
                            {{ $attrValue->value }}
                        </span>
                        @endforeach
                    @endif
                </div>
                @endif
                
                <!-- Action Buttons -->
                <div class="mt-auto">
                    @if($item->is_in_stock)
                        <button class="btn btn-primary btn-sm w-100 mb-2 move-to-cart-btn" 
                                data-wishlist-id="{{ $item->id }}">
                            <i class="bi bi-cart-plus me-1"></i>Move to Cart
                        </button>
                    @else
                        <button class="btn btn-secondary btn-sm w-100 mb-2" disabled>
                            <i class="bi bi-x-circle me-1"></i>Out of Stock
                        </button>
                    @endif
                    
                    <div class="d-flex gap-1">
                        <a href="{{ route('products.show', $item->product->slug) }}" 
                           class="btn btn-outline-primary btn-sm flex-fill">
                            <i class="bi bi-eye"></i>
                        </a>
                        <button class="btn btn-outline-danger btn-sm remove-item-btn" 
                                data-wishlist-id="{{ $item->id }}"
                                title="Remove from wishlist">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Date Added -->
                <div class="mt-2 pt-2 border-top">
                    <small class="text-muted">
                        <i class="bi bi-calendar-plus me-1"></i>
                        {{ $item->created_at->format('M d') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>