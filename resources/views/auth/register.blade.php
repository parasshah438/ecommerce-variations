@extends('layouts.auth')

@section('title', 'Register')
@section('container-width', '1000px')
@section('container-height', '600px')

@section('additional-css')
    <!-- International Telephone Input CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
@endsection

@section('additional-js')
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
@endsection

@section('left-panel')
    <h1>Join Our Community</h1>
    <p>Create your account and become part of our amazing community. Enjoy exclusive features, personalized experiences, and much more.</p>
    <div class="mt-4">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-check-circle-fill me-3" style="font-size: 1.25rem;"></i>
            <span>Secure and encrypted data</span>
        </div>
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-check-circle-fill me-3" style="font-size: 1.25rem;"></i>
            <span>24/7 customer support</span>
        </div>
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-3" style="font-size: 1.25rem;"></i>
            <span>Free account forever</span>
        </div>
    </div>
@endsection

@section('content')
    <h2 class="form-title">Create Account</h2>
    <p class="form-subtitle">Fill in your information to get started</p>
    
    <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf
        
        <!-- Name Field -->
        <div class="form-floating">
            <input id="name" type="text" 
                   class="form-control @error('name') is-invalid @enderror" 
                   name="name" value="{{ old('name') }}" 
                    autocomplete="name" autofocus
                   placeholder="Full Name">
            <label for="name">
                <i class="bi bi-person me-2"></i>{{ __('Full Name') }}
            </label>
            @error('name')
                <div class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </div>
            @enderror
        </div>
        
        <!-- Email Field -->
        <div class="form-floating">
            <input id="email" type="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" 
                    autocomplete="email"
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
        
        <!-- Mobile Number Field with Country Code -->
        <div class="phone-input-container">
            <input id="mobile_number" type="tel" 
                   class="form-control @error('mobile_number') is-invalid @enderror" 
                   name="mobile_number" value="{{ old('mobile_number') }}"
                   placeholder="Enter mobile number">
            <input type="hidden" name="country_code" id="country_code" value="{{ old('country_code') }}">
            @error('mobile_number')
                <div class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </div>
            @enderror
            @error('country_code')
                <div class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </div>
            @enderror
        </div>
        
        <!-- Password Field -->
        <div class="form-floating">
            <input id="password" type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   name="password"  autocomplete="new-password"
                   placeholder="Password">
            <label for="password">
                <i class="bi bi-lock me-2"></i>{{ __('Password') }}
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
                    autocomplete="new-password"
                   placeholder="Confirm Password">
            <label for="password-confirm">
                <i class="bi bi-lock-fill me-2"></i>{{ __('Confirm Password') }}
            </label>
            <div id="passwordMatch" class="invalid-feedback" style="display: none;">
                Passwords do not match
            </div>
        </div>
        
        <!-- Terms and Conditions -->
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="terms" name="terms">
            <label class="form-check-label" for="terms">
                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
            </label>
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
            {{ __('Create Account') }}
        </button>
    </form>
    
    <!-- Login Link -->
    <div class="auth-link">
        <p class="mb-0">Already have an account? <a href="{{ route('login') }}">Sign in here</a></p>
    </div>
@endsection

@section('scripts')
    // Initialize International Telephone Input
    const phoneInputField = document.querySelector("#mobile_number");
    const phoneInput = window.intlTelInput(phoneInputField, {
        initialCountry: "auto",
        geoIpLookup: function(callback) {
            // Use our server-side geolocation endpoint to avoid CORS issues
            fetch('/api/geo-location', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(function(data) {
                if (data.success && data.country_code) {
                    callback(data.country_code.toLowerCase());
                } else {
                    callback("us");
                }
            })
            .catch(function(error) {
                console.log('Geolocation lookup failed:', error);
                callback("us");
            });
        },
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js",
        preferredCountries: ["us", "gb", "ca", "au", "in"],
        separateDialCode: true,
        autoPlaceholder: "aggressive",
        customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
            return "e.g. " + selectedCountryPlaceholder;
        }
    });
    
    // Update country code when country changes
    phoneInputField.addEventListener('countrychange', function() {
        document.getElementById('country_code').value = phoneInput.getSelectedCountryData().dialCode;
    });
    
    // Set initial country code
    document.getElementById('country_code').value = phoneInput.getSelectedCountryData().dialCode;
    
    // Client-side uniqueness validation
    let emailCheckTimeout;
    let mobileCheckTimeout;
    let isEmailAvailable = true;
    let isMobileAvailable = true;
    
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
    
    // Email validation and uniqueness check
    const emailField = document.getElementById('email');
    emailField.addEventListener('input', function() {
        const email = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(emailCheckTimeout);
        
        // Reset email availability flag when user starts typing
        isEmailAvailable = true;
        
        // Remove existing custom error messages and invalid state
        const existingError = this.parentNode.querySelector('.email-check-error');
        if (existingError) {
            existingError.remove();
        }
        this.classList.remove('is-invalid');
        
        // Reset submit button state
        resetSubmitButton();
        
        if (email && email.includes('@')) {
            // Debounce the API call
            emailCheckTimeout = setTimeout(() => {
                checkEmailUniqueness(email);
            }, 500);
        }
    });
    
    emailField.addEventListener('blur', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailRegex.test(this.value)) {
            this.classList.add('is-invalid');
        }
    });
    
    // Mobile validation and uniqueness check
    phoneInputField.addEventListener('input', function() {
        const mobile = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(mobileCheckTimeout);
        
        // Reset mobile availability flag when user starts typing
        isMobileAvailable = true;
        
        // Remove existing custom error messages and invalid state
        const existingError = this.parentNode.querySelector('.mobile-check-error');
        if (existingError) {
            existingError.remove();
        }
        this.classList.remove('is-invalid');
        
        // Reset submit button state
        resetSubmitButton();
        
        if (mobile && mobile.length >= 10) {
            // Debounce the API call
            mobileCheckTimeout = setTimeout(() => {
                checkMobileUniqueness(mobile);
            }, 500);
        }
    });
    
    // Check email uniqueness via API
    function checkEmailUniqueness(email) {
        fetch('/api/check-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            const emailField = document.getElementById('email');
            const existingError = emailField.parentNode.querySelector('.email-check-error');
            
            if (existingError) {
                existingError.remove();
            }
            
            if (!data.available) {
                isEmailAvailable = false;
                emailField.classList.add('is-invalid');
                
                // Add custom error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback email-check-error';
                errorDiv.innerHTML = '<strong>' + data.message + '</strong>';
                emailField.parentNode.appendChild(errorDiv);
            } else {
                isEmailAvailable = true;
                emailField.classList.remove('is-invalid');
            }
        })
        .catch(error => {
            console.error('Error checking email:', error);
        });
    }
    
    // Check mobile uniqueness via API
    function checkMobileUniqueness(mobile) {
        fetch('/api/check-mobile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ mobile_number: mobile })
        })
        .then(response => response.json())
        .then(data => {
            const mobileField = document.getElementById('mobile_number');
            const existingError = mobileField.parentNode.querySelector('.mobile-check-error');
            
            if (existingError) {
                existingError.remove();
            }
            
            if (!data.available) {
                isMobileAvailable = false;
                mobileField.classList.add('is-invalid');
                
                // Add custom error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback mobile-check-error';
                errorDiv.innerHTML = '<strong>' + data.message + '</strong>';
                mobileField.parentNode.appendChild(errorDiv);
            } else {
                isMobileAvailable = true;
                mobileField.classList.remove('is-invalid');
            }
        })
        .catch(error => {
            console.error('Error checking mobile:', error);
        });
    }
    
    // Function to reset submit button state
    function resetSubmitButton() {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '{{ __('Create Account') }}';
    }
    
    // Enhanced form submission validation
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        // Check if email and mobile are available
        if (!isEmailAvailable) {
            e.preventDefault();
            document.getElementById('email').focus();
            return false;
        }
        
        if (!isMobileAvailable) {
            e.preventDefault();
            document.getElementById('mobile_number').focus();
            return false;
        }
        
        // Check password match
        if (passwordField.value !== confirmPasswordField.value) {
            e.preventDefault();
            confirmPasswordField.focus();
            return false;
        }
        
        // Check terms acceptance
        if (!document.getElementById('terms').checked) {
            e.preventDefault();
            document.getElementById('terms').focus();
            return false;
        }
        
        // Validate phone number
        if (phoneInput.isValidNumber()) {
            document.getElementById('mobile_number').value = phoneInput.getNumber();
        } else if (phoneInputField.value.trim() !== '') {
            e.preventDefault();
            phoneInputField.classList.add('is-invalid');
            return false;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating Account...';
    });
@endsection