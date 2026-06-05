@extends('admin.layout')

@section('title', 'Edit Coupon')
@section('page-title', 'Edit Coupon')
@section('page-description', 'Update coupon details, validity, and usage rules')
@section('breadcrumb-section', 'Admin')
@section('breadcrumb-page', 'Edit Coupon')

@section('page-actions')
    <a href="{{ route('admin.coupons.show', $coupon) }}" class="btn btn-outline-info">
        <i class="fas fa-eye"></i>
        View Coupon
    </a>
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
        Back to Coupons
    </a>
@endsection

@section('content')
<form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Coupon Information</h5>
                    <span class="badge bg-secondary">ID #{{ $coupon->id }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="code" class="form-label">Coupon Code <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('code') is-invalid @enderror"
                                   id="code"
                                   name="code"
                                   value="{{ old('code', $coupon->code) }}"
                                   placeholder="SAVE10"
                                   required>
                            <div class="form-text">Use letters, numbers, dashes, and underscores only.</div>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror"
                                    id="type"
                                    name="type"
                                    required>
                                <option value="">Select type</option>
                                <option value="percentage" {{ old('type', $coupon->type) === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="discount" class="form-label">Discount Value <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   max="1000000"
                                   class="form-control @error('discount') is-invalid @enderror"
                                   id="discount"
                                   name="discount"
                                   value="{{ old('discount', $coupon->discount) }}"
                                   placeholder="10"
                                   required>
                            <div class="form-text" id="discountHelp">Enter a percentage or fixed amount based on the selected type.</div>
                            @error('discount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Used Count</label>
                            <input type="text" class="form-control bg-light" value="{{ $coupon->used_count }}" readonly disabled>
                            <div class="form-text">System-managed from completed orders.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="valid_from" class="form-label">Valid From</label>
                            <input type="date"
                                   class="form-control @error('valid_from') is-invalid @enderror"
                                   id="valid_from"
                                   name="valid_from"
                                   value="{{ old('valid_from', $coupon->valid_from ? \Illuminate\Support\Carbon::parse($coupon->valid_from)->format('Y-m-d') : '') }}">
                            @error('valid_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="valid_until" class="form-label">Valid Until</label>
                            <input type="date"
                                   class="form-control @error('valid_until') is-invalid @enderror"
                                   id="valid_until"
                                   name="valid_until"
                                   value="{{ old('valid_until', $coupon->valid_until ? \Illuminate\Support\Carbon::parse($coupon->valid_until)->format('Y-m-d') : '') }}">
                            @error('valid_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Business Rules</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="minimum_cart_value" class="form-label">Minimum Cart Value</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control @error('minimum_cart_value') is-invalid @enderror"
                                   id="minimum_cart_value"
                                   name="minimum_cart_value"
                                   value="{{ old('minimum_cart_value', $coupon->minimum_cart_value) }}"
                                   placeholder="0">
                            @error('minimum_cart_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="maximum_discount_limit" class="form-label">Maximum Discount Limit</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control @error('maximum_discount_limit') is-invalid @enderror"
                                   id="maximum_discount_limit"
                                   name="maximum_discount_limit"
                                   value="{{ old('maximum_discount_limit', $coupon->maximum_discount_limit) }}"
                                   placeholder="Optional">
                            <div class="form-text">Useful for percentage coupons.</div>
                            @error('maximum_discount_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="usage_limit" class="form-label">Global Usage Limit</label>
                            <input type="number"
                                   min="1"
                                   class="form-control @error('usage_limit') is-invalid @enderror"
                                   id="usage_limit"
                                   name="usage_limit"
                                   value="{{ old('usage_limit', $coupon->usage_limit) }}"
                                   placeholder="Unlimited">
                            <div class="form-text">Total uses across all customers.</div>
                            @error('usage_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="per_user_limit" class="form-label">Per-Customer Limit</label>
                            <input type="number"
                                   min="1"
                                   class="form-control @error('per_user_limit') is-invalid @enderror"
                                   id="per_user_limit"
                                   name="per_user_limit"
                                   value="{{ old('per_user_limit', $coupon->per_user_limit) }}"
                                   placeholder="Unlimited">
                            <div class="form-text">Set to 1 for once per customer.</div>
                            @error('per_user_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Coupon Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">Current Status</div>
                        @php
                            $today = now()->toDateString();
                            $isScheduled = $coupon->valid_from && $coupon->valid_from > $today;
                            $isExpired = $coupon->valid_until && $coupon->valid_until < $today;
                            $isUsedUp = $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit;
                            $isActive = !$isScheduled && !$isExpired && !$isUsedUp;
                        @endphp
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

                    <div class="mb-3">
                        <div class="text-muted small">Applied Carts</div>
                        <div class="fw-semibold">{{ $coupon->carts->count() }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Coupon Usage</div>
                        <div class="fw-semibold">
                            {{ $coupon->usage_limit ? $coupon->used_count . ' / ' . $coupon->usage_limit : $coupon->used_count . ' / Unlimited' }}
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="text-muted small">Created At</div>
                        <div class="fw-semibold">{{ $coupon->created_at?->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Coupon
                        </button>
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Deleting a coupon will remove it permanently if it is not applied to any cart.
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
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('type');
    const discount = document.getElementById('discount');
    const help = document.getElementById('discountHelp');

    function updateHelp() {
        if (!type || !help || !discount) return;

        if (type.value === 'percentage') {
            help.textContent = 'Percentage discount. The controller will cap this at 100.';
            discount.max = '100';
        } else if (type.value === 'fixed') {
            help.textContent = 'Fixed discount amount in currency.';
            discount.removeAttribute('max');
        } else {
            help.textContent = 'Enter a percentage or fixed amount based on the selected type.';
            discount.removeAttribute('max');
        }
    }

    type?.addEventListener('change', updateHelp);
    updateHelp();
});
</script>
@endsection
