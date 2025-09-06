@extends('layouts.frontend')

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