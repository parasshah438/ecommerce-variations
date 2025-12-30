@extends('layouts.app')

@section('title', 'My Wishlist')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Wishlist</li>
@endsection

@section('content')
@php
    // Fallback for undefined variables
    $totalItems = $totalItems ?? (isset($wishlistItems) ? $wishlistItems->total() : 0);
    
    // If wishlistItems is not set, create an empty paginator
    if (!isset($wishlistItems)) {
        $wishlistItems = new \Illuminate\Pagination\LengthAwarePaginator(
            collect([]), 
            0, 
            12, 
            1, 
            ['path' => request()->url()]
        );
    }
@endphp

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-primary fw-bold">
                        <i class="bi bi-heart-fill me-2 text-danger"></i>My Wishlist
                    </h2>
                    <p class="text-muted mb-0">
                        @if($totalItems > 0)
                            {{ $totalItems }} item{{ $totalItems > 1 ? 's' : '' }} saved for later
                        @else
                            Your wishlist is empty
                        @endif
                    </p>
                </div>
                @if($totalItems > 0)
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-danger btn-sm" onclick="clearWishlist()">
                        <i class="bi bi-trash me-1"></i>Clear All
                    </button>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-shop me-1"></i>Continue Shopping
                    </a>
                </div>
                @endif
            </div>

            @if($wishlistItems->count() > 0)
                <!-- Wishlist Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon danger mx-auto">
                                <i class="bi bi-heart-fill"></i>
                            </div>
                            <div class="stat-value">{{ $totalItems }}</div>
                            <div class="stat-label">Saved Items</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon success mx-auto">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="stat-value">
                                ${{ number_format($wishlistItems->sum(function($item) { 
                                    return optional($item->product) ? $item->product->getBestSalePrice() : 0; 
                                }), 2) }}
                            </div>
                            <div class="stat-label">Total Value</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon warning mx-auto">
                                <i class="bi bi-tag-fill"></i>
                            </div>
                            <div class="stat-value">
                                ${{ number_format($wishlistItems->sum(function($item) { 
                                    if (!$item->product) return 0;
                                    $original = $item->product->price;
                                    $sale = $item->product->getBestSalePrice();
                                    return $original - $sale;
                                }), 2) }}
                            </div>
                            <div class="stat-label">Potential Savings</div>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="row g-4">
                    @foreach($wishlistItems as $wishlistItem)
                        @php $product = $wishlistItem->product; @endphp
                        <div class="col-6 col-md-4 col-lg-3" id="wishlist-item-{{ $product->id }}">
                            <div class="card product-card h-100 border-0 shadow-sm">
                                <!-- Product Image -->
                                <div class="card-img-wrapper position-relative">
                                    @php
                                        $thumbnail = $product->getThumbnailImage();
                                        $imageUrl = null;
                                        
                                        if ($thumbnail) {
                                            // Try different possible image path formats
                                            if (isset($thumbnail->image_path)) {
                                                $imageUrl = asset('storage/' . $thumbnail->image_path);
                                            } elseif (isset($thumbnail->path)) {
                                                $imageUrl = asset('storage/' . $thumbnail->path);
                                            } elseif (isset($thumbnail->url)) {
                                                $imageUrl = $thumbnail->url;
                                            }
                                        }
                                        
                                        // Fallback to first image if thumbnail method doesn't work
                                        if (!$imageUrl && $product->images && $product->images->count() > 0) {
                                            $firstImage = $product->images->first();
                                            if (isset($firstImage->image_path)) {
                                                $imageUrl = asset('storage/' . $firstImage->image_path);
                                            } elseif (isset($firstImage->path)) {
                                                $imageUrl = asset('storage/' . $firstImage->path);
                                            }
                                        }
                                    @endphp
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" 
                                             class="card-img-top product-img" 
                                             alt="{{ $product->name }}"
                                             style="height: 200px; object-fit: cover;"
                                             onerror="this.parentElement.innerHTML='<div class=&quot;placeholder-img d-flex align-items-center justify-content-center bg-light&quot; style=&quot;height: 200px;&quot;><i class=&quot;bi bi-image text-muted&quot; style=&quot;font-size: 3rem;&quot;></i></div>'">
                                    @else
                                        <div class="placeholder-img d-flex align-items-center justify-content-center bg-light" 
                                             style="height: 200px;">
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    
                                    <!-- Sale Badge -->
                                    @php
                                        $discountPercent = $product->getDiscountPercentage();
                                    @endphp
                                    @if($discountPercent > 0)
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-danger">{{ $discountPercent }}% OFF</span>
                                        </div>
                                    @endif

                                    <!-- Added Time -->
                                    <div class="position-absolute bottom-0 start-0 m-2">
                                        <span class="badge bg-dark bg-opacity-75 text-white small">
                                            <i class="bi bi-heart me-1"></i>{{ $wishlistItem->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Product Details -->
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2 text-truncate" style="font-size: 0.9rem;">
                                        <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark">
                                            {{ $product->name }}
                                        </a>
                                    </h6>
                                    
                                    @if($product->category)
                                        <p class="text-muted small mb-2">{{ $product->category->name }}</p>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="price">
                                        @php
                                            $bestPrice = $product->getBestSalePrice();
                                            $hasDiscount = $bestPrice < $product->price;
                                        @endphp
                                        @if($hasDiscount)
                                            <span class="text-danger fw-bold">${{ number_format($bestPrice, 2) }}</span>
                                            <small class="text-muted text-decoration-line-through ms-1">
                                                ${{ number_format($product->price, 2) }}
                                            </small>
                                        @else
                                            <span class="text-primary fw-bold">${{ number_format($product->price, 2) }}</span>
                                        @endif
                                        </div>
                                        
                                        <!-- Stock Status -->
                                        @php
                                            // Check if product has stock through variations or direct stock attribute
                                            $hasStock = false;
                                            if (isset($product->stock) && $product->stock > 0) {
                                                $hasStock = true;
                                            } elseif ($product->variations && $product->variations->count() > 0) {
                                                $hasStock = $product->variations->sum(function($variation) {
                                                    return optional($variation->stock)->quantity ?? 0;
                                                }) > 0;
                                            } else {
                                                $hasStock = true; // Default to in stock if no stock system
                                            }
                                        @endphp
                                        @if($hasStock)
                                            <span class="badge bg-success small">In Stock</span>
                                        @else
                                            <span class="badge bg-danger small">Out of Stock</span>
                                        @endif
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('products.show', $product->slug) }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>View Details
                                        </a>
                                        <div class="d-flex gap-2">
                                            @php
                                                // Check stock status (reuse the logic from above)
                                                $hasStock = false;
                                                if (isset($product->stock) && $product->stock > 0) {
                                                    $hasStock = true;
                                                } elseif ($product->variations && $product->variations->count() > 0) {
                                                    $hasStock = $product->variations->sum(function($variation) {
                                                        return optional($variation->stock)->quantity ?? 0;
                                                    }) > 0;
                                                } else {
                                                    $hasStock = true; // Default to in stock if no stock system
                                                }
                                            @endphp
                                            @if($hasStock)
                                                <button class="btn btn-success btn-sm flex-grow-1" onclick="addToCart({{ $product->id }})">
                                                    <i class="bi bi-cart-plus me-1"></i>Add to Cart
                                                </button>
                                            @else
                                                <button class="btn btn-outline-secondary btn-sm flex-grow-1" disabled>
                                                    <i class="bi bi-x-circle me-1"></i>Out of Stock
                                                </button>
                                            @endif
                                            <button class="btn btn-danger btn-sm" 
                                                    onclick="removeFromWishlist({{ $product->id }})"
                                                    title="Remove from wishlist">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($wishlistItems->hasPages())
                    <div class="d-flex justify-content-center mt-5">
                        {{ $wishlistItems->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-heart text-muted mb-3" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mb-3">Your Wishlist is Empty</h4>
                        <p class="text-muted mb-4">
                            Save products you love by clicking the heart icon.<br>
                            Your favorite items will appear here for easy access.
                        </p>
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            <i class="bi bi-shop me-2"></i>Start Shopping
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.product-card {
    transition: all 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.card-img-wrapper {
    overflow: hidden;
    border-radius: 12px 12px 0 0;
}

.product-img {
    transition: transform 0.3s ease;
}

.product-card:hover .product-img {
    transform: scale(1.05);
}

/* Remove button styling - Force visibility */
.remove-btn,
button.remove-btn,
.product-card .remove-btn,
.card .remove-btn {
    width: 32px !important;
    height: 32px !important;
    opacity: 1 !important;
    z-index: 1000 !important;
    transition: all 0.2s ease !important;
    display: flex !important;
    visibility: visible !important;
    align-items: center !important;
    justify-content: center !important;
    border: 2px solid white !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3) !important;
    position: absolute !important;
    top: 8px !important;
    right: 8px !important;
    background-color: #dc3545 !important;
}

.remove-btn:hover,
button.remove-btn:hover,
.product-card .remove-btn:hover,
.card .remove-btn:hover {
    opacity: 1 !important;
    transform: scale(1.1) !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.4) !important;
    visibility: visible !important;
    display: flex !important;
}

.remove-btn i,
button.remove-btn i,
.product-card .remove-btn i,
.card .remove-btn i {
    font-size: 1rem !important;
    font-weight: bold !important;
    color: white !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.remove-btn:hover {
    opacity: 1 !important;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.4);
}

.remove-btn:focus {
    opacity: 1 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.remove-btn i {
    font-size: 1rem !important;
    font-weight: bold;
    color: white !important;
}

/* Ensure remove button is visible on mobile */
@media (max-width: 768px) {
    .remove-btn {
        width: 36px !important;
        height: 36px !important;
        opacity: 1 !important;
    }
    
    .remove-btn i {
        font-size: 1.2rem !important;
    }
}

/* Card positioning */
.card-img-wrapper {
    position: relative;
    z-index: 1;
}

.product-card {
    position: relative;
    z-index: 1;
}

.product-card .position-absolute {
    z-index: 10;
}

.empty-state {
    max-width: 400px;
    margin: 0 auto;
}

.placeholder-img {
    border-radius: 12px 12px 0 0;
}

.stat-card {
    padding: 1.5rem;
    border-radius: 12px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

@media (max-width: 768px) {
    .product-card .card-body {
        padding: 1rem;
    }
    
    .product-card .card-title {
        font-size: 0.85rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .stat-card {
        margin-bottom: 1rem;
    }
}

[data-theme="dark"] .product-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
}

[data-theme="dark"] .placeholder-img {
    background: var(--sidebar-hover) !important;
}
</style>
@endpush

@push('scripts')
<script>
// Remove from wishlist
function removeFromWishlist(productId) {
    if (confirm('Are you sure you want to remove this item from your wishlist?')) {
        fetch(`/wishlist/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`wishlist-item-${productId}`).style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    document.getElementById(`wishlist-item-${productId}`).remove();
                    location.reload(); // Reload to update counts
                }, 300);
                toastr.success(data.message);
                
                // Update wishlist count in sidebar
                updateWishlistCount(data.wishlist_count);
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Failed to remove item from wishlist.');
        });
    }
}

// Clear wishlist
function clearWishlist() {
    if (confirm('Are you sure you want to clear your entire wishlist? This action cannot be undone.')) {
        window.location.href = '{{ route('wishlist.clear') }}';
    }
}

// Add to cart
function addToCart(productId) {
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Failed to add item to cart.');
    });
}

// Update wishlist count in sidebar
function updateWishlistCount(count) {
    const wishlistBadge = document.querySelector('.nav-link[href*="wishlist"] .badge');
    if (wishlistBadge) {
        if (count > 0) {
            wishlistBadge.textContent = count;
            wishlistBadge.style.display = 'inline-block';
        } else {
            wishlistBadge.style.display = 'none';
        }
    }
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
@keyframes fadeOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.8); }
}
</style>
@endpush
@endsection@extends('layouts.frontend')

@section('title', 'My Wishlist - ' . config('app.name'))

@section('breadcrumb')
<div class="bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">My Wishlist</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid px-md-5">
    <div class="row">
        <div class="col-12">
            <!-- Wishlist Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger bg-opacity-10 border-danger">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex align-items-center">
                            <h4 class="mb-0 me-3">
                                <i class="bi bi-heart-fill text-danger me-2"></i>
                                My Wishlist
                                <span class="badge bg-danger ms-2" id="wishlist-count">{{ $totalCount }}</span>
                            </h4>
                        </div>
                        
                        @if($wishlistItems->count() > 0)
                        <div class="d-flex gap-2 flex-wrap">
                            <!-- Bulk Actions -->
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-primary btn-sm" id="select-all-btn">
                                    <i class="bi bi-check-all me-1"></i>Select All
                                </button>
                                <button class="btn btn-outline-secondary btn-sm d-none" id="deselect-all-btn">
                                    <i class="bi bi-x-square me-1"></i>Deselect All
                                </button>
                            </div>
                            
                            <!-- Action Buttons -->
                            <button class="btn btn-primary btn-sm d-none" id="move-selected-to-cart">
                                <i class="bi bi-cart-plus me-1"></i>Move Selected to Cart
                            </button>
                            <button class="btn btn-outline-danger btn-sm d-none" id="remove-selected">
                                <i class="bi bi-trash me-1"></i>Remove Selected
                            </button>
                            <button class="btn btn-success btn-sm" id="move-all-to-cart">
                                <i class="bi bi-cart-fill me-1"></i>Move All to Cart
                            </button>
                            <button class="btn btn-outline-danger btn-sm" id="clear-wishlist">
                                <i class="bi bi-trash3 me-1"></i>Clear All
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                
                @if($wishlistItems->count() > 0)
                <!-- Selection Info Bar -->
                <div class="card-body py-2 bg-light d-none" id="selection-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            <span id="selected-count">0</span> of {{ $totalCount }} items selected
                        </span>
                        <button class="btn btn-sm btn-outline-secondary" id="clear-selection">
                            Clear Selection
                        </button>
                    </div>
                </div>
                @endif
            </div>

            <!-- Wishlist Items -->
            @if($wishlistItems->count() > 0)
            <div id="wishlist-items-container">
                @include('wishlist._items', ['wishlistItems' => $wishlistItems])
            </div>
            
            <!-- Load More Button -->
            @if($wishlistItems->hasMorePages())
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary" id="load-more-btn" data-page="{{ $wishlistItems->currentPage() + 1 }}">
                    <span class="btn-text">
                        <i class="bi bi-arrow-down-circle me-2"></i>Load More Items
                    </span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>Loading...
                    </span>
                </button>
            </div>
            @endif
            @else
            <!-- Empty Wishlist -->
            <div class="text-center py-5" id="empty-wishlist">
                <i class="bi bi-heart text-muted" style="font-size: 5rem;"></i>
                <h3 class="mt-4 text-muted">Your wishlist is empty</h3>
                <p class="text-muted mb-4">Save items you love for later!</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-shop me-2"></i>Start Shopping
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Confirmation Modals -->
<!-- Remove Items Modal -->
<div class="modal fade" id="removeItemsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="remove-modal-text">Are you sure you want to remove selected items from your wishlist?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-remove">
                    <i class="bi bi-trash me-2"></i>Remove Items
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Clear All Modal -->
<div class="modal fade" id="clearAllModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear Wishlist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to clear your entire wishlist?</p>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    All {{ $totalCount }} items will be permanently removed from your wishlist.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-clear-all">
                    <i class="bi bi-trash3 me-2"></i>Clear All
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Move to Cart Modal -->
<div class="modal fade" id="moveToCartModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Move to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>How many items would you like to add to cart?</p>
                <div class="mb-3">
                    <label for="cart-quantity" class="form-label">Quantity:</label>
                    <div class="input-group" style="max-width: 150px;">
                        <button class="btn btn-outline-secondary" type="button" id="qty-minus">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" class="form-control text-center" id="cart-quantity" value="1" min="1" max="10">
                        <button class="btn btn-outline-secondary" type="button" id="qty-plus">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-move-to-cart">
                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let selectedItems = new Set();
    let currentWishlistId = null;
    
    // Selection functionality
    $(document).on('change', '.item-checkbox', function() {
        const wishlistId = $(this).val();
        const isChecked = $(this).is(':checked');
        
        if (isChecked) {
            selectedItems.add(wishlistId);
        } else {
            selectedItems.delete(wishlistId);
        }
        
        updateSelectionUI();
    });
    
    // Select all functionality
    $('#select-all-btn').click(function() {
        $('.item-checkbox').prop('checked', true);
        selectedItems.clear();
        $('.item-checkbox').each(function() {
            selectedItems.add($(this).val());
        });
        updateSelectionUI();
    });
    
    $('#deselect-all-btn, #clear-selection').click(function() {
        $('.item-checkbox').prop('checked', false);
        selectedItems.clear();
        updateSelectionUI();
    });
    
    function updateSelectionUI() {
        const count = selectedItems.size;
        $('#selected-count').text(count);
        
        if (count > 0) {
            $('#selection-info').removeClass('d-none');
            $('#move-selected-to-cart, #remove-selected').removeClass('d-none');
            $('#deselect-all-btn').removeClass('d-none');
            $('#select-all-btn').addClass('d-none');
        } else {
            $('#selection-info').addClass('d-none');
            $('#move-selected-to-cart, #remove-selected').addClass('d-none');
            $('#deselect-all-btn').addClass('d-none');
            $('#select-all-btn').removeClass('d-none');
        }
    }
    
    // Load more functionality
    $('#load-more-btn').click(function() {
        const $btn = $(this);
        const page = $btn.data('page');
        
        // Show loading state
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        $btn.prop('disabled', true);
        
        $.ajax({
            url: '{{ route("wishlist.load_more") }}',
            method: 'GET',
            data: { page: page },
            success: function(response) {
                if (response.success) {
                    $('#wishlist-items-container').append(response.html);
                    
                    if (response.has_more) {
                        $btn.data('page', response.current_page + 1);
                    } else {
                        $btn.remove();
                    }
                    
                    toastr.success('More items loaded');
                } else {
                    toastr.error('Failed to load more items');
                }
            },
            error: function() {
                toastr.error('Failed to load more items');
            },
            complete: function() {
                // Hide loading state
                $btn.find('.btn-text').removeClass('d-none');
                $btn.find('.btn-loading').addClass('d-none');
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Single item actions
    $(document).on('click', '.remove-item-btn', function() {
        const wishlistId = $(this).data('wishlist-id');
        removeItems([wishlistId]);
    });
    
    $(document).on('click', '.move-to-cart-btn', function() {
        currentWishlistId = $(this).data('wishlist-id');
        $('#moveToCartModal').modal('show');
    });
    
    // Bulk actions
    $('#remove-selected').click(function() {
        if (selectedItems.size > 0) {
            const count = selectedItems.size;
            $('#remove-modal-text').text(`Are you sure you want to remove ${count} selected items from your wishlist?`);
            $('#removeItemsModal').modal('show');
        }
    });
    
    $('#move-selected-to-cart').click(function() {
        if (selectedItems.size > 0) {
            moveSelectedToCart();
        }
    });
    
    $('#move-all-to-cart').click(function() {
        if (confirm('Move all wishlist items to cart?')) {
            moveAllToCart();
        }
    });
    
    $('#clear-wishlist').click(function() {
        $('#clearAllModal').modal('show');
    });
    
    // Modal confirmations
    $('#confirm-remove').click(function() {
        const itemsArray = Array.from(selectedItems);
        removeItems(itemsArray);
        $('#removeItemsModal').modal('hide');
    });
    
    $('#confirm-clear-all').click(function() {
        clearAllWishlist();
        $('#clearAllModal').modal('hide');
    });
    
    $('#confirm-move-to-cart').click(function() {
        const quantity = parseInt($('#cart-quantity').val()) || 1;
        moveToCart(currentWishlistId, quantity);
        $('#moveToCartModal').modal('hide');
    });
    
    // Quantity controls in modal
    $('#qty-plus').click(function() {
        const $qty = $('#cart-quantity');
        const current = parseInt($qty.val()) || 1;
        if (current < 10) {
            $qty.val(current + 1);
        }
    });
    
    $('#qty-minus').click(function() {
        const $qty = $('#cart-quantity');
        const current = parseInt($qty.val()) || 1;
        if (current > 1) {
            $qty.val(current - 1);
        }
    });
    
    // AJAX Functions
    function removeItems(wishlistIds) {
        const isMultiple = wishlistIds.length > 1;
        const url = isMultiple ? '{{ route("wishlist.remove_multiple") }}' : '{{ route("wishlist.remove") }}';
        const data = isMultiple 
            ? { wishlist_ids: wishlistIds, _token: '{{ csrf_token() }}' }
            : { wishlist_id: wishlistIds[0], _token: '{{ csrf_token() }}' };
        
        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    // Remove items from DOM
                    wishlistIds.forEach(id => {
                        $(`[data-wishlist-item="${id}"]`).fadeOut(400, function() {
                            $(this).remove();
                            checkEmptyWishlist();
                        });
                        selectedItems.delete(id.toString());
                    });
                    
                    updateWishlistCount(response.wishlist_count);
                    updateSelectionUI();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Failed to remove items');
            }
        });
    }
    
    function moveToCart(wishlistId, quantity = 1) {
        $.ajax({
            url: '{{ route("wishlist.move_to_cart") }}',
            method: 'POST',
            data: {
                wishlist_id: wishlistId,
                quantity: quantity,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $(`[data-wishlist-item="${wishlistId}"]`).fadeOut(400, function() {
                        $(this).remove();
                        checkEmptyWishlist();
                    });
                    
                    updateWishlistCount(response.wishlist_count);
                    updateCartBadge(response.cart_summary);
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Failed to move item to cart');
            }
        });
    }
    
    function moveSelectedToCart() {
        // For multiple items, we'll move them one by one
        const itemsArray = Array.from(selectedItems);
        let processedCount = 0;
        
        itemsArray.forEach((wishlistId, index) => {
            setTimeout(() => {
                moveToCart(wishlistId, 1);
                processedCount++;
                
                if (processedCount === itemsArray.length) {
                    selectedItems.clear();
                    updateSelectionUI();
                }
            }, index * 200); // Stagger requests
        });
    }
    
    function moveAllToCart() {
        $.ajax({
            url: '{{ route("wishlist.move_all_to_cart") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    if (response.moved_count > 0) {
                        $('.wishlist-item').fadeOut(400, function() {
                            $(this).remove();
                        });
                        
                        setTimeout(() => {
                            $('#empty-wishlist').removeClass('d-none');
                            $('.card').first().addClass('d-none');
                        }, 500);
                    }
                    
                    updateWishlistCount(response.wishlist_count);
                    updateCartBadge(response.cart_summary);
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Failed to move items to cart');
            }
        });
    }
    
    function clearAllWishlist() {
        $.ajax({
            url: '{{ route("wishlist.clear_all") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('.wishlist-item').fadeOut(400, function() {
                        $(this).remove();
                    });
                    
                    setTimeout(() => {
                        $('#empty-wishlist').removeClass('d-none');
                        $('.card').first().addClass('d-none');
                    }, 500);
                    
                    updateWishlistCount(0);
                    selectedItems.clear();
                    updateSelectionUI();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Failed to clear wishlist');
            }
        });
    }
    
    function updateWishlistCount(count) {
        $('#wishlist-count').text(count);
        
        // Update any wishlist badges in navigation
        $('.wishlist-badge').text(count);
    }
    
    function updateCartBadge(cartSummary) {
        if (cartSummary && cartSummary.items) {
            $('#cart-badge').text(cartSummary.items);
        }
    }
    
    function checkEmptyWishlist() {
        if ($('.wishlist-item').length === 0) {
            setTimeout(() => {
                $('#empty-wishlist').removeClass('d-none');
                $('.card').first().addClass('d-none');
            }, 500);
        }
    }
});
</script>
@endsection