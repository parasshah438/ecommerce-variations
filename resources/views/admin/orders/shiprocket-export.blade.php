@extends('layouts.admin')

@section('title', 'Shiprocket Export')

@section('content')
<div class="container-fluid px-4 sr-export-page">
    <div class="sr-export-hero mb-4">
        <div>
            <p class="sr-kicker mb-1">Operations</p>
            <h2 class="mb-1">Shiprocket Export Console</h2>
            <p class="mb-0 text-muted">Trigger Shiprocket external/orders export with optional filters.</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-dark mt-3 mt-md-0">
            <i class="fas fa-list me-2"></i>Back to Orders
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5 class="mb-0">Export Filters</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.shiprocket.export') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" id="date_from" name="date_from" class="form-control" value="{{ old('date_from') }}">
                        </div>

                        <div class="mb-3">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" id="date_to" name="date_to" class="form-control" value="{{ old('date_to') }}">
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <input type="text" id="status" name="status" class="form-control" value="{{ old('status') }}" placeholder="NEW, SHIPPED, DELIVERED...">
                        </div>

                        <div class="mb-4">
                            <label for="channel_order_id" class="form-label">Channel Order ID</label>
                            <input type="text" id="channel_order_id" name="channel_order_id" class="form-control" value="{{ old('channel_order_id') }}" placeholder="Optional">
                        </div>

                        <button type="submit" class="btn btn-sr-primary w-100">
                            <i class="fas fa-rocket me-2"></i>Start Shiprocket Export
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5 class="mb-0">Latest Response</h5>
                </div>
                <div class="card-body">
                    @php
                        $result = session('shiprocket_export_result');
                        $filters = session('shiprocket_export_filters', []);
                    @endphp

                    @if($result)
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="sr-mini-card">
                                    <span>Status</span>
                                    <strong>{{ $result['status'] ?? 'N/A' }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sr-mini-card">
                                    <span>Background Download</span>
                                    <strong>{{ !empty($result['is_background_downloading']) ? 'Yes' : 'No' }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sr-mini-card">
                                    <span>Filter Count</span>
                                    <strong>{{ count($filters) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6>Applied Filters</h6>
                            @if(!empty($filters))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($filters as $key => $value)
                                        <span class="badge rounded-pill text-bg-secondary">{{ $key }}: {{ $value }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">No filters were used.</p>
                            @endif
                        </div>

                        <h6>Raw Response</h6>
                        <pre class="sr-json">{{ json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    @else
                        <div class="sr-empty-state">
                            <i class="fas fa-cloud-upload-alt mb-2"></i>
                            <h5>No export triggered yet</h5>
                            <p class="text-muted mb-0">Set filters and start an export to view Shiprocket API response here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<style>
.sr-export-page {
    --sr-ink: #192734;
    --sr-orange: #ef6c2f;
    --sr-blue: #1c7ed6;
    font-family: 'Space Grotesk', sans-serif;
    color: var(--sr-ink);
}

.sr-export-hero {
    border-radius: 16px;
    border: 1px solid #e6edf5;
    background: linear-gradient(120deg, #fdfefe 0%, #edf6ff 45%, #fff4ec 100%);
    padding: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
}

.sr-kicker {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.09em;
    color: var(--sr-blue);
    text-transform: uppercase;
}

.btn-sr-primary {
    background: linear-gradient(135deg, var(--sr-orange), #ff8d5f);
    color: #fff;
    border: none;
    font-weight: 700;
}

.btn-sr-primary:hover {
    color: #fff;
    filter: brightness(0.96);
}

.sr-mini-card {
    border-radius: 12px;
    padding: 0.75rem;
    border: 1px solid #e8edf4;
    background: #fcfeff;
}

.sr-mini-card span {
    display: block;
    color: #6b7f93;
    font-size: 0.73rem;
    text-transform: uppercase;
    margin-bottom: 0.2rem;
}

.sr-mini-card strong {
    font-size: 1rem;
}

.sr-empty-state {
    min-height: 320px;
    display: grid;
    place-items: center;
    text-align: center;
    border: 1px dashed #dbe5f1;
    border-radius: 14px;
    background: #fbfdff;
}

.sr-empty-state i {
    font-size: 2rem;
    color: var(--sr-blue);
}

.sr-json {
    background: #0f1722;
    color: #d8e6ff;
    border-radius: 12px;
    padding: 1rem;
    max-height: 360px;
    overflow: auto;
    font-size: 0.78rem;
    line-height: 1.55;
}

@media (max-width: 768px) {
    .sr-empty-state {
        min-height: 220px;
    }
}
</style>
@endpush
