@extends('admin.layout')

@section('title', 'Coupon Details')
@section('page-title', 'Coupon Details')
@section('page-description', 'View coupon rules, validity, and usage information')
@section('breadcrumb-section', 'Admin')
@section('breadcrumb-page', 'Coupon Details')

@section('page-actions')
    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-primary">
        <i class="fas fa-pen"></i>
        Edit Coupon
    </a>
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
        Back to Coupons
    </a>
@endsection

@section('content')
@php
    $today = now()->toDateString();
    $isScheduled = $coupon->valid_from && $coupon->valid_from > $today;
    $isExpired = $coupon->valid_until && $coupon->valid_until < $today;
    $isUsedUp = $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit;
    $isActive = !$isScheduled && !$isExpired && !$isUsedUp;
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Coupon Information</h5>
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
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Coupon Code</div>
                        <div class="fs-4 fw-bold text-uppercase">{{ $coupon->code }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Discount</div>
                        <div class="fs-4 fw-bold">
                            @if($coupon->type === 'percentage')
                                {{ rtrim(rtrim(number_format($coupon->discount, 2), '0'), '.') }}%
                            @else
                                ₹{{ number_format($coupon->discount, 2) }}
                            @endif
                        </div>
                        <div class="text-muted small text-capitalize">{{ $coupon->type }} discount</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Validity</div>
                        <div class="fw-semibold">
                            <div><strong>From:</strong> {{ $coupon->valid_from ? \Illuminate\Support\Carbon::parse($coupon->valid_from)->format('d M Y') : 'No start date' }}</div>
                            <div><strong>To:</strong> {{ $coupon->valid_until ? \Illuminate\Support\Carbon::parse($coupon->valid_until)->format('d M Y') : 'No end date' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Usage</div>
                        <div class="fw-semibold">
                            {{ $coupon->used_count }}
                            @if($coupon->usage_limit)
                                / {{ $coupon->usage_limit }}
                            @else
                                / Unlimited
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Minimum Cart Value</div>
                        <div class="fw-semibold">₹{{ number_format($coupon->minimum_cart_value ?? 0, 2) }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Maximum Discount Limit</div>
                        <div class="fw-semibold">
                            {{ $coupon->maximum_discount_limit !== null ? '₹' . number_format($coupon->maximum_discount_limit, 2) : 'No limit' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Created At</div>
                        <div class="fw-semibold">{{ $coupon->created_at?->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Business Rules Summary</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Minimum Cart Check</div>
                            <div class="fw-semibold">
                                {{ $coupon->minimum_cart_value > 0 ? 'Required' : 'Not required' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Maximum Discount Cap</div>
                            <div class="fw-semibold">
                                {{ $coupon->maximum_discount_limit !== null ? 'Enabled' : 'Disabled' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Global Usage Limit</div>
                            <div class="fw-semibold">
                                {{ $coupon->usage_limit ? $coupon->usage_limit . ' total' : 'Unlimited' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Per-Customer Limit</div>
                            <div class="fw-semibold">
                                {{ $coupon->per_user_limit ? $coupon->per_user_limit . ' per user' : 'Unlimited' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Applied Carts</h5>
                <span class="badge bg-primary">{{ $coupon->carts->count() }}</span>
            </div>
            <div class="card-body">
                @if($coupon->carts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Cart ID</th>
                                    <th>User</th>
                                    <th>Coupon Discount</th>
                                    <th>Cart UUID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($coupon->carts as $cart)
                                    <tr>
                                        <td>#{{ $cart->id }}</td>
                                        <td>{{ $cart->user->name ?? $cart->user->email ?? 'Guest' }}</td>
                                        <td>₹{{ number_format($cart->discount_amount ?? 0, 2) }}</td>
                                        <td><code>{{ $cart->uuid }}</code></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5 class="mb-2">No carts applied yet</h5>
                        <p class="text-muted mb-0">This coupon has not been used in any cart.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Status</span>
                    <strong>
                        @if($isActive)
                            <span class="text-success">Active</span>
                        @elseif($isScheduled)
                            <span class="text-info">Scheduled</span>
                        @elseif($isExpired)
                            <span class="text-danger">Expired</span>
                        @elseif($isUsedUp)
                            <span class="text-warning">Used Up</span>
                        @else
                            <span class="text-secondary">Inactive</span>
                        @endif
                    </strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Carts Used</span>
                    <strong>{{ $coupon->carts->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Used Count</span>
                    <strong>{{ $coupon->used_count }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Usage Remaining</span>
                    <strong>
                        {{ $coupon->usage_limit ? max($coupon->usage_limit - $coupon->used_count, 0) : 'Unlimited' }}
                    </strong>
                </div>
                <div class="d-flex justify-content-between mb-0">
                    <span class="text-muted">Cart Minimum</span>
                    <strong>₹{{ number_format($coupon->minimum_cart_value ?? 0, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-primary">
                        <i class="fas fa-pen"></i>
                        Edit Coupon
                    </a>
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="card-title mb-0">Danger Zone</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    Delete this coupon only if you are sure it is no longer needed.
                </p>
                <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Delete this coupon?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="fas fa-trash"></i>
                        Delete Coupon
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
