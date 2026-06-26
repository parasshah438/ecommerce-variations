@extends('layouts.admin')

@section('title', 'Shiprocket Order Details')

@section('content')
<div class="container-fluid px-4 shiprocket-page">
    <div class="sr-hero mb-4">
        <div>
            <p class="sr-kicker mb-1">Shipping Intelligence</p>
            <h2 class="mb-1">Shiprocket Order Details</h2>
            <p class="mb-0 text-muted">Order #{{ $order->id }} mapped to Shiprocket Order #{{ $shiprocketOrderId }}</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-dark">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-dark">
                <i class="fas fa-receipt me-2"></i>Open Order
            </a>
        </div>
    </div>

    @php
        $data = $shiprocketResponse['data'] ?? [];
        $shipments = $data['shipments'] ?? [];
        if (isset($shipments['id'])) {
            $shipments = [$shipments];
        }
        $products = $data['products'] ?? [];
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="sr-stat-card">
                <span>Status</span>
                <h4 class="mb-0">{{ $data['status'] ?? 'Unknown' }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sr-stat-card">
                <span>Payment</span>
                <h4 class="mb-0">{{ ucfirst($data['payment_method'] ?? 'na') }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sr-stat-card">
                <span>Total</span>
                <h4 class="mb-0">{{ $data['currency'] ?? 'INR' }} {{ number_format((float)($data['total'] ?? 0), 2) }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sr-stat-card">
                <span>Shipment Count</span>
                <h4 class="mb-0">{{ count($shipments) }}</h4>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5 class="mb-0">Customer & Delivery</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="sr-label">Customer</div>
                            <div class="sr-value">{{ $data['customer_name'] ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="sr-label">Phone</div>
                            <div class="sr-value">{{ $data['customer_phone'] ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="sr-label">Email</div>
                            <div class="sr-value">{{ $data['customer_email'] ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="sr-label">Order Date</div>
                            <div class="sr-value">{{ $data['order_date'] ?? ($data['created_at'] ?? '-') }}</div>
                        </div>
                        <div class="col-12">
                            <div class="sr-label">Address</div>
                            <div class="sr-value">{{ ($data['customer_address'] ?? '') }} {{ ($data['customer_city'] ?? '') }} {{ ($data['customer_state'] ?? '') }} {{ ($data['customer_pincode'] ?? '') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5 class="mb-0">Shipments</h5>
                </div>
                <div class="card-body">
                    @forelse($shipments as $shipment)
                        <div class="sr-shipment-block">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Shipment #{{ $shipment['id'] ?? '-' }}</strong>
                                <span class="badge bg-secondary">{{ $shipment['status'] ?? 'PENDING' }}</span>
                            </div>
                            <div class="small text-muted">AWB: {{ $shipment['awb'] ?? '-' }}</div>
                            <div class="small text-muted">Courier: {{ $shipment['courier'] ?? ($shipment['name'] ?? '-') }}</div>
                            <div class="small text-muted">Weight: {{ $shipment['weight'] ?? '-' }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No shipment records available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-transparent border-0 pt-4 pb-0">
            <h5 class="mb-0">Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $item)
                            <tr>
                                <td>{{ $item['name'] ?? '-' }}</td>
                                <td>{{ $item['sku'] ?? ($item['channel_sku'] ?? '-') }}</td>
                                <td>{{ $item['quantity'] ?? '-' }}</td>
                                <td>{{ number_format((float)($item['price'] ?? $item['selling_price'] ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Raw API Response</h5>
            <button class="btn btn-sm btn-outline-dark" onclick="copyShiprocketJson()">
                <i class="fas fa-copy me-1"></i>Copy JSON
            </button>
        </div>
        <div class="card-body">
            <pre id="shiprocketJson" class="sr-json">{{ json_encode($shiprocketResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
.shiprocket-page {
    --sr-bg-a: #f6fbff;
    --sr-bg-b: #fff8ef;
    --sr-ink: #10253f;
    --sr-accent: #ff6f3c;
    --sr-accent-2: #0b6efd;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--sr-ink);
    background: radial-gradient(circle at 10% 20%, var(--sr-bg-a), transparent 35%), radial-gradient(circle at 90% 0, var(--sr-bg-b), transparent 25%);
}

.sr-hero {
    background: linear-gradient(130deg, #ffffff 10%, #f5fbff 55%, #fff2e8 100%);
    border: 1px solid #e6eef8;
    border-radius: 16px;
    padding: 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.sr-kicker {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--sr-accent-2);
}

.sr-stat-card {
    background: #fff;
    border: 1px solid #eaf0f6;
    border-radius: 14px;
    padding: 1rem;
    height: 100%;
    box-shadow: 0 8px 20px rgba(14, 31, 53, 0.06);
}

.sr-stat-card span {
    font-size: 0.74rem;
    color: #667f99;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.sr-label {
    font-size: 0.74rem;
    color: #6b7b8f;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.2rem;
}

.sr-value {
    font-weight: 600;
    color: #1f2f44;
}

.sr-shipment-block {
    border: 1px solid #e8eff7;
    border-radius: 12px;
    padding: 0.85rem;
    margin-bottom: 0.75rem;
    background: #fafcff;
}

.sr-json {
    background: #0f1722;
    color: #d8e6ff;
    border-radius: 12px;
    padding: 1rem;
    max-height: 420px;
    overflow: auto;
    font-size: 0.78rem;
    line-height: 1.55;
}

@media (max-width: 768px) {
    .sr-hero {
        padding: 1rem;
    }

    .sr-json {
        max-height: 280px;
    }
}
</style>
@endpush

@push('scripts')
<script>
function copyShiprocketJson() {
    const block = document.getElementById('shiprocketJson');
    navigator.clipboard.writeText(block.innerText)
        .then(() => {
            if (typeof showToast === 'function') {
                showToast('Shiprocket JSON copied.', 'success');
            } else {
                alert('Shiprocket JSON copied.');
            }
        })
        .catch(() => alert('Could not copy JSON.'));
}
</script>
@endpush
