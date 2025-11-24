/**
 * User Ecommerce Management JavaScript
 * Advanced ecommerce features for user management
 */

$(document).ready(function() {
    
    // Ecommerce Action Handlers
    
    // View ecommerce details
    $(document).on('click', '.view-ecommerce-details', function() {
        const userId = $(this).data('id');
        
        $.ajax({
            url: `/admin/users/${userId}/ecommerce-details`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showEcommerceDetailsModal(response.data);
                } else {
                    showToast(response.message || 'Failed to load ecommerce details', 'error');
                }
            },
            error: function() {
                showToast('Error loading ecommerce details', 'error');
            }
        });
    });
    
    // View user orders
    $(document).on('click', '.view-user-orders', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        
        $.ajax({
            url: `/admin/users/${userId}/orders`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showUserOrdersModal(response.data, response.count);
                } else {
                    showToast(response.message || 'Failed to load orders', 'error');
                }
            }
        });
    });
    
    // View user wishlist
    $(document).on('click', '.view-user-wishlist', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        
        $.ajax({
            url: `/admin/users/${userId}/wishlist`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showUserWishlistModal(response.data, response.count);
                } else {
                    showToast(response.message || 'Failed to load wishlist', 'error');
                }
            }
        });
    });
    
    // View user cart
    $(document).on('click', '.view-user-cart', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        
        $.ajax({
            url: `/admin/users/${userId}/cart`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showUserCartModal(response.data, response.count);
                } else {
                    showToast(response.message || 'Failed to load cart', 'error');
                }
            }
        });
    });
    
    // View user payments
    $(document).on('click', '.view-user-payments', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        
        $.ajax({
            url: `/admin/users/${userId}/payments`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showUserPaymentsModal(response.data, response.count);
                } else {
                    showToast(response.message || 'Failed to load payments', 'error');
                }
            }
        });
    });
    
    // Send user email
    $(document).on('click', '.send-user-email', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        showSendEmailModal(userId);
    });

    // Modal functions for ecommerce features
    
    function showEcommerceDetailsModal(data) {
        const modalHtml = `
            <div class="modal fade" id="ecommerceDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ecommerce Details - ${data.user.name}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6><i class="fas fa-chart-line me-2"></i>Summary</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row text-center">
                                                <div class="col-6 mb-3">
                                                    <div class="text-danger h4">${data.metrics.wishlist_count}</div>
                                                    <small>Wishlist Items</small>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <div class="text-warning h4">${data.metrics.cart_count}</div>
                                                    <small>Cart Items</small>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <div class="text-info h4">${data.metrics.recent_views}</div>
                                                    <small>Recent Views</small>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <div class="text-primary h4">${data.metrics.total_orders}</div>
                                                    <small>Total Orders</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6><i class="fas fa-dollar-sign me-2"></i>Payment Summary</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Successful Payments:</span>
                                                <span class="text-success fw-bold">$${data.metrics.successful_payments.toFixed(2)}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Failed Payments:</span>
                                                <span class="text-danger fw-bold">$${data.metrics.failed_payments.toFixed(2)}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#ecommerceDetailsModal').remove();
        $('body').append(modalHtml);
        $('#ecommerceDetailsModal').modal('show');
    }
    
    function showUserOrdersModal(orders, count) {
        let ordersHtml = '';
        if (orders.length > 0) {
            orders.forEach(order => {
                ordersHtml += `
                    <tr>
                        <td>#${order.id}</td>
                        <td>$${order.total}</td>
                        <td><span class="badge bg-${getStatusColor(order.status)}">${order.status}</span></td>
                        <td><span class="badge bg-${getPaymentStatusColor(order.payment_status)}">${order.payment_status}</span></td>
                        <td>${new Date(order.created_at).toLocaleDateString()}</td>
                    </tr>
                `;
            });
        } else {
            ordersHtml = '<tr><td colspan="5" class="text-center">No orders found</td></tr>';
        }
        
        const modalHtml = `
            <div class="modal fade" id="userOrdersModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-shopping-bag me-2"></i>User Orders (${count})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${ordersHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#userOrdersModal').remove();
        $('body').append(modalHtml);
        $('#userOrdersModal').modal('show');
    }
    
    function showUserWishlistModal(wishlist, count) {
        let wishlistHtml = '';
        if (wishlist.length > 0) {
            wishlist.forEach(item => {
                wishlistHtml += `
                    <tr>
                        <td>${item.product_name}</td>
                        <td>$${item.price}</td>
                        <td>${new Date(item.added_at).toLocaleDateString()}</td>
                    </tr>
                `;
            });
        } else {
            wishlistHtml = '<tr><td colspan="3" class="text-center">No wishlist items found</td></tr>';
        }
        
        const modalHtml = `
            <div class="modal fade" id="userWishlistModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-heart me-2"></i>User Wishlist (${count})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Added Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${wishlistHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#userWishlistModal').remove();
        $('body').append(modalHtml);
        $('#userWishlistModal').modal('show');
    }
    
    function showUserCartModal(cart, count) {
        let cartHtml = '';
        if (cart.length > 0) {
            cart.forEach(item => {
                cartHtml += `
                    <tr>
                        <td>${item.product_name}</td>
                        <td>${item.quantity}</td>
                        <td>$${item.unit_price}</td>
                        <td>$${item.total_price}</td>
                        <td>${new Date(item.updated_at).toLocaleDateString()}</td>
                    </tr>
                `;
            });
        } else {
            cartHtml = '<tr><td colspan="5" class="text-center">No cart items found</td></tr>';
        }
        
        const modalHtml = `
            <div class="modal fade" id="userCartModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-shopping-cart me-2"></i>User Cart (${count})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th>Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${cartHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#userCartModal').remove();
        $('body').append(modalHtml);
        $('#userCartModal').modal('show');
    }
    
    function showUserPaymentsModal(payments, count) {
        let paymentsHtml = '';
        if (payments.length > 0) {
            payments.forEach(payment => {
                paymentsHtml += `
                    <tr>
                        <td>#${payment.id}</td>
                        <td>$${payment.amount}</td>
                        <td><span class="badge bg-${getPaymentStatusColor(payment.status)}">${payment.status}</span></td>
                        <td>${payment.payment_method}</td>
                        <td>${payment.transaction_id}</td>
                        <td>${new Date(payment.created_at).toLocaleDateString()}</td>
                    </tr>
                `;
            });
        } else {
            paymentsHtml = '<tr><td colspan="6" class="text-center">No payments found</td></tr>';
        }
        
        const modalHtml = `
            <div class="modal fade" id="userPaymentsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Payment History (${count})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Payment ID</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Method</th>
                                            <th>Transaction ID</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${paymentsHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#userPaymentsModal').remove();
        $('body').append(modalHtml);
        $('#userPaymentsModal').modal('show');
    }
    
    function showSendEmailModal(userId) {
        const modalHtml = `
            <div class="modal fade" id="sendEmailModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-envelope me-2"></i>Send Email</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="sendEmailForm">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Email Type</label>
                                    <select class="form-select" name="email_type" required>
                                        <option value="">Select Type</option>
                                        <option value="promotional">Promotional</option>
                                        <option value="notification">Notification</option>
                                        <option value="support">Support</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Subject</label>
                                    <input type="text" class="form-control" name="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Message</label>
                                    <textarea class="form-control" name="message" rows="5" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Send Email
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        $('#sendEmailModal').remove();
        $('body').append(modalHtml);
        $('#sendEmailModal').modal('show');
        
        // Handle form submission
        $('#sendEmailForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            $.ajax({
                url: `/admin/users/${userId}/send-email`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#sendEmailModal').modal('hide');
                        showToast(response.message, 'success');
                    } else {
                        showToast(response.message || 'Failed to send email', 'error');
                    }
                },
                error: function() {
                    showToast('Error sending email', 'error');
                }
            });
        });
    }
    
    // Helper functions for status colors
    function getStatusColor(status) {
        switch(status) {
            case 'completed': return 'success';
            case 'pending': return 'warning';
            case 'cancelled': return 'danger';
            case 'processing': return 'info';
            default: return 'secondary';
        }
    }
    
    function getPaymentStatusColor(status) {
        switch(status) {
            case 'paid': 
            case 'completed': 
            case 'success': return 'success';
            case 'pending': return 'warning';
            case 'failed': 
            case 'cancelled': return 'danger';
            default: return 'secondary';
        }
    }
    
    // Add showToast function if not already defined
    if (typeof showToast === 'undefined') {
        window.showToast = function(message, type = 'info') {
            // Simple toast implementation
            const alertClass = type === 'success' ? 'alert-success' : 
                              type === 'error' ? 'alert-danger' : 
                              type === 'warning' ? 'alert-warning' : 'alert-info';
            
            const toast = $(`
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            
            $('body').append(toast);
            
            setTimeout(function() {
                toast.alert('close');
            }, 5000);
        }
    }
    
    // Make functions globally available
    window.showEcommerceDetailsModal = showEcommerceDetailsModal;
    window.showUserOrdersModal = showUserOrdersModal;
    window.showUserWishlistModal = showUserWishlistModal;
    window.showUserCartModal = showUserCartModal;
    window.showUserPaymentsModal = showUserPaymentsModal;
    window.showSendEmailModal = showSendEmailModal;
    window.getStatusColor = getStatusColor;
    window.getPaymentStatusColor = getPaymentStatusColor;
});