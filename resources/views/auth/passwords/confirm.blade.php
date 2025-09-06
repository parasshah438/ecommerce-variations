@extends('layouts.auth')

@section('title', 'Confirm Password')
@section('container-width', '800px')
@section('container-height', '500px')

@section('left-panel')
    <h1>Security Check</h1>
    <p>For your security, please confirm your password before accessing sensitive areas of your account. This helps protect your personal information.</p>
    <div class="mt-4">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-shield-lock me-3" style="font-size: 1.25rem;"></i>
            <span>Enhanced security protection</span>
        </div>
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-clock me-3" style="font-size: 1.25rem;"></i>
            <span>Session timeout protection</span>
        </div>
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle me-3" style="font-size: 1.25rem;"></i>
            <span>Secure access verification</span>
        </div>
    </div>
@endsection

@section('content')
    <h2 class="form-title">Confirm Password</h2>
    <p class="form-subtitle">Please confirm your password to continue</p>
    
    <!-- Security Notice -->
    <div class="security-notice">
        <i class="bi bi-info-circle"></i>
        <div class="security-notice-content">
            <div class="security-notice-title">Security Verification Required</div>
            <div class="security-notice-text">
                {{ __('Please confirm your password before continuing.') }}
            </div>
        </div>
    </div>
    
    <!-- Display validation errors -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form method="POST" action="{{ route('password.confirm') }}" id="confirmForm">
        @csrf
        
        <!-- Password Field -->
        <div class="form-floating">
            <input id="password" type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   name="password" required autocomplete="current-password" autofocus
                   placeholder="Current Password">
            <label for="password">
                <i class="bi bi-lock me-2"></i>{{ __('Current Password') }}
            </label>
            @error('password')
                <div class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </div>
            @enderror
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
                {{ __('Confirm Password') }}
            </button>
            
            @if (Route::has('password.request'))
                <a class="btn-link" href="{{ route('password.request') }}">
                    <i class="bi bi-question-circle me-2"></i>{{ __('Forgot Your Password?') }}
                </a>
            @endif
        </div>
    </form>
    
    <!-- Back Link -->
    <div class="auth-link">
        <a href="{{ url()->previous() }}">
            <i class="bi bi-arrow-left me-2"></i>Go Back
        </a>
    </div>
@endsection

@section('scripts')
    // Form submission loading state
    const form = document.getElementById('confirmForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Confirming...';
    });
    
    // Focus on password field when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const passwordField = document.getElementById('password');
        passwordField.focus();
    });
@endsection