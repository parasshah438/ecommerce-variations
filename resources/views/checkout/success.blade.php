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
    
    .tax-highlight {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(13, 110, 253, 0.05));
        border-left: 4px solid #0d6efd;
    }
    
    .payment-summary-item {
        padding: 0.5rem 0;
        border-bottom: 1px dashed #e9ecef;
    }
    
    .payment-summary-item:last-child {
        border-bottom: none;
        padding-top: 0.75rem;
        margin-top: 0.5rem;
        border-top: 2px solid #0d6efd;
    }
    
    .tax-info-card {
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
        border: 1px solid rgba(13, 110, 253, 0.1);
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
                                        <th colspan="4" class="text-end">Subtotal:</th>
                                        <th>₹{{ number_format($order->subtotal ?? 0, 2) }}</th>
                                    </tr>
                                    @if($order->coupon_discount > 0)
                                    <tr class="text-success">
                                        <th colspan="4" class="text-end">Coupon Discount ({{ $order->coupon_code }}):</th>
                                        <th>-₹{{ number_format($order->coupon_discount, 2) }}</th>
                                    </tr>
                                    @endif
                                    @if($order->shipping_cost > 0)
                                    <tr>
                                        <th colspan="4" class="text-end">Shipping:</th>
                                        <th>₹{{ number_format($order->shipping_cost, 2) }}</th>
                                    </tr>
                                    @else
                                    <tr class="text-success">
                                        <th colspan="4" class="text-end">Shipping:</th>
                                        <th>Free</th>
                                    </tr>
                                    @endif
                                    @if($order->tax_amount > 0)
                                    <tr class="text-info">
                                        <th colspan="4" class="text-end">{{ $order->tax_name ?? 'Tax' }} ({{ number_format(($order->tax_rate ?? 0) * 100, 0) }}%):</th>
                                        <th>₹{{ number_format($order->tax_amount, 2) }}</th>
                                    </tr>
                                    @endif
                                    <tr class="table-primary">
                                        <th colspan="4" class="text-end fs-5">Total Amount:</th>
                                        <th class="text-primary fs-5">₹{{ number_format($order->total, 2) }}</th>
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
                <!-- Tax & Payment Summary -->
                @if($order->tax_amount > 0 || $order->shipping_cost > 0 || $order->coupon_discount > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-calculator text-primary me-2"></i>
                            Payment Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="payment-summary-item">
                            <div class="d-flex justify-content-between">
                                <span>Items Subtotal:</span>
                                <strong>₹{{ number_format($order->subtotal ?? 0, 2) }}</strong>
                            </div>
                        </div>
                        
                        @if($order->coupon_discount > 0)
                        <div class="payment-summary-item">
                            <div class="d-flex justify-content-between text-success">
                                <span>
                                    <i class="bi bi-tag-fill me-1"></i>
                                    Discount ({{ $order->coupon_code }}):
                                </span>
                                <strong>-₹{{ number_format($order->coupon_discount, 2) }}</strong>
                            </div>
                        </div>
                        @endif
                        
                        @if($order->shipping_cost > 0)
                        <div class="payment-summary-item">
                            <div class="d-flex justify-content-between">
                                <span>
                                    <i class="bi bi-truck me-1"></i>
                                    Shipping:
                                </span>
                                <strong>₹{{ number_format($order->shipping_cost, 2) }}</strong>
                            </div>
                        </div>
                        @else
                        <div class="payment-summary-item">
                            <div class="d-flex justify-content-between text-success">
                                <span>
                                    <i class="bi bi-truck me-1"></i>
                                    Shipping:
                                </span>
                                <strong>Free</strong>
                            </div>
                        </div>
                        @endif
                        
                        @if($order->tax_amount > 0)
                        <div class="payment-summary-item tax-highlight p-2 rounded">
                            <div class="d-flex justify-content-between text-info">
                                <span>
                                    <i class="bi bi-receipt me-1"></i>
                                    {{ $order->tax_name ?? 'Tax' }} ({{ number_format(($order->tax_rate ?? 0) * 100, 0) }}%):
                                </span>
                                <strong>₹{{ number_format($order->tax_amount, 2) }}</strong>
                            </div>
                            
                            <!-- Tax Calculation Method Info -->
                            <div class="small text-muted mt-1">
                                <i class="bi bi-info-circle me-1"></i>
                                Tax calculated on {{ $order->coupon_discount > 0 ? 'discounted amount' : 'subtotal' }}
                            </div>
                        </div>
                        @endif
                        
                        <div class="payment-summary-item">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold mb-0">Total Paid:</h6>
                                <h6 class="fw-bold text-primary mb-0">₹{{ number_format($order->total, 2) }}</h6>
                            </div>
                        </div>
                        
                        <!-- Payment Method Info -->
                        <div class="mt-3 p-2 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-{{ $order->payment_method === 'cod' ? 'cash-coin' : 'credit-card' }} text-success me-2"></i>
                                <div>
                                    <small class="fw-semibold">Payment Method</small>
                                    <div class="small text-muted">
                                        {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online Payment' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Tax Information Card (for tax compliance) -->
                @if($order->tax_amount > 0)
                <div class="card shadow-sm mb-4 tax-info-card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text text-primary me-2"></i>
                            Tax Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="h6 text-primary mb-1">{{ number_format(($order->tax_rate ?? 0) * 100, 0) }}%</div>
                                    <small class="text-muted">{{ $order->tax_name ?? 'Tax' }} Rate</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="h6 text-success mb-1">₹{{ number_format($order->tax_amount, 2) }}</div>
                                    <small class="text-muted">Tax Amount</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6 class="small fw-semibold mb-2">Tax Calculation Details:</h6>
                            <div class="small text-muted">
                                <div class="d-flex justify-content-between">
                                    <span>Taxable Amount:</span>
                                    <span>₹{{ number_format(($order->subtotal ?? 0) - ($order->coupon_discount ?? 0), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Tax Rate Applied:</span>
                                    <span>{{ number_format(($order->tax_rate ?? 0) * 100, 2) }}%</span>
                                </div>
                                <div class="d-flex justify-content-between fw-semibold">
                                    <span>Tax Amount:</span>
                                    <span>₹{{ number_format($order->tax_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tax Invoice Note -->
                        <div class="mt-3 p-2 border rounded bg-info bg-opacity-10">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle text-info me-2 mt-1"></i>
                                <div class="small">
                                    <strong>Tax Invoice:</strong> A detailed tax invoice will be generated and sent to your email for GST compliance purposes.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
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