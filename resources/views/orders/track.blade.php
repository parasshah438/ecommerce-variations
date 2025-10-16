@extends('layouts.app')

@section('title', 'Track Order - #' . $order->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Track Order #{{ $order->id }}</h1>
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
                        <a href="{{ route('order.details', $order) }}" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="fas fa-eye me-1"></i>View Details
                        </a>
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to Orders
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Tracking Timeline -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-route me-2"></i>Order Timeline
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="tracking-timeline">
                                @foreach($trackingSteps as $step => $details)
                                    <div class="timeline-item {{ $details['completed'] ? 'completed' : 'pending' }}">
                                        <div class="timeline-marker">
                                            @if($details['completed'])
                                                <i class="fas fa-check-circle"></i>
                                            @else
                                                <i class="far fa-circle"></i>
                                            @endif
                                        </div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ $details['title'] }}</h6>
                                            <p class="timeline-description">{{ $details['description'] }}</p>
                                            @if($details['timestamp'])
                                                <small class="timeline-time">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $details['timestamp']->format('M d, Y \a\t h:i A') }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($order->status === 'shipped')
                                <div class="alert alert-info mt-4">
                                    <i class="fas fa-truck me-2"></i>
                                    <strong>Your order is on the way!</strong> 
                                    Expected delivery within 2-3 business days.
                                </div>
                            @elseif($order->status === 'delivered')
                                <div class="alert alert-success mt-4">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Order delivered successfully!</strong> 
                                    Thank you for shopping with us.
                                </div>
                            @elseif($order->status === 'cancelled')
                                <div class="alert alert-danger mt-4">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <strong>Order has been cancelled.</strong>
                                    @if($order->notes)
                                        <br>Reason: {{ $order->notes }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <!-- Quick Info Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Quick Info
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="text-center">
                                        <div class="h4 mb-0">{{ $order->items->count() }}</div>
                                        <small class="text-muted">Items</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <div class="h4 mb-0">₹{{ number_format($order->total, 2) }}</div>
                                        <small class="text-muted">Total</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Payment Method:</span>
                                <span>{{ ucfirst($order->payment_method) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Payment Status:</span>
                                <span class="badge 
                                    @if($order->payment_status === 'paid') bg-success
                                    @elseif($order->payment_status === 'failed') bg-danger
                                    @else bg-warning
                                    @endif">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
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

                    <!-- Order Items Preview -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-box me-2"></i>Items in Order
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @foreach($order->items as $item)
                                <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="row align-items-center">
                                        <div class="col-3">
                                            @if($item->productVariation->product->images->count() > 0)
                                                <img src="{{ asset('storage/' . $item->productVariation->product->images->first()->image_path) }}" 
                                                     alt="{{ $item->productVariation->product->name }}"
                                                     class="img-fluid rounded">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-9">
                                            <h6 class="mb-1">{{ $item->productVariation->product->name }}</h6>
                                            <small class="text-muted">Qty: {{ $item->quantity }}</small>
                                            <div class="fw-bold">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Support Information -->
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-light border">
                <div class="row text-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <i class="fas fa-headset text-primary fs-2"></i>
                        <h6 class="mt-2">Need Help?</h6>
                        <p class="mb-0 small">Contact our support team for any queries</p>
                        <a href="#" class="btn btn-outline-primary btn-sm mt-2">Contact Support</a>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <i class="fas fa-undo text-warning fs-2"></i>
                        <h6 class="mt-2">Easy Returns</h6>
                        <p class="mb-0 small">30-day return policy on most items</p>
                        @if($order->status === 'delivered')
                            <button class="btn btn-outline-warning btn-sm mt-2" 
                                    data-bs-toggle="modal" data-bs-target="#returnModal">
                                Return Items
                            </button>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-shipping-fast text-success fs-2"></i>
                        <h6 class="mt-2">Fast Delivery</h6>
                        <p class="mb-0 small">Free shipping on orders above ₹500</p>
                        <form action="{{ route('order.reorder', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm mt-2">
                                Reorder
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                                        {{ $item->productVariation->product->name }} (Qty: {{ $item->quantity }})
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
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .tracking-timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .tracking-timeline::before {
        content: '';
        position: absolute;
        left: 1rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }
    
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    
    .timeline-marker {
        position: absolute;
        left: -2rem;
        top: 0;
        width: 2rem;
        height: 2rem;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }
    
    .timeline-item.completed .timeline-marker {
        color: #198754;
        border: 2px solid #198754;
    }
    
    .timeline-item.completed .timeline-marker::before {
        content: '';
        position: absolute;
        left: -0.75rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1.5rem;
        height: 2px;
        background: #198754;
        z-index: -1;
    }
    
    .timeline-item.pending .timeline-marker {
        color: #6c757d;
        border: 2px solid #6c757d;
    }
    
    .timeline-content {
        padding-left: 1rem;
    }
    
    .timeline-title {
        margin-bottom: 0.25rem;
        font-weight: 600;
    }
    
    .timeline-description {
        margin-bottom: 0.5rem;
        color: #6c757d;
    }
    
    .timeline-time {
        color: #6c757d;
        font-size: 0.875rem;
    }
    
    .timeline-item.completed .timeline-title {
        color: #198754;
    }
    
    address {
        line-height: 1.6;
    }
    
    @media (max-width: 768px) {
        .tracking-timeline {
            padding-left: 1.5rem;
        }
        
        .timeline-marker {
            left: -1.5rem;
        }
        
        .timeline-content {
            padding-left: 0.5rem;
        }
    }
</style>
@endpush