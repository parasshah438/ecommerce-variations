@extends('layouts.auth')

@section('title', 'Login')

@section('left-panel')
    <h1>Welcome Back</h1>
    <p>Sign in to your account and continue your journey with us. Access your personalized dashboard and manage your preferences.</p>
    <div class="mt-4">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-shield-check me-3" style="font-size: 1.25rem;"></i>
            <span>Secure login process</span>
        </div>
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-lightning-charge me-3" style="font-size: 1.25rem;"></i>
            <span>Fast and reliable</span>
        </div>
        <div class="d-flex align-items-center">
            <i class="bi bi-headset me-3" style="font-size: 1.25rem;"></i>
            <span>24/7 support available</span>
        </div>
    </div>
@endsection

@section('content')
    <h2 class="form-title">Sign In</h2>
    <p class="form-subtitle">Enter your credentials to access your account</p>
    
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
    
    <!-- Social Login Options -->
    <div class="social-login">
        <!-- OTP Login (Always Available) -->
        <a href="{{ route('otp.login') }}" class="social-btn text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
            <i class="bi bi-phone me-2" style="font-size: 1.25rem;"></i>
            Login with OTP
        </a>
        
        <!-- Dynamic Social Login Buttons -->
        @if(isset($socialProviders) && count($socialProviders) > 0)
            <x-social-login-buttons :providers="$socialProviders" />
        @else
            <!-- Fallback static buttons (in case social providers are not loaded) -->
            <a href="{{ route('social.redirect', 'google') }}" class="social-btn text-dark">
                <svg width="20" height="20" viewBox="0 0 24 24" class="me-2">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continue with Google
            </a>
            <a href="{{ route('social.redirect', 'facebook') }}" class="social-btn text-dark">
                <i class="bi bi-facebook me-2" style="color: #1877F2; font-size: 1.25rem;"></i>
                Continue with Facebook
            </a>
        @endif
    </div>
    
    <div class="divider">
        <span>or sign in with email</span>
    </div>
    
    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf
        
        <!-- Email or Mobile Field -->
        <div class="form-floating">
            <input id="login_field" type="text" 
                   class="form-control @error('login_field') is-invalid @enderror @error('email') is-invalid @enderror" 
                   name="login_field" value="{{ old('login_field') }}" 
                   autocomplete="username" autofocus
                   placeholder="Email or Mobile Number">
            <label for="login_field">
                <i class="bi bi-person me-2"></i>{{ __('Email or Mobile Number') }}
            </label>
            @error('login_field')
                <div class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </div>
            @enderror
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
                   name="password" autocomplete="current-password"
                   placeholder="Password">
            <label for="password">
                <i class="bi bi-lock me-2"></i>{{ __('Password') }}
            </label>
            @error('password')
                <div class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </div>
            @enderror
        </div>
        
        <!-- Remember Me and Forgot Password -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">
                    {{ __('Remember Me') }}
                </label>
            </div>
            
            @if (Route::has('password.request'))
                <a class="btn-link" href="{{ route('password.request') }}">
                    {{ __('Forgot Password?') }}
                </a>
            @endif
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
            {{ __('Sign In') }}
        </button>
        
        <!-- OTP Login Alternative -->
        <div class="text-center mt-3">
            <p class="text-muted small mb-2">or</p>
            <button type="button" class="btn btn-outline-primary btn-sm" id="otpLoginBtn">
                <i class="fas fa-mobile-alt me-2"></i>
                Login with OTP instead
            </button>
        </div>
    </form>
    
    <!-- Register Link -->
    <div class="auth-link">
        <p class="mb-0">Don't have an account? <a href="{{ route('register') }}">Create one here</a></p>
    </div>
@endsection

@section('scripts')
    // Login field validation (email or mobile)
    const loginField = document.getElementById('login_field');
    loginField.addEventListener('blur', function() {
        const value = this.value.trim();
        if (value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const mobileRegex = /^\+?[1-9]\d{1,14}$/;
            
            if (!emailRegex.test(value) && !mobileRegex.test(value)) {
                this.classList.add('is-invalid');
                // Show custom error message
                let errorDiv = this.parentNode.querySelector('.custom-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback custom-error';
                    this.parentNode.appendChild(errorDiv);
                }
                errorDiv.innerHTML = '<strong>Please enter a valid email address or mobile number</strong>';
                errorDiv.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                const errorDiv = this.parentNode.querySelector('.custom-error');
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                }
            }
        }
    });
    
    // Update placeholder text based on input
    loginField.addEventListener('input', function() {
        const value = this.value.trim();
        const label = this.parentNode.querySelector('label');
        
        if (value.includes('@')) {
            label.innerHTML = '<i class="bi bi-envelope me-2"></i>Email Address';
        } else if (value.match(/^\+?[0-9]/)) {
            label.innerHTML = '<i class="bi bi-phone me-2"></i>Mobile Number';
        } else {
            label.innerHTML = '<i class="bi bi-person me-2"></i>Email or Mobile Number';
        }
    });
    
    // Form submission loading state
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Signing In...';
    });
    
    // OTP Login button handler
    const otpLoginBtn = document.getElementById('otpLoginBtn');
    otpLoginBtn.addEventListener('click', function() {
        const emailValue = loginField.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        // If valid email is entered, pass it to OTP login
        if (emailValue && emailRegex.test(emailValue)) {
            window.location.href = '{{ route("otp.login") }}?email=' + encodeURIComponent(emailValue);
        } else {
            window.location.href = '{{ route("otp.login") }}';
        }
    });
@endsection