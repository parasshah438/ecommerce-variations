@extends('layouts.frontend')

@section('title', 'Track Your Order - ' . config('app.name'))

@section('breadcrumb')
<div class="bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Track Order</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <!-- Header Section -->
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-search display-5 text-primary"></i>
                </div>
                <h1 class="display-6 fw-bold mb-3">Track Your Order</h1>
                <p class="lead text-muted">Enter your order details below to track your shipment status</p>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Please correct the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Tracking Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('order.track.search') }}" method="POST" id="trackingForm">
                        @csrf
                        
                        <!-- Order Number -->
                        <div class="mb-4">
                            <label for="order_number" class="form-label fw-semibold">
                                <i class="bi bi-receipt me-2 text-primary"></i>Order Number
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('order_number') is-invalid @enderror" 
                                   id="order_number" 
                                   name="order_number" 
                                   placeholder="e.g., ORD-123456789" 
                                   value="{{ old('order_number') }}"
                                   required>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                You can find your order number in the confirmation email
                            </div>
                            @error('order_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Contact Type Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-check me-2 text-primary"></i>Verify Identity With
                            </label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card h-100">
                                        <input class="form-check-input" type="radio" name="contact_type" id="email_option" value="email" {{ old('contact_type', 'email') === 'email' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 p-3 border rounded" for="email_option">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-envelope-check fs-4 text-primary me-3"></i>
                                                <div>
                                                    <div class="fw-semibold">Email Address</div>
                                                    <small class="text-muted">Use the email from your order</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card h-100">
                                        <input class="form-check-input" type="radio" name="contact_type" id="phone_option" value="phone" {{ old('contact_type') === 'phone' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 p-3 border rounded" for="phone_option">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-telephone-check fs-4 text-primary me-3"></i>
                                                <div>
                                                    <div class="fw-semibold">Phone Number</div>
                                                    <small class="text-muted">Use the phone from your order</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-4">
                            <label for="contact_info" class="form-label fw-semibold">
                                <span id="contact_label_text">
                                    <i class="bi bi-envelope me-2 text-primary"></i>Email Address
                                </span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('contact_info') is-invalid @enderror" 
                                   id="contact_info" 
                                   name="contact_info" 
                                   placeholder="Enter your email address"
                                   value="{{ old('contact_info') }}"
                                   required>
                            <div class="form-text" id="contact_help_text">
                                <i class="bi bi-shield-check me-1"></i>
                                This information is used to verify your identity
                            </div>
                            @error('contact_info')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-search me-2"></i>
                                <span class="btn-text">Track My Order</span>
                                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-5">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body text-center p-4">
                                <i class="bi bi-question-circle display-6 text-info mb-3"></i>
                                <h5 class="card-title">Need Help?</h5>
                                <p class="card-text text-muted">Can't find your order number? Check your email confirmation or contact our support team.</p>
                                <a href="{{ route('pages.support') }}" class="btn btn-outline-info">Get Support</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body text-center p-4">
                                <i class="bi bi-person-circle display-6 text-success mb-3"></i>
                                <h5 class="card-title">Have an Account?</h5>
                                <p class="card-text text-muted">Log in to view all your orders and get detailed tracking information.</p>
                                <a href="{{ route('login') }}" class="btn btn-outline-success">Sign In</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-check-card .form-check-input {
    display: none;
}

.form-check-card .form-check-input:checked + .form-check-label {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary) !important;
}

.form-check-card .form-check-input:checked + .form-check-label .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
}

.form-check-card .form-check-input:checked + .form-check-label .text-primary {
    color: white !important;
}

.form-check-card .form-check-label {
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-check-card .form-check-label:hover {
    border-color: var(--bs-primary) !important;
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}

.btn:disabled {
    pointer-events: none;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailOption = document.getElementById('email_option');
    const phoneOption = document.getElementById('phone_option');
    const contactInfo = document.getElementById('contact_info');
    const contactLabel = document.getElementById('contact_label_text');
    const contactHelp = document.getElementById('contact_help_text');
    const form = document.getElementById('trackingForm');
    const submitBtn = document.getElementById('submitBtn');

    // Update form based on contact type selection
    function updateContactForm() {
        if (emailOption.checked) {
            contactLabel.innerHTML = '<i class="bi bi-envelope me-2 text-primary"></i>Email Address';
            contactInfo.placeholder = 'Enter your email address';
            contactInfo.type = 'email';
            contactHelp.innerHTML = '<i class="bi bi-shield-check me-1"></i>Enter the email address used when placing the order';
        } else if (phoneOption.checked) {
            contactLabel.innerHTML = '<i class="bi bi-telephone me-2 text-primary"></i>Phone Number';
            contactInfo.placeholder = 'Enter your phone number';
            contactInfo.type = 'tel';
            contactHelp.innerHTML = '<i class="bi bi-shield-check me-1"></i>Enter the phone number used when placing the order';
        }
    }

    // Event listeners for radio buttons
    emailOption.addEventListener('change', updateContactForm);
    phoneOption.addEventListener('change', updateContactForm);

    // Form submission with loading state
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text').textContent = 'Searching...';
        submitBtn.querySelector('.spinner-border').classList.remove('d-none');
    });

    // Auto-format order number
    document.getElementById('order_number').addEventListener('input', function(e) {
        let value = e.target.value.toUpperCase();
        // Remove any non-alphanumeric characters except hyphens
        value = value.replace(/[^A-Z0-9-]/g, '');
        e.target.value = value;
    });

    // Initialize form
    updateContactForm();
});
</script>
@endsection