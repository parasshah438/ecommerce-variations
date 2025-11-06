@extends('layouts.app')

@section('title', 'My Orders')


@push('styles')
<style>
    /* Orders Page Dark Mode Support */
    .card {
        background: var(--card-bg);
        border-color: var(--border-color);
        transition: transform 0.2s ease-in-out, box-shadow 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
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
    
    .form-label {
        color: var(--text-primary);
    }
    
    .form-control, .form-select {
        background: var(--card-bg);
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    
    .form-control:focus, .form-select:focus {
        background: var(--card-bg);
        border-color: var(--primary-color);
        color: var(--text-primary);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }
    
    .form-control::placeholder {
        color: var(--text-secondary);
    }
    
    .input-group .btn {
        border-color: var(--border-color);
    }
    
    /* Badge Styling */
    .badge {
        font-size: 0.75rem;
    }
    
    .badge.bg-light {
        background-color: var(--sidebar-hover) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-color);
    }
    
    /* Modal Dark Mode */
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
    
    .btn-close {
        filter: var(--text-primary);
    }
    
    [data-theme="dark"] .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    
    /* Alert Dark Mode */
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
    
    /* Form Check Dark Mode */
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
    
    /* Toast Dark Mode */
    .toast {
        min-width: 300px;
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
    
    /* Empty State Icon */
    [data-theme="dark"] .fa-shopping-bag {
        opacity: 0.5;
    }
    
    /* Pagination Dark Mode */
    .pagination {
        --bs-pagination-bg: var(--card-bg);
        --bs-pagination-border-color: var(--border-color);
        --bs-pagination-color: var(--text-primary);
        --bs-pagination-hover-bg: var(--sidebar-hover);
        --bs-pagination-hover-border-color: var(--border-color);
        --bs-pagination-active-bg: var(--primary-color);
        --bs-pagination-active-border-color: var(--primary-color);
    }
    
    /* Smooth transitions for theme switching */
    .card, .form-control, .form-select, .modal-content, .alert, .toast {
        transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }
    
    @media (max-width: 768px) {
        .d-flex.gap-2 {
            flex-direction: column;
        }
        
        .d-flex.gap-2 > * {
            margin-bottom: 0.5rem;
        }
    }
</style>
@endpush


@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">My Orders</h1>
                    <p class="text-muted">Track and manage your order history</p>
                </div>
                <div class="d-none d-md-block">
                    <span class="badge bg-primary fs-6">{{ $orders->total() }} Total Orders</span>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('orders.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Orders</option>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search Order</label>
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="Order ID..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders List -->
            @if($orders->count() > 0)
                <div class="row">
                    @foreach($orders as $order)
                        <div class="col-12 mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-body">
                                    <!-- Order Header -->
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-center">
                                                <h5 class="card-title mb-0 me-3">Order #{{ $order->id }}</h5>
                                                <span class="badge 
                                                    @if($order->status === 'delivered') bg-success
                                                    @elseif($order->status === 'cancelled') bg-danger
                                                    @elseif($order->status === 'shipped') bg-info
                                                    @elseif($order->status === 'processing') bg-warning
                                                    @else bg-secondary
                                                    @endif">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <div class="h5 mb-0">₹{{ number_format($order->total, 2) }}</div>
                                            <small class="text-muted">{{ ucfirst($order->payment_method) }}</small>
                                        </div>
                                    </div>

                                    <!-- Order Items Preview -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted me-2">Items:</span>
                                                <div class="d-flex flex-wrap">
                                                    @foreach($order->items->take(3) as $item)
                                                        <span class="badge bg-light text-dark me-1 mb-1">
                                                            {{ $item->productVariation->product->name }}
                                                            @if($item->quantity > 1)
                                                                ({{ $item->quantity }})
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                    @if($order->items->count() > 3)
                                                        <span class="badge bg-secondary">+{{ $order->items->count() - 3 }} more</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delivery Address -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $order->address->address_line }}, {{ $order->address->city }}, {{ $order->address->state }} - {{ $order->address->zip }}
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('order.details', $order) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>View Details
                                                </a>
                                                <a href="{{ route('order.track', $order) }}" class="btn btn-outline-info btn-sm">
                                                    <i class="fas fa-truck me-1"></i>Track Order
                                                </a>
                                                
                                                @if($order->status === 'delivered')
                                                    <a href="{{ route('order.invoice', $order) }}" class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-download me-1"></i>Invoice
                                                    </a>
                                                @endif

                                                @if(in_array($order->status, ['pending', 'confirmed']))
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            data-bs-toggle="modal" data-bs-target="#cancelModal{{ $order->id }}">
                                                        <i class="fas fa-times me-1"></i>Cancel
                                                    </button>
                                                @endif

                                                @if($order->status === 'delivered')
                                                    <button type="button" class="btn btn-outline-warning btn-sm" 
                                                            data-bs-toggle="modal" data-bs-target="#returnModal{{ $order->id }}">
                                                        <i class="fas fa-undo me-1"></i>Return
                                                    </button>
                                                @endif

                                                <form action="{{ route('order.reorder', $order) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-shopping-cart me-1"></i>Reorder
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cancel Modal -->
                        <div class="modal fade" id="cancelModal{{ $order->id }}" tabindex="-1">
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
                                                <label for="reason{{ $order->id }}" class="form-label">Reason for cancellation</label>
                                                <textarea name="reason" id="reason{{ $order->id }}" class="form-control" rows="3" required></textarea>
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

                        <!-- Return Modal -->
                        @if($order->status === 'delivered')
                            <div class="modal fade" id="returnModal{{ $order->id }}" tabindex="-1">
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
                                                                {{ $item->productVariation->product->name }} (Qty: {{ $item->quantity }})
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="mb-3">
                                                    <label for="returnReason{{ $order->id }}" class="form-label">Reason for return</label>
                                                    <textarea name="reason" id="returnReason{{ $order->id }}" class="form-control" rows="3" required></textarea>
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
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $orders->withQueryString()->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-shopping-bag text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-muted">No Orders Found</h4>
                    <p class="text-muted">You haven't placed any orders yet.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

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


@push('scripts')
<script>
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
