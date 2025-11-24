/**
 * Ecommerce Admin UI Enhancements
 * Enhanced user interaction and animations for ecommerce metrics
 */

$(document).ready(function() {
    
    // Enhanced tooltips for metric cards
    $('[data-bs-toggle="tooltip"]').tooltip({
        placement: 'top',
        trigger: 'hover'
    });

    // Fix dropdown positioning
    $('.dropdown').on('show.bs.dropdown', function(e) {
        const dropdown = $(this);
        const menu = dropdown.find('.dropdown-menu');
        
        // Add animation
        menu.addClass('animate__animated animate__fadeIn animate__faster');
        
        // Fix positioning for dropdowns in DataTables
        setTimeout(() => {
            fixDropdownPosition(dropdown, menu);
        }, 10);
    });
    
    // Recalculate dropdown position on scroll
    $(window).on('scroll resize', function() {
        $('.dropdown-menu.show').each(function() {
            const menu = $(this);
            const dropdown = menu.closest('.dropdown, .btn-group');
            fixDropdownPosition(dropdown, menu);
        });
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown, .btn-group').length) {
            $('.dropdown-menu.show').removeClass('show');
        }
    });

    // Add loading states for ecommerce actions
    $('.view-ecommerce-details').on('click', function() {
        const btn = $(this);
        const originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...');
        btn.prop('disabled', true);
        
        // Simulate API call - replace with actual AJAX call
        setTimeout(() => {
            btn.html(originalText);
            btn.prop('disabled', false);
            
            // Show detailed analytics modal or redirect
            showEcommerceDetailsModal($(this).data('id'));
        }, 1000);
    });

    // Enhanced click handlers for quick actions
    $('.view-user-orders').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        loadUserOrders(userId);
    });

    $('.view-user-wishlist').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        loadUserWishlist(userId);
    });

    $('.view-user-cart').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        loadUserCart(userId);
    });

    $('.view-user-payments').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        loadUserPayments(userId);
    });

    $('.send-user-email').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        openEmailComposer(userId);
    });

    $('.view-user-activity').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        loadUserActivity(userId);
    });

    // Handlers for detail modal actions
    $(document).on('click', '.view-user-orders-detail', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        loadUserOrders(userId);
    });

    $(document).on('click', '.view-user-wishlist-detail', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        loadUserWishlist(userId);
    });

    $(document).on('click', '.view-user-cart-detail', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        loadUserCart(userId);
    });

    $(document).on('click', '.view-user-payments-detail', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        loadUserPayments(userId);
    });

    $(document).on('click', '.send-user-email-detail', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        openEmailComposer(userId);
    });

    $(document).on('click', '.view-user-activity-detail', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        loadUserActivity(userId);
    });

    // Add hover effects to metric cards
    $(document).on('mouseenter', '.card.border-0.bg-light', function() {
        $(this).addClass('shadow-sm');
        $(this).find('i').addClass('animate__animated animate__pulse');
    });

    $(document).on('mouseleave', '.card.border-0.bg-light', function() {
        $(this).removeClass('shadow-sm');
        $(this).find('i').removeClass('animate__animated animate__pulse');
    });

    // Auto-refresh metrics every 30 seconds
    setInterval(refreshEcommerceMetrics, 30000);
    
    // Initialize dropdown fixes for DataTables
    initializeDataTablesDropdowns();
    
    // Enhanced ecommerce overview button handler
    $(document).on('click', '.ecommerce-overview', function(e) {
        e.preventDefault();
        const button = $(this);
        const userId = button.data('id');
        const userName = button.data('user-name');
        const wishlistCount = button.data('wishlist');
        const cartCount = button.data('cart');
        const viewsCount = button.data('views');
        const ordersCount = button.data('orders');
        const successPayments = button.data('success-payments');
        const failedPayments = button.data('failed-payments');
        
        // Show user details modal and populate with ecommerce data
        showUserDetailsWithEcommerce(userId, {
            name: userName,
            wishlist_count: wishlistCount,
            cart_count: cartCount,
            recent_views: viewsCount,
            total_orders: ordersCount,
            successful_payments: successPayments,
            failed_payments: failedPayments
        });
    });
});

/**
 * Initialize dropdown fixes for DataTables
 */
function initializeDataTablesDropdowns() {
    // Force DataTables to allow overflow
    if ($.fn.DataTable) {
        // Override DataTable settings
        $.fn.dataTable.ext.classes.sScrollBody = 'dataTables_scrollBody';
        
        // Hook into DataTable draw event
        $(document).on('draw.dt', function() {
            // Re-initialize tooltips after table redraw
            $('[data-bs-toggle="tooltip"]').tooltip({
                placement: 'top',
                trigger: 'hover'
            });
            
            // Fix any open dropdowns
            $('.dropdown-menu.show').each(function() {
                const menu = $(this);
                const dropdown = menu.closest('.dropdown, .btn-group');
                fixDropdownPosition(dropdown, menu);
            });
        });
    }
    
    // Add CSS class to DataTable container
    setTimeout(() => {
        $('.dataTables_wrapper').addClass('overflow-visible');
        $('table.dataTable').addClass('overflow-visible');
    }, 100);
}

/**
 * Show user details modal with ecommerce overview
 */
function showUserDetailsWithEcommerce(userId, ecommerceData) {
    // First load regular user data
    $.ajax({
        url: `/admin/users/ajax/view`,
        method: 'POST',
        data: {
            id: userId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                populateUserDetailsModal(response.user, ecommerceData);
                $('#userDetailsModal').modal('show');
            } else {
                showErrorToast('Failed to load user details');
            }
        },
        error: function() {
            showErrorToast('Error loading user details');
        }
    });
}

/**
 * Populate user details modal with ecommerce data
 */
function populateUserDetailsModal(user, ecommerceData) {
    // Set basic user info
    $('#userDetailsModal .modal-title').html(`
        <i class="fas fa-user-circle me-2"></i>${user.name}
        <small class="text-muted ms-2">#${user.id}</small>
    `);
    
    // Create enhanced user details with ecommerce section
    const modalBody = `
        <div class="row">
            <!-- User Information Column -->
            <div class="col-md-6">
                <div class="card border-0 bg-light h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>User Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-4"><strong>Name:</strong></div>
                            <div class="col-8">${user.name || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Email:</strong></div>
                            <div class="col-8">${user.email || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Mobile:</strong></div>
                            <div class="col-8">${user.mobile_number || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Role:</strong></div>
                            <div class="col-8">
                                <span class="badge bg-info">${user.role || 'user'}</span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Status:</strong></div>
                            <div class="col-8">
                                <span class="badge ${user.status === 'active' ? 'bg-success' : 'bg-warning'}">${user.status || 'active'}</span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Joined:</strong></div>
                            <div class="col-8">${user.created_at || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Last Login:</strong></div>
                            <div class="col-8">${user.last_login_at || 'Never'}</div>
                        </div>
                        ${user.address ? `
                        <div class="row mb-2">
                            <div class="col-4"><strong>Address:</strong></div>
                            <div class="col-8">${user.address}</div>
                        </div>
                        ` : ''}
                        ${user.city ? `
                        <div class="row mb-2">
                            <div class="col-4"><strong>City:</strong></div>
                            <div class="col-8">${user.city}</div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            <!-- Ecommerce Overview Column -->
            <div class="col-md-6">
                <div class="card border-0 bg-light h-100">
                    <div class="card-header bg-gradient-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-store me-2"></i>Ecommerce Overview</h6>
                    </div>
                    <div class="card-body">
                        <!-- Ecommerce Metrics Grid -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="card border-danger border-2 h-100 hover-lift">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-heart text-danger mb-2" style="font-size: 1.5rem;"></i>
                                        <div class="fw-bold fs-4 text-danger">${ecommerceData.wishlist_count}</div>
                                        <small class="text-muted fw-medium">Wishlist Items</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card border-warning border-2 h-100 hover-lift">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-shopping-cart text-warning mb-2" style="font-size: 1.5rem;"></i>
                                        <div class="fw-bold fs-4 text-warning">${ecommerceData.cart_count}</div>
                                        <small class="text-muted fw-medium">Cart Items</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="card border-info border-2 h-100 hover-lift">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-eye text-info mb-2" style="font-size: 1.5rem;"></i>
                                        <div class="fw-bold fs-4 text-info">${ecommerceData.recent_views}</div>
                                        <small class="text-muted fw-medium">Recent Views</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card border-primary border-2 h-100 hover-lift">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-shopping-bag text-primary mb-2" style="font-size: 1.5rem;"></i>
                                        <div class="fw-bold fs-4 text-primary">${ecommerceData.total_orders}</div>
                                        <small class="text-muted fw-medium">Total Orders</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Summary -->
                        <div class="border-top pt-3">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-credit-card text-primary me-2"></i>Payment Summary
                            </h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="card bg-success bg-opacity-10 border-success h-100">
                                        <div class="card-body text-center p-2">
                                            <i class="fas fa-check-circle text-success mb-1"></i>
                                            <div class="fw-bold text-success">$${ecommerceData.successful_payments}</div>
                                            <small class="text-muted">Successful</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-danger bg-opacity-10 border-danger h-100">
                                        <div class="card-body text-center p-2">
                                            <i class="fas fa-times-circle text-danger mb-1"></i>
                                            <div class="fw-bold text-danger">$${ecommerceData.failed_payments}</div>
                                            <small class="text-muted">Failed</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions Section -->
        <div class="mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-lightning-bolt text-warning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-lg-3 col-md-6 col-6">
                            <button type="button" class="btn btn-outline-primary w-100 py-2 view-user-orders-detail hover-lift" data-id="${user.id}">
                                <i class="fas fa-shopping-bag mb-2 d-block" style="font-size: 1.2rem;"></i>
                                <div class="fw-medium">View Orders</div>
                                <div class="badge bg-primary rounded-pill mt-1">${ecommerceData.total_orders}</div>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 col-6">
                            <button type="button" class="btn btn-outline-danger w-100 py-2 view-user-wishlist-detail hover-lift" data-id="${user.id}">
                                <i class="fas fa-heart mb-2 d-block" style="font-size: 1.2rem;"></i>
                                <div class="fw-medium">View Wishlist</div>
                                <div class="badge bg-danger rounded-pill mt-1">${ecommerceData.wishlist_count}</div>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 col-6">
                            <button type="button" class="btn btn-outline-warning w-100 py-2 view-user-cart-detail hover-lift" data-id="${user.id}">
                                <i class="fas fa-shopping-cart mb-2 d-block" style="font-size: 1.2rem;"></i>
                                <div class="fw-medium">View Cart</div>
                                <div class="badge bg-warning text-dark rounded-pill mt-1">${ecommerceData.cart_count}</div>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 col-6">
                            <button type="button" class="btn btn-outline-info w-100 py-2 view-user-payments-detail hover-lift" data-id="${user.id}">
                                <i class="fas fa-credit-card mb-2 d-block" style="font-size: 1.2rem;"></i>
                                <div class="fw-medium">Payments</div>
                            </button>
                        </div>
                    </div>
                    
                    <div class="row g-2 mt-2">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-success w-100 py-2 send-user-email-detail hover-lift" data-id="${user.id}">
                                <i class="fas fa-envelope me-2"></i>Send Email
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-secondary w-100 py-2 view-user-activity-detail hover-lift" data-id="${user.id}">
                                <i class="fas fa-history me-2"></i>Activity Log
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Set the modal body
    $('#userDetailsModal .modal-body').html(modalBody);
    
    // Add custom CSS for this modal if not already added
    if (!$('#ecommerceModalStyles').length) {
        $('head').append(`
            <style id="ecommerceModalStyles">
                .bg-gradient-primary {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                }
                .border-2 {
                    border-width: 2px !important;
                }
                .hover-lift {
                    transition: all 0.3s ease;
                }
                .hover-lift:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
                }
                #userDetailsModal .card {
                    transition: all 0.3s ease;
                }
                #userDetailsModal .modal-dialog {
                    max-width: 900px;
                }
            </style>
        `);
    }
}

/**
 * Load user orders in a modal
 */
function loadUserOrders(userId) {
    showLoadingModal('Loading Orders...');
    
    $.ajax({
        url: `/admin/users/${userId}/orders`,
        method: 'GET',
        success: function(response) {
            hideLoadingModal();
            showOrdersModal(response.data, userId);
        },
        error: function() {
            hideLoadingModal();
            showErrorToast('Failed to load user orders');
        }
    });
}

/**
 * Load user wishlist in a modal
 */
function loadUserWishlist(userId) {
    showLoadingModal('Loading Wishlist...');
    
    $.ajax({
        url: `/admin/users/${userId}/wishlist`,
        method: 'GET',
        success: function(response) {
            hideLoadingModal();
            showWishlistModal(response.data, userId);
        },
        error: function() {
            hideLoadingModal();
            showErrorToast('Failed to load user wishlist');
        }
    });
}

/**
 * Load user cart in a modal
 */
function loadUserCart(userId) {
    showLoadingModal('Loading Cart...');
    
    $.ajax({
        url: `/admin/users/${userId}/cart`,
        method: 'GET',
        success: function(response) {
            hideLoadingModal();
            showCartModal(response.data, userId);
        },
        error: function() {
            hideLoadingModal();
            showErrorToast('Failed to load user cart');
        }
    });
}

/**
 * Load user payments in a modal
 */
function loadUserPayments(userId) {
    showLoadingModal('Loading Payments...');
    
    $.ajax({
        url: `/admin/users/${userId}/payments`,
        method: 'GET',
        success: function(response) {
            hideLoadingModal();
            showPaymentsModal(response.data, userId);
        },
        error: function() {
            hideLoadingModal();
            showErrorToast('Failed to load user payments');
        }
    });
}

/**
 * Open email composer
 */
function openEmailComposer(userId) {
    $('#emailComposerModal').modal('show');
    $('#emailUserId').val(userId);
}

/**
 * Load user activity log
 */
function loadUserActivity(userId) {
    showLoadingModal('Loading Activity...');
    
    $.ajax({
        url: `/admin/users/${userId}/activity`,
        method: 'GET',
        success: function(response) {
            hideLoadingModal();
            showActivityModal(response.data, userId);
        },
        error: function() {
            hideLoadingModal();
            showErrorToast('Failed to load user activity');
        }
    });
}

/**
 * Show ecommerce details modal
 */
function showEcommerceDetailsModal(userId) {
    // Implementation for detailed analytics modal
    console.log('Show detailed analytics for user:', userId);
    
    // You can implement a comprehensive analytics modal here
    showInfoToast('Detailed analytics feature coming soon!');
}

/**
 * Refresh ecommerce metrics
 */
function refreshEcommerceMetrics() {
    // Refresh DataTables to get updated metrics
    if (typeof userDataTable !== 'undefined') {
        userDataTable.ajax.reload(null, false);
    }
}

/**
 * Show loading modal
 */
function showLoadingModal(message = 'Loading...') {
    $('#loadingModal .modal-body').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="fw-bold">${message}</div>
        </div>
    `);
    $('#loadingModal').modal('show');
}

/**
 * Hide loading modal
 */
function hideLoadingModal() {
    $('#loadingModal').modal('hide');
}

/**
 * Show success toast
 */
function showSuccessToast(message) {
    showToast(message, 'success');
}

/**
 * Show error toast
 */
function showErrorToast(message) {
    showToast(message, 'error');
}

/**
 * Show info toast
 */
function showInfoToast(message) {
    showToast(message, 'info');
}

/**
 * Fix dropdown positioning to prevent cutoff
 */
function fixDropdownPosition(dropdown, menu) {
    if (!menu.hasClass('show')) return;
    
    const button = dropdown.find('.dropdown-toggle');
    const buttonOffset = button.offset();
    const menuWidth = menu.outerWidth();
    const menuHeight = menu.outerHeight();
    const windowWidth = $(window).width();
    const windowHeight = $(window).height();
    const scrollTop = $(window).scrollTop();
    
    let left = buttonOffset.left;
    let top = buttonOffset.top + button.outerHeight() + 5;
    
    // Adjust horizontal position if menu goes off-screen
    if (left + menuWidth > windowWidth - 20) {
        left = windowWidth - menuWidth - 20;
    }
    if (left < 20) {
        left = 20;
    }
    
    // Adjust vertical position if menu goes off-screen
    if (top + menuHeight > windowHeight + scrollTop - 20) {
        // Show above the button instead
        top = buttonOffset.top - menuHeight - 5;
        
        // If still doesn't fit, adjust to fit in viewport
        if (top < scrollTop + 20) {
            top = scrollTop + 20;
            menu.css('max-height', (windowHeight - 60) + 'px');
        }
    }
    
    // Apply positioning
    menu.css({
        'position': 'fixed',
        'left': left + 'px',
        'top': top + 'px',
        'right': 'auto',
        'bottom': 'auto',
        'transform': 'none'
    });
}

/**
 * Generic toast function
 */
function showToast(message, type = 'info') {
    const iconMap = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };
    
    const colorMap = {
        success: 'success',
        error: 'danger',
        info: 'primary',
        warning: 'warning'
    };
    
    const toast = $(`
        <div class="toast align-items-center text-white bg-${colorMap[type]} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${iconMap[type]} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);
    
    $('#toastContainer').append(toast);
    toast.toast('show');
    
    // Remove toast after it's hidden
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

// Placeholder functions for modals (implement as needed)
function showOrdersModal(data, userId) {
    console.log('Show orders modal for user:', userId, data);
    showInfoToast('Orders modal implementation needed');
}

function showWishlistModal(data, userId) {
    console.log('Show wishlist modal for user:', userId, data);
    showInfoToast('Wishlist modal implementation needed');
}

function showCartModal(data, userId) {
    console.log('Show cart modal for user:', userId, data);
    showInfoToast('Cart modal implementation needed');
}

function showPaymentsModal(data, userId) {
    console.log('Show payments modal for user:', userId, data);
    showInfoToast('Payments modal implementation needed');
}

function showActivityModal(data, userId) {
    console.log('Show activity modal for user:', userId, data);
    showInfoToast('Activity modal implementation needed');
}