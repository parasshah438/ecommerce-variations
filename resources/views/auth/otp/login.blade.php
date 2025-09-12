@extends('layouts.auth')

@section('title', 'OTP Login')

@section('left-panel')
    <h1>Secure Access</h1>
    <p>Get instant access to your account with our secure OTP verification system. No passwords needed - just enter your email or mobile number and we'll send you a verification code.</p>
    <div class="mt-4">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-shield-check me-3" style="font-size: 1.25rem;"></i>
            <span>Advanced security with OTP</span>
        </div>
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-stopwatch me-3" style="font-size: 1.25rem;"></i>
            <span>Quick 30-second verification</span>
        </div>
        <div class="d-flex align-items-center">
            <i class="bi bi-phone me-3" style="font-size: 1.25rem;"></i>
            <span>Works with email or mobile</span>
        </div>
    </div>
@endsection

@section('content')
    <h2 class="form-title">Login with OTP</h2>
    <p class="form-subtitle">Enter your email or mobile number to receive a verification code</p>
    
    <!-- Display success messages -->
    @if (session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <!-- Display error messages -->
    @if (session('error'))
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif
    
    <!-- Display validation errors -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="bi bi-exclamation-circle me-2"></i>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form method="POST" action="{{ route('otp.send') }}" id="otpForm">
        @csrf
        
        <!-- Email Field -->
        <div class="form-floating">
            <input id="email" type="text" 
                   class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email', request('email')) }}" 
                   autocomplete="email" autofocus required
                   placeholder="Email Address">
            <label for="email">
                <i class="bi bi-envelope me-2"></i>Email Address
            </label>
            @error('email')
                <div class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </div>
            @enderror
        </div>
    
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary" id="sendOtpBtn">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
            <i class="bi bi-send me-2"></i>Send Verification Code
        </button>
    </form>
    
    <!-- Alternative Login Options -->
    <div class="text-center mt-4">
        <div class="divider">
            <span>or</span>
        </div>
        
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-lock me-2"></i>Login with Password
            </a>
            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-person-plus me-2"></i>Create Account
            </a>
        </div>
    </div>
    
    <!-- Help Section -->
    <div class="auth-link">
        <p class="mb-0">Need help? <a href="#" data-bs-toggle="modal" data-bs-target="#helpModal">Contact Support</a></p>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">OTP Login Help</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>How does OTP login work?</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-1-circle me-2 text-primary"></i>Enter your email or mobile number</li>
                        <li class="mb-2"><i class="bi bi-2-circle me-2 text-primary"></i>Choose to receive OTP via Email or SMS</li>
                        <li class="mb-2"><i class="bi bi-3-circle me-2 text-primary"></i>Enter the 6-digit code you receive</li>
                        <li class="mb-2"><i class="bi bi-4-circle me-2 text-primary"></i>Get instant access to your account</li>
                    </ul>
                    
                    <h6 class="mt-3">Troubleshooting:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-1"><i class="bi bi-question-circle me-2 text-info"></i>Code not received? Check spam folder or try SMS</li>
                        <li class="mb-1"><i class="bi bi-question-circle me-2 text-info"></i>Code expired? Request a new one</li>
                        <li class="mb-1"><i class="bi bi-question-circle me-2 text-info"></i>Still having issues? Contact our support team</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    // Form validation
    const emailField = document.getElementById('email');
    const emailRadio = document.getElementById('email_type');
    const smsRadio = document.getElementById('sms_type');
    const form = document.getElementById('otpForm');
    const sendBtn = document.getElementById('sendOtpBtn');

    // Real-time validation
    emailField.addEventListener('blur', function() {
        const value = this.value.trim();
        if (value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(value)) {
                this.classList.add('is-invalid');
                showCustomError(this, 'Please enter a valid email address');
            } else {
                this.classList.remove('is-invalid');
                hideCustomError(this);
            }
        }
    });

    // Form submission handling
    form.addEventListener('submit', function(e) {
        // Show loading state
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending Code...';
        sendBtn.disabled = true;
    });

    // Radio button change handlers
    document.querySelectorAll('input[name="type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const email = emailField.value.trim();
            if (this.value === 'sms' && email.includes('@')) {
                showToast('Note: SMS will be sent to the mobile number associated with this email', 'info');
            }
        });
    });
    // Utility functions
    function showCustomError(element, message) {
        let errorDiv = element.parentNode.querySelector('.custom-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback custom-error';
            element.parentNode.appendChild(errorDiv);
        }
        errorDiv.innerHTML = '<strong>' + message + '</strong>';
        errorDiv.style.display = 'block';
    }

    function hideCustomError(element) {
        const errorDiv = element.parentNode.querySelector('.custom-error');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }

    function showToast(message, type = 'info') {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Auto-focus on page load
    window.addEventListener('load', function() {
        emailField.focus();
    });
@endsection