@extends('admin.layout')

@section('title', 'Create Coupon')
@section('page-title', 'Create Coupon')
@section('page-description', 'Add a new coupon code with discount rules and usage limits')
@section('breadcrumb-section', 'Admin')
@section('breadcrumb-page', 'Create Coupon')

@section('page-actions')
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
        Back to Coupons
    </a>
@endsection

@section('content')
<form action="{{ route('admin.coupons.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Coupon Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="code" class="form-label">Coupon Code <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('code') is-invalid @enderror"
                                   id="code"
                                   name="code"
                                   value="{{ old('code') }}"
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
                                <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
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
                                   value="{{ old('discount') }}"
                                   placeholder="10"
                                   required>
                            <div class="form-text" id="discountHelp">Enter a percentage or fixed amount based on the selected type.</div>
                            @error('discount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Used Count</label>
                            <input type="text" class="form-control bg-light" value="0" readonly disabled>
                            <div class="form-text">Managed automatically when customers place orders.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="valid_from" class="form-label">Valid From</label>
                            <input type="date"
                                   class="form-control @error('valid_from') is-invalid @enderror"
                                   id="valid_from"
                                   name="valid_from"
                                   value="{{ old('valid_from') }}">
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
                                   value="{{ old('valid_until') }}">
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
                                   value="{{ old('minimum_cart_value', 0) }}"
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
                                   value="{{ old('maximum_discount_limit') }}"
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
                                   value="{{ old('usage_limit') }}"
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
                                   value="{{ old('per_user_limit') }}"
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
                    <h5 class="card-title mb-0">Quick Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-2">• Percentage coupons are capped at 100% in the controller.</li>
                        <li class="mb-2">• Fixed coupons should use the currency amount directly.</li>
                        <li class="mb-2">• Minimum cart value prevents low-value redemption.</li>
                        <li class="mb-2">• Usage limits help control campaign budgets.</li>
                        <li>• Dates are optional, so you can create evergreen coupons too.</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Create Coupon
                        </button>
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
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
        if (!type || !help) return;

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
