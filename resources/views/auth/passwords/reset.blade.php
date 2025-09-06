@extends('layouts.auth')

@section('title', 'Reset Password')
@section('container-height', '600px')

@section('left-panel')
    <h1>New Password</h1>
    <p>Create a strong, secure password for your account. Make sure it's something you'll remember but others can't guess.</p>
    <div class="mt-4">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-shield-check me-3" style="font-size: 1.25rem;"></i>
            <span>Secure password requirements</span>
        </div>
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-key me-3" style="font-size: 1.25rem;"></i>
            <span>Minimum 8 characters</span>
        </div>
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle me-3" style="font-size: 1.25rem;"></i>
            <span>Mix of letters, numbers & symbols</span>
        </div>
    </div>
@endsection

@section('content')
    <h2 class="form-title">Reset Password</h2>
    <p class="form-subtitle">Enter your new password below</p>
    
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
    
    <form method="POST" action="{{ route('password.update') }}" id="resetForm">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        
        <!-- Email Field -->
        <div class="form-floating">
            <input id="email" type="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ $email ?? old('email') }}" 
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
        
        <!-- Password Field -->
        <div class="form-floating">
            <input id="password" type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   name="password" required autocomplete="new-password"
                   placeholder="New Password">
            <label for="password">
                <i class="bi bi-lock me-2"></i>{{ __('New Password') }}
            </label>
            <div class="password-strength" id="passwordStrength" style="display: none;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Password strength:</small>
                    <small id="strengthText" class="text-muted">Weak</small>
                </div>
                <div class="strength-bar">
                    <div class="strength-fill"></div>
                </div>
            </div>
            @error('password')
                <div class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </div>
            @enderror
        </div>
        
        <!-- Confirm Password Field -->
        <div class="form-floating">
            <input id="password-confirm" type="password" 
                   class="form-control" name="password_confirmation" 
                   required autocomplete="new-password"
                   placeholder="Confirm New Password">
            <label for="password-confirm">
                <i class="bi bi-lock-fill me-2"></i>{{ __('Confirm New Password') }}
            </label>
            <div id="passwordMatch" class="invalid-feedback" style="display: none;">
                Passwords do not match
            </div>
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
            {{ __('Reset Password') }}
        </button>
    </form>
    
    <!-- Back to Login Link -->
    <div class="auth-link">
        <a href="{{ route('login') }}">
            <i class="bi bi-arrow-left me-2"></i>Back to Login
        </a>
    </div>
@endsection

@section('scripts')
    // Password strength checker
    const passwordField = document.getElementById('password');
    const passwordStrength = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('strengthText');
    
    passwordField.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        
        if (password.length > 0) {
            passwordStrength.style.display = 'block';
            passwordStrength.className = 'password-strength strength-' + strength.level;
            strengthText.textContent = strength.text;
            strengthText.className = 'text-' + strength.color;
        } else {
            passwordStrength.style.display = 'none';
        }
    });
    
    function checkPasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (password.match(/[a-z]/)) score++;
        if (password.match(/[A-Z]/)) score++;
        if (password.match(/[0-9]/)) score++;
        if (password.match(/[^a-zA-Z0-9]/)) score++;
        
        switch (score) {
            case 0:
            case 1:
                return { level: 'weak', text: 'Weak', color: 'danger' };
            case 2:
                return { level: 'fair', text: 'Fair', color: 'warning' };
            case 3:
            case 4:
                return { level: 'good', text: 'Good', color: 'info' };
            case 5:
                return { level: 'strong', text: 'Strong', color: 'success' };
            default:
                return { level: 'weak', text: 'Weak', color: 'danger' };
        }
    }
    
    // Password confirmation checker
    const confirmPasswordField = document.getElementById('password-confirm');
    const passwordMatch = document.getElementById('passwordMatch');
    
    function checkPasswordMatch() {
        if (confirmPasswordField.value.length > 0) {
            if (passwordField.value !== confirmPasswordField.value) {
                confirmPasswordField.classList.add('is-invalid');
                passwordMatch.style.display = 'block';
            } else {
                confirmPasswordField.classList.remove('is-invalid');
                passwordMatch.style.display = 'none';
            }
        }
    }
    
    confirmPasswordField.addEventListener('input', checkPasswordMatch);
    passwordField.addEventListener('input', checkPasswordMatch);
    
    // Form submission loading state
    const form = document.getElementById('resetForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Resetting Password...';
    });
    
    // Email validation
    const emailField = document.getElementById('email');
    emailField.addEventListener('blur', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailRegex.test(this.value)) {
            this.classList.add('is-invalid');
        }
    });
@endsection