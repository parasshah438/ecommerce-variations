@extends('layouts.frontend')

@section('title', 'Order Tracking - ' . $order->order_number . ' - ' . config('app.name'))

@section('breadcrumb')
<div class="bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('order.track.public') }}">Track Order</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="container py-4">
    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Order Header -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-3">
                <div class="me-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-box-seam fs-2 text-primary"></i>
                    </div>
                </div>
                <div>
                    <h1 class="h3 mb-1">Order #{{ $order->order_number }}</h1>
                    <p class="text-muted mb-0">
                        Placed on {{ $order->created_at->format('M d, Y') }} at {{ $order->created_at->format('h:i A') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <div class="mb-2">
                @php
                    $statusColors = [
                        'pending' => 'warning',
                        'confirmed' => 'info', 
                        'processing' => 'primary',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'cancelled' => 'danger'
                    ];
                    $currentStatus = $order->status;
                    $statusColor = $statusColors[$currentStatus] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $statusColor }} fs-6 px-3 py-2">
                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            <div class="text-muted">
                <strong>Total: ₹{{ number_format($order->total, 2) }}</strong>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tracking Timeline -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-truck me-2 text-primary"></i>
                        Tracking Status
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if($order->status === 'cancelled')
                        <!-- Cancelled Order Display -->
                        <div class="text-center py-4">
                            <i class="bi bi-x-circle display-1 text-danger mb-3"></i>
                            <h4 class="text-danger mb-2">Order Cancelled</h4>
                            <p class="text-muted mb-3">This order was cancelled on {{ $order->cancelled_at ? $order->cancelled_at->format('M d, Y \a\t h:i A') : $order->updated_at->format('M d, Y \a\t h:i A') }}</p>
                            @if($order->notes)
                                <div class="alert alert-light">
                                    <strong>Reason:</strong> {{ $order->notes }}
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Normal Tracking Timeline -->
                        <div class="tracking-timeline">
                            @foreach($trackingSteps as $status => $step)
                                @if($status !== 'cancelled')
                                    <div class="timeline-item {{ $step['completed'] ? 'completed' : 'pending' }} {{ $loop->last ? 'last' : '' }}">
                                        <div class="timeline-marker">
                                            <i class="bi {{ $step['icon'] }} fs-5"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1 {{ $step['completed'] ? 'text-success' : 'text-muted' }}">
                                                        {{ $step['title'] }}
                                                    </h6>
                                                    <p class="mb-0 text-muted small">{{ $step['description'] }}</p>
                                                </div>
                                                @if($step['completed'] && $step['timestamp'])
                                                    <small class="text-muted">
                                                        {{ $step['timestamp']->format('M d, Y') }}<br>
                                                        {{ $step['timestamp']->format('h:i A') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <!-- Estimated Delivery -->
                    @if(in_array($order->status, ['confirmed', 'processing', 'shipped']))
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-calendar-event me-2"></i>
                            <strong>Estimated Delivery:</strong> 
                            @if($order->status === 'shipped')
                                {{ $order->updated_at->addDays(2)->format('M d, Y') }} - {{ $order->updated_at->addDays(5)->format('M d, Y') }}
                            @else
                                {{ now()->addDays(5)->format('M d, Y') }} - {{ now()->addDays(7)->format('M d, Y') }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Details Sidebar -->
        <div class="col-lg-4">
            <!-- Order Items -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="bi bi-bag me-2 text-primary"></i>
                        Order Items ({{ $order->items->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    @foreach($order->items as $item)
                        <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-start">
                                @if($item->productVariation->product->featured_image)
                                    <img src="{{ $item->productVariation->product->featured_image }}" 
                                         alt="{{ $item->productVariation->product->name }}" 
                                         class="rounded me-3"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 small">{{ $item->productVariation->product->name }}</h6>
                                    <div class="small text-muted mb-1">
                                        SKU: {{ $item->productVariation->sku }}
                                    </div>
                                    @if($item->productVariation->attribute_values->count() > 0)
                                        <div class="small text-muted mb-1">
                                            @foreach($item->productVariation->attribute_values as $value)
                                                {{ $value->attribute->name }}: {{ $value->value }}{{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-muted">Qty: {{ $item->quantity }}</span>
                                        <span class="small fw-semibold">₹{{ number_format($item->price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Delivery Address -->
            @if($order->address)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0">
                            <i class="bi bi-geo-alt me-2 text-primary"></i>
                            Delivery Address
                        </h6>
                    </div>
                    <div class="card-body">
                        <address class="mb-0">
                            <strong>{{ $order->address->name }}</strong><br>
                            {{ $order->address->address_line }}<br>
                            {{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->zip }}<br>
                            <i class="bi bi-telephone me-1"></i>{{ $order->address->phone }}
                        </address>
                    </div>
                </div>
            @endif

            <!-- Payment Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="bi bi-credit-card me-2 text-primary"></i>
                        Payment Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Method:</span>
                        <span class="fw-semibold">
                            @if($order->payment_method === 'cod')
                                Cash on Delivery
                            @elseif($order->payment_method === 'razorpay')
                                Online Payment
                            @else
                                {{ ucfirst($order->payment_method) }}
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Total Amount:</span>
                        <span class="fw-bold text-primary">₹{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="{{ route('order.track.public') }}" class="btn btn-outline-primary">
                    <i class="bi bi-search me-2"></i>Track Another Order
                </a>
                @if($order->status !== 'cancelled' && $order->status !== 'delivered')
                    <a href="{{ route('pages.support') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-headset me-2"></i>Contact Support
                    </a>
                @endif
                @if($order->user->email)
                    <a href="mailto:{{ $order->user->email }}" class="btn btn-outline-info">
                        <i class="bi bi-envelope me-2"></i>Email Updates
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.tracking-timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    padding-bottom: 2rem;
}

.timeline-item:not(.last)::before {
    content: '';
    position: absolute;
    left: -2rem;
    top: 2.5rem;
    width: 2px;
    height: calc(100% - 1rem);
    background: #dee2e6;
}

.timeline-item.completed:not(.last)::before {
    background: #198754;
}

.timeline-marker {
    position: absolute;
    left: -2.75rem;
    top: 0;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #dee2e6;
    background: white;
    z-index: 1;
}

.timeline-item.completed .timeline-marker {
    border-color: #198754;
    background: #198754;
    color: white;
}

.timeline-item.pending .timeline-marker {
    border-color: #dee2e6;
    background: white;
    color: #6c757d;
}

.timeline-content {
    min-height: 2.5rem;
    display: flex;
    align-items: center;
}

@media (max-width: 768px) {
    .tracking-timeline {
        padding-left: 1.5rem;
    }
    
    .timeline-marker {
        left: -2.25rem;
        width: 2rem;
        height: 2rem;
    }
    
    .timeline-item:not(.last)::before {
        left: -1.5rem;
        top: 2rem;
    }
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh for active orders every 30 seconds
    const orderStatus = '{{ $order->status }}';
    if (!['delivered', 'cancelled'].includes(orderStatus)) {
        setInterval(function() {
            location.reload();
        }, 30000); // 30 seconds
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>
@endsection