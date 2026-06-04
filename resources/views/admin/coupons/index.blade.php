@extends('admin.layout')

@section('title', 'Coupons Management')
@section('page-title', 'Coupons Management')
@section('page-description', 'Create, edit, and manage coupon codes for the store')
@section('breadcrumb-section', 'Admin')
@section('breadcrumb-page', 'Coupons')

@section('page-actions')
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        New Coupon
    </a>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon primary">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stats-value">{{ $stats['total'] ?? 0 }}</div>
            <div class="stats-label">Total Coupons</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="stats-value">{{ $stats['active'] ?? 0 }}</div>
            <div class="stats-label">Active Coupons</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon warning">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stats-value">{{ $stats['percentage'] ?? 0 }}</div>
            <div class="stats-label">Percentage Coupons</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon danger">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stats-value">{{ $stats['fixed'] ?? 0 }}</div>
            <div class="stats-label">Fixed Amount Coupons</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.coupons.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by code or type">
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label">Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="">All</option>
                    <option value="percentage" {{ request('type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                    <option value="fixed" {{ request('type') === 'fixed' ? 'selected' : '' }}>Fixed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="used_up" {{ request('status') === 'used_up' ? 'selected' : '' }}>Used up</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-grow-1">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Coupons</h5>
        <span class="text-muted small">Showing {{ $coupons->count() }} of {{ $coupons->total() }}</span>
    </div>
    <div class="card-body p-0">
        @if($coupons->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Validity</th>
                            <th>Rules</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coupons as $coupon)
                            @php
                                $today = now()->toDateString();
                                $isScheduled = $coupon->valid_from && $coupon->valid_from > $today;
                                $isExpired = $coupon->valid_until && $coupon->valid_until < $today;
                                $isUsedUp = $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit;
                                $isActive = !$isScheduled && !$isExpired && !$isUsedUp;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $coupon->code }}</div>
                                    <div class="text-muted small">Used {{ $coupon->used_count }} times</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        @if($coupon->type === 'percentage')
                                            {{ $coupon->discount }}%
                                        @else
                                            ₹{{ number_format($coupon->discount, 2) }}
                                        @endif
                                    </div>
                                    <div class="text-muted small text-capitalize">{{ $coupon->type }}</div>
                                </td>
                                <td>
                                    <div class="small">
                                        <div><strong>From:</strong> {{ $coupon->valid_from ? \Illuminate\Support\Carbon::parse($coupon->valid_from)->format('d M Y') : 'No start date' }}</div>
                                        <div><strong>To:</strong> {{ $coupon->valid_until ? \Illuminate\Support\Carbon::parse($coupon->valid_until)->format('d M Y') : 'No end date' }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <div><strong>Min cart:</strong> ₹{{ number_format($coupon->minimum_cart_value ?? 0, 2) }}</div>
                                        <div><strong>Max discount:</strong> {{ $coupon->maximum_discount_limit ? '₹' . number_format($coupon->maximum_discount_limit, 2) : 'No limit' }}</div>
                                        <div><strong>Usage:</strong> {{ $coupon->usage_limit ? $coupon->used_count . ' / ' . $coupon->usage_limit : 'Unlimited' }}</div>
                                    </div>
                                </td>
                                <td>
                                    @if($isActive)
                                        <span class="badge bg-success">Active</span>
                                    @elseif($isScheduled)
                                        <span class="badge bg-info">Scheduled</span>
                                    @elseif($isExpired)
                                        <span class="badge bg-danger">Expired</span>
                                    @elseif($isUsedUp)
                                        <span class="badge bg-warning text-dark">Used Up</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.coupons.show', $coupon) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Delete this coupon?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                <h5 class="mb-2">No coupons found</h5>
                <p class="text-muted mb-4">Create your first coupon to start offering discounts.</p>
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Coupon
                </a>
            </div>
        @endif
    </div>

    @if($coupons->hasPages())
        <div class="card-footer">
            {{ $coupons->links() }}
        </div>
    @endif
</div>
@endsection
