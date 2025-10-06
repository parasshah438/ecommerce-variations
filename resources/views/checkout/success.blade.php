@extends('layouts.frontend')

@section('title', 'Order Placed Successfully - ' . config('app.name'))

@section('styles')
<style>
    .success-container {
        min-height: 80vh;
        padding: 3rem 0;
    }
    
    .success-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #28a745, #157347);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        box-shadow: 0 10px 30px rgba(25, 135, 84, 0.3);
    }
    
    .confetti {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 9999;
    }
    
    .confetti-piece {
        position: absolute;
        width: 10px;
        height: 10px;
        background: #007bff;
        animation: confetti-fall 3s linear infinite;
    }
    
    @keyframes confetti-fall {
        0% {
            transform: translateY(-100vh) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }
    
    .order-timeline {
        position: relative;
        padding: 2rem 0;
    }
    
    .timeline-item {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .timeline-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #28a745;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }
    
    .timeline-icon.pending {
        background: #6c757d;
    }
    
    .action-buttons .btn {
        min-width: 200px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border-radius: 8px;
        margin: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="success-container">
    <!-- Confetti Animation -->
    <div class="confetti" id="confetti"></div>
    
    <div class="container">
        <!-- Success Header -->
        <div class="text-center mb-5">
            <div class="success-icon">
                <i class="bi bi-check-lg text-white" style="font-size: 3rem;"></i>
            </div>
            <h1 class="display-4 fw-bold text-success mb-3">Order Placed Successfully!</h1>
            <p class="lead text-muted">Thank you for your purchase. Your order has been confirmed.</p>
            <div class="badge bg-success fs-6 px-3 py-2">Order #{{ $order->id }}</div>
        </div>
        
        <div class="row">
            <!-- Left Column - Order Details -->
            <div class="col-lg-8">
                <!-- Order Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-receipt text-primary me-2"></i>
                            Order Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Order Number:</span>
                                    <strong>#{{ $order->id }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Order Date:</span>
                                    <strong>{{ $order->created_at->format('M d, Y') }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Payment Method:</span>
                                    <strong>{{ strtoupper($order->payment_method) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Amount:</span>
                                    <strong class="text-primary">₹{{ number_format($order->total, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-bag text-primary me-2"></i>
                            Order Items
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->variation->product->name }}</strong>
                                                @if($item->variation->attribute_values && $item->variation->attribute_values->count() > 0)
                                                    <span class="text-muted d-block mt-1">
                                                        @foreach($item->variation->attribute_values as $value)
                                                            {{ $value->attribute->name }}: {{ $value->value }}{{ !$loop->last ? ', ' : '' }}
                                                        @endforeach
                                                    </span>
                                                @endif
                                            </td>
                                            <td><code>{{ $item->variation->sku }}</code></td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>₹{{ number_format($item->price, 2) }}</td>
                                            <td><strong>₹{{ number_format($item->price * $item->quantity, 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="4" class="text-end">Total:</th>
                                        <th class="text-primary">₹{{ number_format($order->total, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Delivery Address -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            Delivery Address
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-semibold">{{ $order->address->name }}</h6>
                        <p class="text-muted mb-1">{{ $order->address->phone }}</p>
                        <p class="text-muted mb-0">
                            {{ $order->address->address_line }}<br>
                            {{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->zip }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Next Steps -->
            <div class="col-lg-4">
                <!-- Order Timeline -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history text-primary me-2"></i>
                            Order Timeline
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="order-timeline">
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="bi bi-check"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="fw-semibold mb-1">Order Placed</h6>
                                    <small class="text-muted">{{ $order->created_at->format('M d, Y - h:i A') }}</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-icon pending">
                                    <i class="bi bi-package"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="fw-semibold mb-1">Processing</h6>
                                    <small class="text-muted">Estimated: 1-2 business days</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-icon pending">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="fw-semibold mb-1">Shipped</h6>
                                    <small class="text-muted">Estimated: 3-5 business days</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-icon pending">
                                    <i class="bi bi-house-door"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="fw-semibold mb-1">Delivered</h6>
                                    <small class="text-muted">Estimated: 5-7 business days</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="text-center action-buttons">
                    <a href="#" class="btn btn-primary">
                        <i class="bi bi-truck me-2"></i>Track Order
                    </a>
                    <a href="{{ route('welcome') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Confetti Animation
function createConfetti() {
    const confettiContainer = document.getElementById('confetti');
    const colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1'];
    
    for (let i = 0; i < 50; i++) {
        const confettiPiece = document.createElement('div');
        confettiPiece.className = 'confetti-piece';
        confettiPiece.style.left = Math.random() * 100 + '%';
        confettiPiece.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confettiPiece.style.animationDelay = Math.random() * 3 + 's';
        confettiContainer.appendChild(confettiPiece);
    }
    
    // Remove confetti after animation
    setTimeout(() => {
        confettiContainer.innerHTML = '';
    }, 6000);
}

// Trigger confetti on page load
document.addEventListener('DOMContentLoaded', function() {
    createConfetti();
});
</script>
@endsection