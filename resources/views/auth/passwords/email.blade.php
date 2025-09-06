@extends('layouts.auth')

@section('title', 'Reset Password')
@section('container-width', '800px')
@section('container-height', '500px')

@section('left-panel')
    <h1>Forgot Password?</h1>
    <p>No worries! Enter your email address and we'll send you a link to reset your password. The process is quick and secure.</p>
    <div class="mt-4">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-shield-lock me-3" style="font-size: 1.25rem;"></i>
            <span>Secure reset process</span>
        </div>
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-clock me-3" style="font-size: 1.25rem;"></i>
            <span>Link expires in 60 minutes</span>
        </div>
        <div class="d-flex align-items-center">
            <i class="bi bi-envelope-check me-3" style="font-size: 1.25rem;"></i>
            <span>Check your email inbox</span>
        </div>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <div class="text-center mb-4">
            <div class="success-icon">
                <i class="bi bi-check-circle-fill" style="font-size: 2rem; color: var(--success-color);"></i>
            </div>
            <h2 class="form-title">Email Sent!</h2>
            <p class="form-subtitle">We've sent a password reset link to your email address.</p>
            <div class="alert alert-success">
                <i class="bi bi-info-circle me-2"></i>
                {{ session('status') }}
            </div>
            <div class="auth-link">
                <a href="{{ route('login') }}">
                    <i class="bi bi-arrow-left me-2"></i>Back to Login
                </a>
            </div>
        </div>
    @else
        <h2 class="form-title">Reset Password</h2>
        <p class="form-subtitle">Enter your email address to receive a password reset link</p>
        
        <form method="POST" action="{{ route('password.email') }}" id="resetForm">
            @csrf
            
            <!-- Email Field -->
            <div class="form-floating">
                <input id="email" type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       name="email" value="{{ old('email') }}" 
                       required autocomplete="email" autofocus
                       placeholder="Email Address">
                <label for="email">
                    <i class="bi bi-envelope me-2"></i>{{ __('Email Address') }}
                </label>
                @error('email')
                    <div class="invalid-feedback">
                        <strong>{{ $message }}</strong>
                    </div>
                @enderror
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
                {{ __('Send Reset Link') }}
            </button>
        </form>
        
        <!-- Back to Login Link -->
        <div class="auth-link">
            <a href="{{ route('login') }}">
                <i class="bi bi-arrow-left me-2"></i>Back to Login
            </a>
        </div>
    @endif
@endsection

@section('scripts')
    // Email validation
    const emailField = document.getElementById('email');
    if (emailField) {
        emailField.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.classList.add('is-invalid');
            } else if (this.value) {
                this.classList.remove('is-invalid');
            }
        });
        
        emailField.addEventListener('input', function() {
            if (this.classList.contains('is-invalid') && this.value.trim() !== '') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailRegex.test(this.value)) {
                    this.classList.remove('is-invalid');
                }
            }
        });
    }
    
    // Form submission loading state
    const form = document.getElementById('resetForm');
    if (form) {
        const submitBtn = document.getElementById('submitBtn');
        form.addEventListener('submit', function(e) {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending Link...';
        });
    }
@endsection