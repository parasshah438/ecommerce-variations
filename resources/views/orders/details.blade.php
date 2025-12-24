@extends('layouts.app')

@section('title', 'Order Details - #' . $order->id)

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Order #{{ $order->id }}</h1>
                    <p class="text-muted mb-0">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
                <div class="text-end">
                    <span class="badge fs-6 
                        @if($order->status === 'delivered') bg-success
                        @elseif($order->status === 'cancelled') bg-danger
                        @elseif($order->status === 'shipped') bg-info
                        @elseif($order->status === 'processing') bg-warning
                        @else bg-secondary
                        @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                    <div class="mt-1">
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to Orders
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Order Items -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-box me-2"></i>Order Items ({{ $order->items->count() }})
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @foreach($order->items as $item)
                                <div class="border-bottom p-3 {{ $loop->last ? '' : 'border-bottom' }}">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 col-3">
                                            @if($item->productVariation->product->images->count() > 0)
                                                <img src="{{ $item->productVariation->product->getThumbnailImage() ? $item->productVariation->product->getThumbnailImage()->getThumbnailUrl(150) : asset('images/product-placeholder.jpg') }}" 
                                                     alt="{{ $item->productVariation->product->name }}"
                                                     class="img-fluid rounded">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-6 col-9">
                                            <h6 class="mb-1">{{ $item->productVariation->product->name }}</h6>
                                            <p class="text-muted mb-1">SKU: {{ $item->productVariation->sku }}</p>
                                            @if($item->productVariation->attributeValues->count() > 0)
                                                <div class="d-flex flex-wrap">
                                                    @foreach($item->productVariation->attributeValues as $attrValue)
                                                        <span class="badge bg-light text-dark me-1 mb-1">
                                                            {{ $attrValue->attribute->name }}: {{ $attrValue->value }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-2 col-6 text-center">
                                            <span class="fw-bold">Qty: {{ $item->quantity }}</span>
                                        </div>
                                        <div class="col-md-2 col-6 text-end">
                                            <div class="fw-bold">₹{{ number_format($item->price, 2) }}</div>
                                            @if($item->quantity > 1)
                                                <small class="text-muted">₹{{ number_format($item->price * $item->quantity, 2) }} total</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <!-- Order Summary Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-receipt me-2"></i>Order Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>₹{{ number_format($order->total, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span class="text-success">Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total:</span>
                                <span>₹{{ number_format($order->total, 2) }}</span>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">Payment Method: {{ ucfirst($order->payment_method) }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-map-marker-alt me-2"></i>Delivery Address
                            </h5>
                        </div>
                        <div class="card-body">
                            <address class="mb-0">
                                <strong>{{ $order->address->name }}</strong><br>
                                {{ $order->address->address_line }}<br>
                                {{ $order->address->city }}, {{ $order->address->state }} - {{ $order->address->zip }}<br>
                                {{ $order->address->country }}<br>
                                <i class="fas fa-phone me-1"></i>{{ $order->address->phone }}
                            </address>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    @if($order->payments->count() > 0)
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-credit-card me-2"></i>Payment Information
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach($order->payments as $payment)
                                    <div class="mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Payment ID:</span>
                                            <span class="font-monospace">{{ $payment->payment_id }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Gateway:</span>
                                            <span>{{ ucfirst($payment->gateway) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Status:</span>
                                            <span class="badge 
                                                @if($payment->payment_status === 'paid') bg-success
                                                @elseif($payment->payment_status === 'failed') bg-danger
                                                @else bg-warning
                                                @endif">
                                                {{ ucfirst($payment->payment_status) }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Amount:</span>
                                            <span>₹{{ number_format($payment->amount, 2) }}</span>
                                        </div>
                                        @if($payment->refund_id)
                                            <div class="mt-2 p-2 bg-light rounded">
                                                <small class="text-muted">
                                                    <i class="fas fa-undo me-1"></i>
                                                    Refund ID: {{ $payment->refund_id }}<br>
                                                    Refund Amount: ₹{{ number_format($payment->refund_amount, 2) }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('order.track', $order) }}" class="btn btn-primary">
                                    <i class="fas fa-truck me-2"></i>Track Order
                                </a>
                                
                                @if($order->status === 'delivered')
                                    <a href="{{ route('order.invoice', $order) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-download me-2"></i>Download Invoice
                                    </a>
                                    <a href="{{ route('order.receipt', $order) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-file-pdf me-2"></i>Download Receipt
                                    </a>
                                @endif

                                @if(in_array($order->status, ['pending', 'confirmed']))
                                    <button type="button" class="btn btn-outline-danger" 
                                            data-bs-toggle="modal" data-bs-target="#cancelModal">
                                        <i class="fas fa-times me-2"></i>Cancel Order
                                    </button>
                                @endif

                                @if($order->status === 'delivered')
                                    <button type="button" class="btn btn-outline-warning" 
                                            data-bs-toggle="modal" data-bs-target="#returnModal">
                                        <i class="fas fa-undo me-2"></i>Return Order
                                    </button>
                                @endif

                                <form action="{{ route('order.reorder', $order) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success w-100">
                                        <i class="fas fa-shopping-cart me-2"></i>Reorder Items
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Cancel Modal -->
@if(in_array($order->status, ['pending', 'confirmed']))
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Order #{{ $order->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('order.cancel', $order) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for cancellation</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This action cannot be undone. Your order will be cancelled immediately.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Order</button>
                        <button type="submit" class="btn btn-danger">Cancel Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Return Modal -->
@if($order->status === 'delivered')
    <div class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Return Order #{{ $order->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('order.return', $order) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select items to return:</label>
                            @foreach($order->items as $item)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="return_items[]" value="{{ $item->id }}" 
                                           id="returnItem{{ $item->id }}">
                                    <label class="form-check-label" for="returnItem{{ $item->id }}">
                                        @if($item->productVariation && $item->productVariation->product)
                                            {{ $item->productVariation->product->name }} (Qty: {{ $item->quantity }})
                                        @else
                                            Product unavailable (Qty: {{ $item->quantity }})
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="mb-3">
                            <label for="returnReason" class="form-label">Reason for return</label>
                            <textarea name="reason" id="returnReason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            We will review your return request and contact you within 2-3 business days.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Submit Return Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Toast Notifications -->
@if(session('success'))
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast show" role="alert">
            <div class="toast-header">
                <i class="fas fa-check-circle text-success me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast show" role="alert">
            <div class="toast-header">
                <i class="fas fa-exclamation-circle text-danger me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ session('error') }}
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    /* Dark Mode Support for Order Details */
    .card {
        background: var(--card-bg);
        border-color: var(--border-color);
        transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        box-shadow: var(--shadow);
    }
    
    .card-header {
        background: var(--sidebar-hover) !important;
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    
    .card-title {
        color: var(--text-primary);
    }
    
    .card-body {
        color: var(--text-primary);
    }
    
    .text-muted {
        color: var(--text-secondary) !important;
    }
    
    h1, h2, h3, h4, h5, h6 {
        color: var(--text-primary);
    }
    
    .bg-light {
        background-color: var(--sidebar-hover) !important;
    }
    
    .badge.bg-light {
        background-color: var(--sidebar-hover) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-color);
    }
    
    /* Form elements */
    .form-control, .form-select {
        background: var(--card-bg);
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    
    .form-control:focus, .form-select:focus {
        background: var(--card-bg);
        border-color: var(--primary-color);
        color: var(--text-primary);
    }
    
    .form-label {
        color: var(--text-primary);
    }
    
    .form-check-input {
        background-color: var(--card-bg);
        border-color: var(--border-color);
    }
    
    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .form-check-label {
        color: var(--text-primary);
    }
    
    /* Modal */
    .modal-content {
        background: var(--card-bg);
        border-color: var(--border-color);
    }
    
    .modal-header, .modal-footer {
        border-color: var(--border-color);
    }
    
    .modal-title, .modal-body {
        color: var(--text-primary);
    }
    
    [data-theme="dark"] .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    
    /* Alert */
    .alert {
        background: var(--sidebar-hover);
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    
    .alert-warning {
        background: rgba(251, 146, 60, 0.1);
        border-color: rgba(251, 146, 60, 0.3);
        color: var(--text-primary);
    }
    
    .alert-info {
        background: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.3);
        color: var(--text-primary);
    }
    
    /* Toast */
    .toast {
        background: var(--card-bg);
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    
    .toast-header {
        background: var(--sidebar-hover);
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    
    .toast-body {
        background: var(--card-bg);
        color: var(--text-primary);
    }
    
    /* Border colors */
    .border, .border-bottom {
        border-color: var(--border-color) !important;
    }
    
    hr {
        border-color: var(--border-color);
        opacity: 0.3;
    }
    
    /* Address element */
    address {
        color: var(--text-primary);
        line-height: 1.6;
    }
    
    .font-monospace {
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.85rem;
        color: var(--text-primary);
    }
    
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
        }
        
        .card-body {
            padding: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-hide toasts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(toast => {
            setTimeout(() => {
                const bsToast = new bootstrap.Toast(toast);
                bsToast.hide();
            }, 5000);
        });
    });
</script>
@endpush