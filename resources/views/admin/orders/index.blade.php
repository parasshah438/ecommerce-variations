@extends('layouts.admin')

@section('title', 'Order Management')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">Order Management</h2>
            <p class="text-muted mb-0">Manage and track all customer orders</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-filter me-2"></i>Filter Orders
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportOrders('excel')"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportOrders('csv')"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportOrders('pdf')"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" onclick="refreshOrders()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Active Filters Indicator -->
    @if(request()->hasAny(['status', 'payment_status', 'date_from', 'date_to', 'amount_min', 'amount_max', 'customer_email', 'payment_method', 'search', 'quick_filter']))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-filter me-2"></i>
                        <strong>Active Filters:</strong>
                        @if(request('status'))
                            <span class="badge bg-primary me-1">Status: {{ ucfirst(request('status')) }}</span>
                        @endif
                        @if(request('payment_status'))
                            <span class="badge bg-success me-1">Payment: {{ ucfirst(request('payment_status')) }}</span>
                        @endif
                        @if(request('date_from') || request('date_to'))
                            <span class="badge bg-info me-1">
                                Date: {{ request('date_from') ? request('date_from') : 'Start' }} to {{ request('date_to') ? request('date_to') : 'End' }}
                            </span>
                        @endif
                        @if(request('amount_min') || request('amount_max'))
                            <span class="badge bg-warning me-1">
                                Amount: ₹{{ request('amount_min') ?: '0' }} - ₹{{ request('amount_max') ?: '∞' }}
                            </span>
                        @endif
                        @if(request('customer_email'))
                            <span class="badge bg-secondary me-1">Email: {{ request('customer_email') }}</span>
                        @endif
                        @if(request('payment_method'))
                            <span class="badge bg-dark me-1">Method: {{ ucfirst(request('payment_method')) }}</span>
                        @endif
                        @if(request('search'))
                            <span class="badge bg-light text-dark me-1">Search: "{{ request('search') }}"</span>
                        @endif
                        @if(request('quick_filter'))
                            <span class="badge bg-primary me-1">Period: {{ str_replace('_', ' ', ucwords(request('quick_filter'))) }}</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear All
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4" id="statisticsCards">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                    <h4 class="mb-0" id="totalOrders">{{ isset($stats) ? number_format($stats['total_orders']) : $orders->total() }}</h4>
                    <small class="text-muted">Total Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h4 class="mb-0" id="pendingOrders">{{ isset($stats) ? number_format($stats['pending_orders']) : 0 }}</h4>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h4 class="mb-0" id="confirmedOrders">{{ isset($stats) ? number_format($stats['confirmed_orders']) : 0 }}</h4>
                    <small class="text-muted">Confirmed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-shipping-fast fa-2x"></i>
                    </div>
                    <h4 class="mb-0" id="shippedOrders">{{ isset($stats) ? number_format($stats['shipped_orders']) : 0 }}</h4>
                    <small class="text-muted">Shipped</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-check fa-2x"></i>
                    </div>
                    <h4 class="mb-0" id="deliveredOrders">{{ isset($stats) ? number_format($stats['delivered_orders']) : 0 }}</h4>
                    <small class="text-muted">Delivered</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                    <h4 class="mb-0" id="cancelledOrders">{{ isset($stats) ? number_format($stats['cancelled_orders']) : 0 }}</h4>
                    <small class="text-muted">Cancelled</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        Orders 
                        @if(request()->hasAny(['status', 'payment_status', 'date_from', 'date_to', 'amount_min', 'amount_max', 'customer_email', 'payment_method', 'search', 'quick_filter']))
                            <span class="badge bg-info">Filtered</span>
                        @endif
                    </h5>
                    <small class="text-muted">
                        Showing {{ $orders->count() }} of {{ $orders->total() }} orders
                        @if(request()->hasAny(['status', 'payment_status', 'date_from', 'date_to', 'amount_min', 'amount_max', 'customer_email', 'payment_method', 'search', 'quick_filter']))
                            ({{ number_format($orders->total()) }} match your filters)
                        @endif
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Search orders..." id="searchInput" style="width: 200px;" value="{{ request('search') }}">
                    <button class="btn btn-sm btn-outline-secondary" onclick="bulkActions()">
                        <i class="fas fa-tasks me-1"></i>Bulk Actions
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr data-order-id="{{ $order->id }}">
                            <td class="ps-4">
                                <div class="form-check">
                                    <input class="form-check-input order-checkbox" type="checkbox" value="{{ $order->id }}">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h6 class="mb-0">#{{ $order->id }}</h6>
                                        <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-light rounded-circle me-3 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $order->user->name }}</h6>
                                        <small class="text-muted">{{ $order->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : ($order->status === 'processing' ? 'info' : 'warning')) }} rounded-pill status-badge">
                                    {{ $order->formatted_status }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }} rounded-pill">
                                    {{ $order->formatted_payment_status }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $order->items->count() }} items</span>
                            </td>
                            <td>
                                <strong>₹{{ number_format($order->total, 2) }}</strong>
                            </td>
                            <td>
                                <div>
                                    {{ $order->created_at->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $order->created_at->format('H:i A') }}</small>
                                </div>
                            </td>
                            <td class="text-center order-actions">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.orders.show', $order) }}">
                                                <i class="fas fa-eye me-2"></i>View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="downloadInvoice({{ $order->id }})">
                                                <i class="fas fa-file-invoice me-2"></i>Download Invoice
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        @if($order->status === App\Models\Order::STATUS_PENDING)
                                            <li>
                                                <a class="dropdown-item text-success" href="#" onclick="quickConfirmOrder({{ $order->id }})">
                                                    <i class="fas fa-check me-2"></i>Quick Confirm
                                                </a>
                                            </li>
                                        @endif
                                        @if($order->canBeCancelled())
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="quickCancelOrder({{ $order->id }})">
                                                    <i class="fas fa-times me-2"></i>Cancel Order
                                                </a>
                                            </li>
                                        @endif
                                        @if($order->canBeReturned())
                                            <li>
                                                <a class="dropdown-item text-warning" href="#" onclick="quickReturnOrder({{ $order->id }})">
                                                    <i class="fas fa-undo me-2"></i>Process Return
                                                </a>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="sendOrderEmail({{ $order->id }})">
                                                <i class="fas fa-envelope me-2"></i>Send Email
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                    <h5>No Orders Found</h5>
                                    <p>There are no orders to display at the moment.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} results
                    </small>
                </div>
                <div>
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.orders.modals.bulk-actions-modal')
@include('admin.orders.modals.filter-modal')
@endsection

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.badge {
    font-size: 0.75em;
    font-weight: 500;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #64748b;
    font-size: 0.875rem;
}

.table td {
    border-top: 1px solid #f1f5f9;
    padding: 1rem 0.75rem;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .dropdown-menu {
        font-size: 0.875rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Load statistics on page load
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterOrders();
        }, 500);
    });
    
    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    
    selectAllCheckbox.addEventListener('change', function() {
        orderCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
});

function loadStatistics() {
    fetch('{{ route("admin.orders.statistics") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalOrders').textContent = data.data.total_orders;
                document.getElementById('pendingOrders').textContent = data.data.pending_orders;
                document.getElementById('confirmedOrders').textContent = data.data.confirmed_orders;
                document.getElementById('shippedOrders').textContent = data.data.shipped_orders;
                document.getElementById('deliveredOrders').textContent = data.data.delivered_orders;
                document.getElementById('cancelledOrders').textContent = data.data.cancelled_orders;
            }
        })
        .catch(error => console.error('Error loading statistics:', error));
}

function refreshOrders() {
    location.reload();
}

function exportOrders(format) {
    window.open(`{{ route('admin.orders.export') }}?format=${format}`, '_blank');
}

function filterOrders() {
    const searchTerm = document.getElementById('searchInput').value;
    
    if (searchTerm.length >= 3 || searchTerm.length === 0) {
        // Get current URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        
        if (searchTerm) {
            urlParams.set('search', searchTerm);
        } else {
            urlParams.delete('search');
        }
        
        // Navigate to the filtered URL
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.location.href = newUrl;
    }
}

function quickConfirmOrder(orderId) {
    if (confirm('Are you sure you want to confirm this order?')) {
        // Show loading state
        const loadingToast = showToast('Processing order confirmation...', 'info');
        
        fetch(`/admin/orders/${orderId}/confirm`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Update the status badge in the table row
                updateOrderStatusInTable(orderId, data.order_status);
                // Refresh the page after a short delay to show updated data
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'An error occurred while confirming the order.';
            if (error.message) {
                errorMessage = error.message;
            }
            showToast('Error: ' + errorMessage, 'error');
        })
        .finally(() => {
            // Hide loading toast if exists
            if (loadingToast) {
                loadingToast.hide();
            }
        });
    }
}

function quickCancelOrder(orderId) {
    const reason = prompt('Please enter cancellation reason:');
    if (reason && reason.trim()) {
        const loadingToast = showToast('Processing order cancellation...', 'info');
        
        fetch(`/admin/orders/${orderId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                updateOrderStatusInTable(orderId, data.order_status);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'An error occurred while cancelling the order.';
            if (error.message) {
                errorMessage = error.message;
            }
            showToast('Error: ' + errorMessage, 'error');
        })
        .finally(() => {
            if (loadingToast) {
                loadingToast.hide();
            }
        });
    }
}

function quickReturnOrder(orderId) {
    const reason = prompt('Please enter return reason:');
    if (reason && reason.trim()) {
        fetch(`/admin/orders/${orderId}/return`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the return.');
        });
    }
}

function downloadInvoice(orderId) {
    window.open(`/admin/orders/${orderId}/invoice`, '_blank');
}

function sendOrderEmail(orderId) {
    // Implementation for sending order email
    alert('Email functionality to be implemented');
}

function bulkActions() {
    const selectedOrders = document.querySelectorAll('.order-checkbox:checked');
    if (selectedOrders.length === 0) {
        alert('Please select at least one order.');
        return;
    }
    // Show bulk actions modal
    const modal = new bootstrap.Modal(document.getElementById('bulkActionsModal'));
    modal.show();
}

// Helper function to show toast notifications
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => toast.remove());
    
    const toastHtml = `
        <div class="toast-notification position-fixed top-0 end-0 m-3" style="z-index: 9999;">
            <div class="toast show" role="alert">
                <div class="toast-header bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} text-white">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    <strong class="me-auto">${type === 'success' ? 'Success' : type === 'error' ? 'Error' : 'Info'}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.querySelector('.toast-notification:last-child .toast');
    const toast = new bootstrap.Toast(toastElement);
    
    // Auto-hide after 5 seconds for success/info, 8 seconds for error
    setTimeout(() => {
        if (toastElement) {
            toast.hide();
            setTimeout(() => {
                const notification = toastElement.closest('.toast-notification');
                if (notification) notification.remove();
            }, 300);
        }
    }, type === 'error' ? 8000 : 5000);
    
    return toast;
}

// Helper function to update order status in table without full reload
function updateOrderStatusInTable(orderId, newStatus) {
    const orderRow = document.querySelector(`tr[data-order-id="${orderId}"]`);
    if (orderRow) {
        const statusBadge = orderRow.querySelector('.status-badge');
        if (statusBadge) {
            // Update badge class and text
            statusBadge.className = `badge rounded-pill status-badge bg-${getStatusBadgeClass(newStatus)}`;
            statusBadge.textContent = newStatus;
        }
        
        // Update action buttons based on new status
        updateActionButtons(orderRow, newStatus);
    }
}

// Helper function to get appropriate badge class for status
function getStatusBadgeClass(status) {
    const statusClasses = {
        'delivered': 'success',
        'cancelled': 'danger', 
        'returned': 'warning',
        'processing': 'info',
        'shipped': 'primary',
        'confirmed': 'success',
        'pending': 'warning'
    };
    return statusClasses[status.toLowerCase()] || 'secondary';
}

// Helper function to update action buttons based on status
function updateActionButtons(orderRow, status) {
    const actionsCell = orderRow.querySelector('.order-actions');
    if (!actionsCell) return;
    
    // This would update the action buttons based on the new status
    // Implementation depends on your specific button structure
}
</script>
@endpush
