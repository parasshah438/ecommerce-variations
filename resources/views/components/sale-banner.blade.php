@php
    $activeSales = \App\Models\Sale::where('is_active', true)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
@endphp

@if($activeSales->count() > 0)
<section class="sale-banners py-4 bg-light">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h2 class="h3 fw-bold text-dark mb-0">🔥 Hot Deals & Sales</h2>
                <p class="text-muted mb-0">Limited time offers - Don't miss out!</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('sales.index') }}" class="btn btn-outline-primary">
                    View All Sales <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        
        <div class="row">
            @foreach($activeSales->take(3) as $sale)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="sale-banner-card position-relative overflow-hidden rounded-3 shadow-lg bg-white">
                    @if($sale->banner_image)
                        <div class="sale-image-container position-relative">
                            <img src="{{ Storage::disk('public')->url($sale->banner_image) }}" 
                                 class="w-100" style="height: 180px; object-fit: cover;" alt="{{ $sale->name }}"
                                 loading="lazy"
                                 onerror="this.src='{{ asset('images/sale-placeholder.jpg') }}';">>
                            <div class="sale-overlay-mini position-absolute top-0 start-0 w-100 h-100 d-flex align-items-end">
                                <div class="p-3 w-100">
                                    <div class="sale-badge-mini bg-danger text-white rounded-pill px-3 py-1 fw-bold small">
                                        {{ $sale->discount_value }}{{ $sale->type === 'percentage' ? '%' : '₹' }} OFF
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="p-3">
                        <h5 class="fw-bold text-primary mb-2">{{ $sale->name }}</h5>
                        <p class="text-muted small mb-3">{{ Str::limit($sale->description, 80) }}</p>
                        
                        <!-- Mini Timer -->
                        <div class="sale-timer-mini d-flex align-items-center justify-content-between mb-3" 
                             data-end-date="{{ $sale->end_date->toISOString() }}">
                            <small class="text-danger fw-bold">
                                <i class="bi bi-clock-fill me-1"></i>Ends in:
                            </small>
                            <small class="timer-display fw-bold text-danger">Loading...</small>
                        </div>
                        
                        <div class="d-grid">
                            <a href="{{ route('sales.show', $sale->slug) }}" 
                               class="btn btn-primary btn-sm fw-bold">
                                SHOP NOW
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<style>
.sale-banner-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
}

.sale-banner-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
}

.sale-overlay-mini {
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
}

.sale-badge-mini {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>

<script>
// Initialize mini timers
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.sale-timer-mini').forEach(timer => {
        const endDate = new Date(timer.dataset.endDate);
        const display = timer.querySelector('.timer-display');
        
        function updateTimer() {
            const now = new Date();
            const difference = endDate - now;
            
            if (difference > 0) {
                const days = Math.floor(difference / (1000 * 60 * 60 * 24));
                const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                
                if (days > 0) {
                    display.innerHTML = `${days}d ${hours}h`;
                } else {
                    display.innerHTML = `${hours}h ${minutes}m`;
                }
            } else {
                display.innerHTML = 'Ended';
                timer.classList.add('text-muted');
            }
        }
        
        updateTimer();
        setInterval(updateTimer, 60000); // Update every minute
    });
});
</script>
@endif