@extends('layouts.admin')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">Order #{{ $order->id }}</h2>
            <p class="text-muted mb-0">Created {{ $order->created_at->format('M d, Y \a\t H:i A') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cog me-2"></i>Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="printOrder()"><i class="fas fa-print me-2"></i>Print Order</a></li>
                    <li><a class="dropdown-item" href="#" onclick="downloadInvoice()"><i class="fas fa-download me-2"></i>Download Invoice</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="sendOrderEmail()"><i class="fas fa-envelope me-2"></i>Send Order Email</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Status and Payment Info Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-info-circle text-primary me-2"></i>Order Status
                    </h5>
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : ($order->status === 'processing' ? 'info' : 'warning')) }} fs-6 me-3">
                            {{ $order->formatted_status }}
                        </span>
                        @if($order->status !== 'cancelled' && $order->status !== 'returned' && $order->status !== 'delivered')
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statusModal">
                                <i class="fas fa-edit me-1"></i>Change Status
                            </button>
                        @endif
                    </div>
                    
                    <!-- Status Timeline -->
                    <div class="status-timeline">
                        <div class="timeline-item {{ $order->status === 'pending' ? 'active' : 'completed' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Placed</h6>
                                <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        <div class="timeline-item {{ in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered']) ? 'completed' : ($order->status === 'pending' ? 'active' : '') }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Confirmed</h6>
                                <small class="text-muted">{{ $order->status !== 'pending' ? 'Confirmed' : 'Waiting for confirmation' }}</small>
                            </div>
                        </div>
                        <div class="timeline-item {{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'completed' : ($order->status === 'confirmed' ? 'active' : '') }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Processing</h6>
                                <small class="text-muted">{{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'In progress' : 'Pending' }}</small>
                            </div>
                        </div>
                        <div class="timeline-item {{ in_array($order->status, ['shipped', 'delivered']) ? 'completed' : ($order->status === 'processing' ? 'active' : '') }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Shipped</h6>
                                <small class="text-muted">{{ in_array($order->status, ['shipped', 'delivered']) ? 'Package shipped' : 'Pending shipment' }}</small>
                            </div>
                        </div>
                        <div class="timeline-item {{ $order->status === 'delivered' ? 'completed' : ($order->status === 'shipped' ? 'active' : '') }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Delivered</h6>
                                <small class="text-muted">{{ $order->status === 'delivered' ? 'Order delivered' : 'Awaiting delivery' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-credit-card text-success me-2"></i>Payment Information
                    </h5>
                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-2"><strong>Payment Method:</strong></p>
                            <p class="text-muted mb-3">{{ ucfirst($order->payment_method ?? 'Not specified') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-2"><strong>Payment Status:</strong></p>
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }} mb-3">
                                {{ $order->formatted_payment_status }}
                            </span>
                        </div>
                    </div>
                    
                    @if($order->payment_gateway)
                        <p class="mb-2"><strong>Gateway:</strong> {{ ucfirst($order->payment_gateway) }}</p>
                    @endif
                    
                    @if($order->razorpay_payment_id)
                        <p class="mb-2"><strong>Transaction ID:</strong> <code>{{ $order->razorpay_payment_id }}</code></p>
                    @endif
                    
                    @if($order->payment_status !== 'paid' && $order->status !== 'cancelled')
                        <button class="btn btn-sm btn-outline-success mt-2" onclick="markAsPaid()">
                            <i class="fas fa-check me-1"></i>Mark as Paid
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Row -->
    <div class="row">
        <!-- Customer Information -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-user text-info me-2"></i>Customer Information
                    </h5>
                    <div class="customer-info">
                        <p class="mb-2"><strong>{{ $order->user->name }}</strong></p>
                        <p class="text-muted mb-2"><i class="fas fa-envelope me-2"></i>{{ $order->user->email }}</p>
                        @if($order->user->phone)
                            <p class="text-muted mb-2"><i class="fas fa-phone me-2"></i>{{ $order->user->phone }}</p>
                        @endif
                        <p class="text-muted mb-0"><i class="fas fa-calendar me-2"></i>Customer since {{ $order->user->created_at->format('M Y') }}</p>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.show', $order->user) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="fas fa-user me-1"></i>View Profile
                        </a>
                        <a href="{{ route('admin.users.orders', $order->user) }}" class="btn btn-sm btn-outline-info flex-fill">
                            <i class="fas fa-shopping-bag me-1"></i>All Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Address -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-map-marker-alt text-warning me-2"></i>Shipping Address
                    </h5>
                    @if($order->address)
                        <div class="shipping-address">
                            <p class="mb-1"><strong>{{ $order->address->name }}</strong></p>
                            <p class="mb-1">{{ $order->address->address_line_1 }}</p>
                            @if($order->address->address_line_2)
                                <p class="mb-1">{{ $order->address->address_line_2 }}</p>
                            @endif
                            <p class="mb-1">{{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->postal_code }}</p>
                            <p class="mb-1">{{ $order->address->country }}</p>
                            @if($order->address->phone)
                                <p class="text-muted mb-0"><i class="fas fa-phone me-2"></i>{{ $order->address->phone }}</p>
                            @endif
                        </div>
                    @else
                        <p class="text-muted">No shipping address available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-calculator text-success me-2"></i>Order Summary
                    </h5>
                    <div class="order-summary">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>₹{{ number_format($order->subtotal ?? $order->total, 2) }}</span>
                        </div>
                        @if($order->tax_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax ({{ $order->tax_name ?? 'GST' }} @ {{ $order->tax_rate }}%):</span>
                                <span>₹{{ number_format($order->tax_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->shipping_cost > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>₹{{ number_format($order->shipping_cost, 2) }}</span>
                            </div>
                        @endif
                        @if($order->coupon_discount > 0)
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Discount ({{ $order->coupon_code }}):</span>
                                <span>-₹{{ number_format($order->coupon_discount, 2) }}</span>
                            </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between h5 mb-0">
                            <strong>Total:</strong>
                            <strong>₹{{ number_format($order->total, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-4">
                <i class="fas fa-shopping-cart text-primary me-2"></i>Order Items ({{ $order->items->count() }} items)
            </h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Variations</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->productVariation && $item->productVariation->product && $item->productVariation->product->images->isNotEmpty())
                                            <img src="{{ asset('storage/' . $item->productVariation->product->images->first()->image_path) }}" 
                                                 alt="{{ $item->productVariation->product->name }}" 
                                                 class="rounded me-3" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-1">{{ $item->productVariation->product->name ?? 'Product Deleted' }}</h6>
                                            <small class="text-muted">SKU: {{ $item->productVariation->sku ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($item->productVariation && $item->productVariation->attributeValues->isNotEmpty())
                                        @foreach($item->productVariation->attributeValues as $attributeValue)
                                            <span class="badge bg-light text-dark me-1">{{ $attributeValue->attribute->name }}: {{ $attributeValue->value }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No variations</span>
                                    @endif
                                </td>
                                <td>₹{{ number_format($item->price, 2) }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $item->quantity }}</span>
                                </td>
                                <td>
                                    <strong>₹{{ number_format($item->price * $item->quantity, 2) }}</strong>
                                </td>
                                <td>
                                    @if($item->productVariation && $item->productVariation->product)
                                        <a href="{{ route('admin.products.show', $item->productVariation->product) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">Product deleted</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($order->notes)
        <!-- Order Notes -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-sticky-note text-warning me-2"></i>Order Notes
                </h5>
                <div class="alert alert-info">
                    {{ $order->notes }}
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    @if($order->status !== 'cancelled' && $order->status !== 'returned')
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-bolt text-warning me-2"></i>Quick Actions
                </h5>
                <div class="row">
                    @if($order->canBeCancelled())
                        <div class="col-md-4 mb-2">
                            <button class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="fas fa-times me-2"></i>Cancel Order
                            </button>
                        </div>
                    @endif
                    @if($order->canBeReturned())
                        <div class="col-md-4 mb-2">
                            <button class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#returnModal">
                                <i class="fas fa-undo me-2"></i>Process Return
                            </button>
                        </div>
                    @endif
                    @if($order->status === 'pending')
                        <div class="col-md-4 mb-2">
                            <form action="{{ route('admin.orders.confirm', $order) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-outline-success w-100" onclick="return confirm('Are you sure you want to confirm this order?')">
                                    <i class="fas fa-check me-2"></i>Confirm Order
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@include('admin.orders.modals.status-modal')
@include('admin.orders.modals.cancel-modal')
@include('admin.orders.modals.return-modal')
@endsection

@push('styles')
<style>
.status-timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 12px;
    top: 30px;
    width: 2px;
    height: calc(100% + 20px);
    background-color: #e2e8f0;
}

.timeline-item.completed::after {
    background-color: #10b981;
}

.timeline-item.active::after {
    background-color: #3b82f6;
}

.timeline-marker {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 3px solid #e2e8f0;
    background-color: white;
    margin-right: 15px;
    flex-shrink: 0;
    z-index: 1;
}

.timeline-item.completed .timeline-marker {
    border-color: #10b981;
    background-color: #10b981;
}

.timeline-item.active .timeline-marker {
    border-color: #3b82f6;
    background-color: #3b82f6;
}

.timeline-content h6 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 2px;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.customer-info p {
    margin-bottom: 8px;
}

.badge {
    font-size: 0.75em;
}

@media print {
    .btn, .dropdown, .card-footer, .quick-actions {
        display: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function printOrder() {
    window.print();
}

function downloadInvoice() {
    // Add invoice download functionality
    alert('Invoice download functionality to be implemented');
}

function sendOrderEmail() {
    // Add email functionality
    alert('Email functionality to be implemented');
}

function markAsPaid() {
    if (confirm('Are you sure you want to mark this order as paid?')) {
        // Add AJAX request to mark as paid
        alert('Mark as paid functionality to be implemented');
    }
}

// Add form validation and AJAX handling
document.addEventListener('DOMContentLoaded', function() {
    // Status update form handling
    const statusForm = document.getElementById('statusUpdateForm');
    if (statusForm) {
        statusForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Add AJAX handling here
        });
    }
});
</script>
@endpush