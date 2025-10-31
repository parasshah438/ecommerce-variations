@extends('layouts.app')

@section('title', 'Active Sales & Offers')

@section('content')
<div class="container-fluid px-0">
    <!-- Hero Banner -->
    <div class="sale-hero bg-gradient-primary text-white py-5 mb-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3">🎉 Amazing Sales & Offers</h1>
                    <p class="lead mb-4">Don't miss out on our incredible deals! Limited time offers on your favorite products.</p>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="{{ asset('images/sale-banner.png') }}" alt="Sale Banner" class="img-fluid" style="max-height: 300px;">
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        @if($activeSales->count() > 0)
            <div class="row">
                @foreach($activeSales as $sale)
                <div class="col-lg-6 col-md-6 mb-4">
                    <div class="sale-card card shadow-lg border-0 h-100 position-relative overflow-hidden">
                        @if($sale->banner_image)
                            <div class="sale-image position-relative">
                                <img src="{{ Storage::url($sale->banner_image) }}" 
                                     class="card-img-top" alt="{{ $sale->name }}" 
                                     style="height: 250px; object-fit: cover;">
                                <div class="sale-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                    <div class="text-center text-white">
                                        <div class="sale-badge bg-danger text-white rounded-pill px-4 py-2 fw-bold fs-5 mb-3">
                                            UP TO {{ $sale->discount_value }}{{ $sale->type === 'percentage' ? '%' : '₹' }} OFF
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="card-body p-4">
                            <h3 class="card-title fw-bold text-primary mb-2">{{ $sale->name }}</h3>
                            <p class="card-text text-muted mb-3">{{ $sale->description }}</p>
                            
                            <!-- Sale Timer -->
                            <div class="sale-timer mb-3 p-3 bg-light rounded" data-end-date="{{ $sale->end_date->toISOString() }}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fw-bold text-danger">
                                        <i class="bi bi-clock-fill me-2"></i>Ends In:
                                    </span>
                                    <span class="timer-display fw-bold text-danger fs-5">Loading...</span>
                                </div>
                            </div>

                            <!-- Sale Details -->
                            <div class="sale-details mb-3">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="border-end">
                                            <div class="fw-bold text-primary">{{ $sale->products_count ?? 0 }}</div>
                                            <small class="text-muted">Products</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border-end">
                                            <div class="fw-bold text-success">{{ $sale->getSaleTypeLabel() }}</div>
                                            <small class="text-muted">Type</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        @if($sale->min_order_value)
                                            <div class="fw-bold text-warning">₹{{ $sale->min_order_value }}</div>
                                            <small class="text-muted">Min Order</small>
                                        @else
                                            <div class="fw-bold text-info">No Limit</div>
                                            <small class="text-muted">Min Order</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <a href="{{ route('sales.show', $sale->slug) }}" 
                                   class="btn btn-primary btn-lg fw-bold px-4">
                                    <i class="bi bi-bag-check-fill me-2"></i>SHOP NOW
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($activeSales->hasPages())
                <div class="d-flex justify-content-center mt-5">
                    {{ $activeSales->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <img src="{{ asset('images/no-sales.png') }}" alt="No Sales" class="img-fluid mb-4" style="max-width: 300px;">
                <h3 class="text-muted">No Active Sales</h3>
                <p class="text-muted">Check back later for amazing deals and offers!</p>
                <a href="{{ route('home') }}" class="btn btn-primary">Continue Shopping</a>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.sale-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.sale-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.sale-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
}

.sale-overlay {
    background: linear-gradient(45deg, rgba(0,0,0,0.6), rgba(0,0,0,0.3));
}

.timer-display {
    font-family: 'Courier New', monospace;
}

.sale-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>
@endpush

@push('scripts')
<script>
function initializeSaleTimers() {
    document.querySelectorAll('.sale-timer').forEach(timer => {
        const endDate = new Date(timer.dataset.endDate);
        const display = timer.querySelector('.timer-display');
        
        function updateTimer() {
            const now = new Date();
            const difference = endDate - now;
            
            if (difference > 0) {
                const days = Math.floor(difference / (1000 * 60 * 60 * 24));
                const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((difference % (1000 * 60)) / 1000);
                
                if (days > 0) {
                    display.innerHTML = `${days}d ${hours}h ${minutes}m`;
                } else {
                    display.innerHTML = `${hours}h ${minutes}m ${seconds}s`;
                }
            } else {
                display.innerHTML = 'Sale Ended';
                timer.classList.add('text-muted');
            }
        }
        
        updateTimer();
        setInterval(updateTimer, 1000);
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initializeSaleTimers);
</script>
@endpush
@endsection