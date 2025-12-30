@extends('layouts.app')

@section('title', 'Recent Views')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Recent Views</li>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-primary fw-bold">
                        <i class="bi bi-clock-history me-2"></i>Recently Viewed Products
                    </h2>
                    <p class="text-muted mb-0">
                        @if($recentViews->total() > 0)
                            {{ $recentViews->total() }} product{{ $recentViews->total() > 1 ? 's' : '' }} viewed recently
                        @else
                            No products viewed recently
                        @endif
                    </p>
                </div>
                @if($recentViews->total() > 0)
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('recent-views.clear') }}" class="d-inline">
                        @csrf
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearAllRecentViews(this.form)">
                            <i class="bi bi-trash me-1"></i>Clear All
                        </button>
                    </form>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-shop me-1"></i>Continue Shopping
                    </a>
                </div>
                @endif
            </div>

            <!-- Alert Area for Messages -->
            <div id="alert-container" class="mb-3"></div>

            @if($recentViews->count() > 0)
                <!-- Products Grid -->
                <div class="row g-4">
                    @foreach($recentViews as $recentView)
                        @php $product = $recentView->product; @endphp
                        <div class="col-6 col-md-4 col-lg-3" id="recent-item-{{ $product->id }}">
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
                                    
                                    <!-- Remove Button -->
                                    <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle p-1 remove-btn" 
                                            onclick="removeFromRecentViews({{ $product->id }})"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="left" 
                                            title="Remove from recent views"
                                            style="width: 32px !important; height: 32px !important; opacity: 1 !important; display: flex !important; visibility: visible !important; z-index: 1000 !important;">
                                        <i class="bi bi-x" style="font-size: 1rem !important; color: white !important; display: block !important;"></i>
                                    </button>

                                    <!-- Viewed Time -->
                                    <div class="position-absolute bottom-0 start-0 m-2">
                                        <span class="badge bg-dark bg-opacity-75 text-white small">
                                            <i class="bi bi-clock me-1"></i>{{ $recentView->created_at->diffForHumans() }}
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
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('products.show', $product->slug) }}" class="btn btn-primary btn-sm flex-grow-1">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm wishlist-btn" 
                                                onclick="toggleWishlist({{ $product->id }})"
                                                data-product-id="{{ $product->id }}"
                                                title="Add to wishlist">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" 
                                                onclick="removeFromRecentViews({{ $product->id }})"
                                                title="Remove from recent views">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($recentViews->hasPages())
                    <div class="d-flex justify-content-center mt-5">
                        {{ $recentViews->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-clock-history text-muted mb-3" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mb-3">No Recent Views Yet</h4>
                        <p class="text-muted mb-4">
                            Start browsing products to see them appear here.<br>
                            Your recently viewed products will be displayed in this section.
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
/* EMERGENCY BUTTON VISIBILITY - HIGHEST PRIORITY */
button[onclick*="removeFromRecentViews"] {
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    z-index: 9999 !important;
    position: absolute !important;
    top: 8px !important;
    right: 8px !important;
    width: 32px !important;
    height: 32px !important;
    background: #dc3545 !important;
    border: 2px solid white !important;
    border-radius: 50% !important;
}

button[onclick*="removeFromRecentViews"] i {
    color: white !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
}

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
    position: relative;
    z-index: 1;
}

/* Ensure proper stacking context for remove button */
.product-card {
    position: relative;
    z-index: 1;
}

.product-card .position-absolute {
    z-index: 10;
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

.remove-btn:focus,
button.remove-btn:focus,
.product-card .remove-btn:focus,
.card .remove-btn:focus {
    opacity: 1 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
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

.empty-state {
    max-width: 400px;
    margin: 0 auto;
}

.placeholder-img {
    border-radius: 12px 12px 0 0;
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
// Remove from recent views
function removeFromRecentViews(productId) {
    if (confirm('Are you sure you want to remove this product from recent views?')) {
        const button = document.querySelector(`[onclick="removeFromRecentViews(${productId})"]`);
        const originalContent = button.innerHTML;
        
        // Show loading state
        button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        button.disabled = true;
        
        fetch(`/recent-views/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const itemElement = document.getElementById(`recent-item-${productId}`);
                itemElement.style.animation = 'fadeOut 0.5s ease-out forwards';
                
                setTimeout(() => {
                    itemElement.remove();
                    
                    // Check if there are any items left
                    const remainingItems = document.querySelectorAll('[id^="recent-item-"]');
                    if (remainingItems.length === 0) {
                        location.reload(); // Reload to show empty state
                    } else {
                        // Update the count in the page header if it exists
                        updateRecentViewsCount();
                    }
                }, 500);
                
                toastr.success(data.message || 'Product removed from recent views successfully.');
            } else {
                button.innerHTML = originalContent;
                button.disabled = false;
                toastr.error(data.message || 'Failed to remove product from recent views.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.innerHTML = originalContent;
            button.disabled = false;
            toastr.error('Network error occurred. Please try again.');
        });
    }
}

// Clear all recent views
function clearAllRecentViews(form) {
    if (confirm('Are you sure you want to clear all recent views? This action cannot be undone.')) {
        const button = form.querySelector('button');
        const originalContent = button.innerHTML;
        
        // Show loading state
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Clearing...';
        button.disabled = true;
        
        form.submit();
    }
}

// Update recent views count (helper function)
function updateRecentViewsCount() {
    const remainingItems = document.querySelectorAll('[id^="recent-item-"]').length;
    const countElement = document.querySelector('p.text-muted');
    if (countElement && remainingItems > 0) {
        countElement.textContent = `${remainingItems} product${remainingItems > 1 ? 's' : ''} viewed recently`;
    }
}

// Show dynamic alerts
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alertDiv);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Toggle wishlist
function toggleWishlist(productId) {
    const button = document.querySelector(`[data-product-id="${productId}"]`);
    const icon = button.querySelector('i');
    
    fetch('/wishlist', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
            button.classList.remove('btn-outline-danger');
            button.classList.add('btn-danger');
            toastr.success(data.message);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Failed to add to wishlist.');
    });
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
    0% { 
        opacity: 1; 
        transform: scale(1) translateY(0); 
    }
    50% {
        opacity: 0.5;
        transform: scale(0.95) translateY(-5px);
    }
    100% { 
        opacity: 0; 
        transform: scale(0.8) translateY(-10px); 
        height: 0;
        margin: 0;
        padding: 0;
    }
}

.removing {
    pointer-events: none;
    filter: grayscale(100%);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.loading-spinner {
    display: inline-block;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush
@endsection