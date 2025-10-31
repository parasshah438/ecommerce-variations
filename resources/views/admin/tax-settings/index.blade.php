@extends('layouts.admin')

@section('title', 'Tax Settings')

@section('styles')
<style>
    .tax-settings-container {
        max-width: 800px;
    }
    
    .settings-card {
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
    }
    
    .test-calculator {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1rem;
    }
    
    .result-box {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        border-left: 4px solid var(--primary-color);
    }
    
    .form-floating label {
        color: #6c757d;
    }
    
    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
    }
    
    .badge-info {
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        border: 1px solid rgba(13, 110, 253, 0.2);
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Tax Settings</h1>
            <p class="text-muted mb-0">Configure GST and tax calculation settings</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Tax Settings</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Current Status Card -->
            <div class="card settings-card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-calculator text-primary me-2"></i>
                        Current Tax Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-primary mb-1">{{ ($settings['tax_rate'] * 100) }}%</div>
                                <small class="text-muted">Tax Rate</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 {{ $settings['tax_enabled'] ? 'text-success' : 'text-danger' }} mb-1">
                                    {{ $settings['tax_enabled'] ? 'Enabled' : 'Disabled' }}
                                </div>
                                <small class="text-muted">Tax Status</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-info mb-1">{{ $settings['tax_name'] }}</div>
                                <small class="text-muted">Tax Type</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <span class="badge badge-info px-2 py-1">
                                    {{ ucwords(str_replace('_', ' ', $settings['tax_calculate_on'])) }}
                                </span>
                                <div><small class="text-muted">Calculation Method</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Form -->
            <div class="card settings-card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-gear text-primary me-2"></i>
                        Tax Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tax-settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <!-- Tax Enabled -->
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="tax_enabled" 
                                           name="tax_enabled" value="1" {{ $settings['tax_enabled'] ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="tax_enabled">
                                        Enable Tax Calculation
                                    </label>
                                    <div class="form-text">Turn on/off tax calculation for all orders</div>
                                </div>
                            </div>

                            <!-- Tax Rate -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                                           value="{{ $settings['tax_rate'] }}" step="0.01" min="0" max="1" required>
                                    <label for="tax_rate">Tax Rate (as decimal) *</label>
                                </div>
                                <div class="form-text">
                                    Enter as decimal (e.g., 0.18 for 18%, 0.05 for 5%)
                                </div>
                            </div>

                            <!-- Tax Name -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="tax_name" name="tax_name" 
                                           value="{{ $settings['tax_name'] }}" required>
                                    <label for="tax_name">Tax Name *</label>
                                </div>
                                <div class="form-text">
                                    Display name for tax (GST, VAT, Tax, etc.)
                                </div>
                            </div>

                            <!-- Tax Calculation Method -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="tax_calculate_on" name="tax_calculate_on" required>
                                        <option value="after_discount" {{ $settings['tax_calculate_on'] === 'after_discount' ? 'selected' : '' }}>
                                            After Discount (Recommended)
                                        </option>
                                        <option value="before_discount" {{ $settings['tax_calculate_on'] === 'before_discount' ? 'selected' : '' }}>
                                            Before Discount
                                        </option>
                                    </select>
                                    <label for="tax_calculate_on">Tax Calculation Method *</label>
                                </div>
                                <div class="form-text">
                                    <strong>After Discount:</strong> Apply discount first, then calculate tax<br>
                                    <strong>Before Discount:</strong> Calculate tax on original amount, then apply discount
                                </div>
                            </div>

                            <!-- Tax Inclusive -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="tax_inclusive" name="tax_inclusive">
                                        <option value="0" {{ !$settings['tax_inclusive'] ? 'selected' : '' }}>
                                            Tax Exclusive (Add to price)
                                        </option>
                                        <option value="1" {{ $settings['tax_inclusive'] ? 'selected' : '' }}>
                                            Tax Inclusive (Include in price)
                                        </option>
                                    </select>
                                    <label for="tax_inclusive">Tax Type</label>
                                </div>
                                <div class="form-text">
                                    <strong>Exclusive:</strong> Tax added to product price<br>
                                    <strong>Inclusive:</strong> Tax included in product price (not yet implemented)
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Update Tax Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tax Calculator -->
            <div class="card settings-card mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-calculator-fill text-success me-2"></i>
                        Tax Calculator
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Test how tax will be calculated with current settings</p>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="test_subtotal" 
                                       value="1000" step="0.01" min="0">
                                <label for="test_subtotal">Subtotal (₹)</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="test_discount" 
                                       value="0" step="0.01" min="0">
                                <label for="test_discount">Discount (₹)</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="test_shipping" 
                                       value="50" step="0.01" min="0">
                                <label for="test_shipping">Shipping (₹)</label>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-success" onclick="calculateTax()">
                            <i class="bi bi-calculator me-1"></i>Calculate Tax
                        </button>
                    </div>

                    <div id="tax-result" class="result-box mt-3" style="display: none;">
                        <!-- Results will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function calculateTax() {
    const subtotal = document.getElementById('test_subtotal').value;
    const discount = document.getElementById('test_discount').value;
    const shipping = document.getElementById('test_shipping').value;
    
    fetch('{{ route("admin.tax-settings.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            subtotal: parseFloat(subtotal),
            discount: parseFloat(discount),
            shipping: parseFloat(shipping)
        })
    })
    .then(response => response.json())
    .then(data => {
        displayTaxResult(data);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error calculating tax. Please try again.');
    });
}

function displayTaxResult(data) {
    const resultDiv = document.getElementById('tax-result');
    
    resultDiv.innerHTML = `
        <h6 class="fw-bold mb-3">
            <i class="bi bi-receipt text-primary me-2"></i>Tax Calculation Result
        </h6>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex justify-content-between">
                    <span>Subtotal:</span>
                    <span class="fw-semibold">₹${data.subtotal}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Discount:</span>
                    <span class="fw-semibold text-success">-₹${data.discount}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Taxable Amount:</span>
                    <span class="fw-semibold">₹${data.taxable_amount}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between">
                    <span>Tax (${data.tax_rate_percentage}):</span>
                    <span class="fw-semibold text-warning">₹${data.tax_amount}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Shipping:</span>
                    <span class="fw-semibold">₹${data.shipping}</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <strong>Final Total:</strong>
                    <strong class="text-primary">₹${data.total}</strong>
                </div>
            </div>
        </div>
        <div class="mt-3 text-center">
            <span class="badge bg-info">
                Method: ${data.calculation_method.replace('_', ' ')}
            </span>
        </div>
    `;
    
    resultDiv.style.display = 'block';
}

// Auto-calculate when values change
document.addEventListener('DOMContentLoaded', function() {
    ['test_subtotal', 'test_discount', 'test_shipping'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() {
            // Auto-calculate after a short delay
            clearTimeout(this.timeout);
            this.timeout = setTimeout(calculateTax, 500);
        });
    });
    
    // Calculate initially
    calculateTax();
});
</script>
@endsection