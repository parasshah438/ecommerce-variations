@extends('layouts.app')

@section('title', 'Change Password')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('profile.manage') }}" class="text-decoration-none">Profile</a></li>
<li class="breadcrumb-item active" aria-current="page">Change Password</li>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <!-- Password Change Card -->
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <!-- Header -->
                        <div class="card-header bg-gradient border-0 p-4" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.05));">
                            <h3 class="fw-bold mb-2 text-primary">
                                <i class="bi bi-shield-lock me-2"></i>Change Password
                            </h3>
                            <p class="text-muted mb-0">Keep your account secure with a strong password</p>
                        </div>

                        <!-- Form Body -->
                        <div class="card-body p-4">
                            @if(!empty($socialProviders) && !$user->password)
                                <div class="alert alert-info alert-dismissible fade show rounded-3 border-0" role="alert">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-info-circle-fill me-3 mt-1 fs-5"></i>
                                        <div>
                                            <strong>Social Login Account</strong>
                                            <p class="mb-0 small mt-1">You're currently using social login ({{ implode(', ', $socialProviders) }}). Setting a password will allow you to log in directly with your email.</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form id="passwordForm" method="POST" action="{{ route('profile.password.update') }}">
                                @csrf
                                @method('PUT')

                                @if($user->password)
                                    <div class="mb-4">
                                        <div class="form-floating">
                                            <input type="password" 
                                                   class="form-control @error('current_password') is-invalid @enderror" 
                                                   id="current_password" 
                                                   name="current_password" 
                                                   required
                                                   placeholder="Enter current password">
                                            <label for="current_password">
                                                <i class="bi bi-key me-2"></i>Current Password *
                                            </label>
                                            @error('current_password')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-4">
                                    <div class="form-floating">
                                        <input type="password" 
                                               class="form-control @error('new_password') is-invalid @enderror" 
                                               id="new_password" 
                                               name="new_password" 
                                               required
                                               placeholder="Enter new password">
                                        <label for="new_password">
                                            <i class="bi bi-lock me-2"></i>New Password *
                                        </label>
                                        @error('new_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Password Strength Meter -->
                                    <div class="password-strength mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted fw-semibold">Password strength:</small>
                                            <small id="strengthText" class="text-muted fw-semibold">Enter password</small>
                                        </div>
                                        <div class="strength-meter rounded-pill overflow-hidden" style="height: 6px; background: var(--bs-light); border: 1px solid var(--bs-border-color);">
                                            <div class="strength-fill" id="strengthFill" style="width: 0%; height: 100%; transition: width 0.3s ease, background-color 0.3s ease;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-floating">
                                        <input type="password" 
                                               class="form-control @error('new_password_confirmation') is-invalid @enderror" 
                                               id="new_password_confirmation" 
                                               name="new_password_confirmation" 
                                               required
                                               placeholder="Confirm new password">
                                        <label for="new_password_confirmation">
                                            <i class="bi bi-lock-fill me-2"></i>Confirm New Password *
                                        </label>
                                        @error('new_password_confirmation')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Password Requirements -->
                                <div class="password-requirements rounded-3 p-4 mb-4" style="background: var(--bs-light); border: 1px solid var(--bs-border-color);">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-checklist me-2"></i>Password Requirements:</h6>
                                    <div class="requirement not-met p-2 rounded-2 mb-2" id="req-length">
                                        <i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i>
                                        <span>At least 8 characters long</span>
                                    </div>
                                    <div class="requirement not-met p-2 rounded-2 mb-2" id="req-uppercase">
                                        <i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i>
                                        <span>Contains uppercase letter (A-Z)</span>
                                    </div>
                                    <div class="requirement not-met p-2 rounded-2 mb-2" id="req-lowercase">
                                        <i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i>
                                        <span>Contains lowercase letter (a-z)</span>
                                    </div>
                                    <div class="requirement not-met p-2 rounded-2 mb-2" id="req-number">
                                        <i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i>
                                        <span>Contains number (0-9)</span>
                                    </div>
                                    <div class="requirement not-met p-2 rounded-2" id="req-symbol">
                                        <i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i>
                                        <span>Contains special character (!@#$%^&*)</span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center gap-3 pt-3 border-top">
                                    <a href="{{ route('profile.manage') }}" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-arrow-left me-2"></i>Back to Profile
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-shield-check me-2"></i>Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Security Tips Card -->
                    <div class="card border-0 shadow-sm rounded-3 mt-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb me-2"></i>Security Tips</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><small>Use a unique password that you don't use elsewhere</small></li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><small>Mix uppercase, lowercase, numbers, and symbols</small></li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><small>Avoid using personal information like names or dates</small></li>
                                <li><i class="bi bi-check-circle text-success me-2"></i><small>Change your password regularly for better security</small></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="text-center text-white">
        <div class="spinner-border mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="fw-semibold">Updating your password...</div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Password Strength Meter */
    .strength-fill {
        transition: width 0.3s ease, background-color 0.3s ease;
    }

    .strength-fill.weak {
        background-color: #dc3545;
        width: 25%;
    }

    .strength-fill.fair {
        background-color: #fd7e14;
        width: 50%;
    }

    .strength-fill.good {
        background-color: #ffc107;
        width: 75%;
    }

    .strength-fill.strong {
        background-color: #28a745;
        width: 100%;
    }

    /* Password Requirements */
    .requirement {
        display: flex;
        align-items: center;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        color: var(--text-secondary);
    }

    .requirement.met {
        color: #28a745;
        background: rgba(40, 167, 69, 0.1) !important;
        border-left: 3px solid #28a745;
    }

    .requirement.not-met {
        color: var(--text-secondary);
    }

    .requirement i {
        width: 20px;
        margin-right: 0.5rem;
    }

    /* Form Styling */
    .form-floating > label {
        color: var(--text-secondary);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }

    .form-control.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    /* Button Styling */
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-outline-secondary {
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
        transform: translateY(-2px);
    }

    /* Alert Styling */
    .alert {
        border-radius: 15px;
        border: 1px solid rgba(13, 110, 253, 0.2);
    }

    .alert-info {
        background: rgba(13, 110, 253, 0.05);
        color: #0c5de4;
    }

    /* Card Styling */
    .card {
        border-radius: 20px !important;
        border: 1px solid var(--border-color) !important;
        box-shadow: var(--shadow) !important;
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: var(--shadow-lg) !important;
    }

    .card-header {
        border-radius: 20px 20px 0 0 !important;
    }

    /* Dark Mode Support */
    [data-theme="dark"] .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
    }

    [data-theme="dark"] .card-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.1)) !important;
        border-color: var(--border-color) !important;
    }

    [data-theme="dark"] .card-body {
        background: var(--card-bg) !important;
    }

    [data-theme="dark"] .password-requirements {
        background: rgba(102, 126, 234, 0.05) !important;
        border-color: rgba(102, 126, 234, 0.2) !important;
    }

    [data-theme="dark"] .requirement {
        color: #9ca3af;
    }

    [data-theme="dark"] .requirement.met {
        background: rgba(40, 167, 69, 0.15) !important;
        color: #4ade80;
        border-left-color: #4ade80;
    }

    [data-theme="dark"] .form-floating > label {
        color: #9ca3af;
    }

    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select {
        background-color: #374151;
        border-color: #4b5563;
        color: #f9fafb;
    }

    [data-theme="dark"] .form-control:focus,
    [data-theme="dark"] .form-select:focus {
        background-color: #374151;
        border-color: #667eea;
        color: #f9fafb;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }

    [data-theme="dark"] .form-control::placeholder {
        color: #9ca3af;
    }

    [data-theme="dark"] .form-select option {
        background-color: #374151;
        color: #f9fafb;
    }

    [data-theme="dark"] .form-control.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    [data-theme="dark"] .invalid-feedback {
        color: #f87171;
    }

    [data-theme="dark"] .alert-info {
        background: rgba(13, 110, 253, 0.1);
        color: #60a5fa;
        border-color: rgba(13, 110, 253, 0.2);
    }

    [data-theme="dark"] .alert-info .btn-close {
        filter: invert(1);
    }

    [data-theme="dark"] .text-muted {
        color: #9ca3af !important;
    }

    [data-theme="dark"] .border-top {
        border-color: #4b5563 !important;
    }

    [data-theme="dark"] .strength-meter {
        background: #374151;
        border-color: #4b5563;
    }

    [data-theme="dark"] .btn-outline-secondary {
        color: #818cf8;
        border-color: #818cf8;
    }

    [data-theme="dark"] .btn-outline-secondary:hover {
        background-color: #818cf8;
        border-color: #818cf8;
        color: #111827;
    }

    [data-theme="dark"] .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    [data-theme="dark"] .btn-primary:hover {
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .col-lg-8 {
            padding: 0 1rem;
        }

        .card-body {
            padding: 1.5rem !important;
        }

        .card-header {
            padding: 1.5rem !important;
        }

        .btn-lg {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }

        .d-flex.justify-content-between .btn {
            width: 100%;
        }

        .requirement {
            font-size: 0.85rem;
            padding: 0.5rem !important;
        }

        .password-requirements {
            padding: 1rem !important;
        }
    }

    @media (max-width: 576px) {
        .card {
            border-radius: 15px !important;
        }

        .card-header {
            border-radius: 15px 15px 0 0 !important;
        }

        .card-body {
            padding: 1.25rem !important;
        }

        .form-floating > label {
            font-size: 0.85rem;
        }

        h3 {
            font-size: 1.25rem;
        }

        .password-requirements {
            padding: 0.75rem !important;
        }
    }

    /* Loading Animation */
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .spinner-border {
        animation: spin 1s linear infinite;
    }
</style>
@endpush

@push('scripts')
<script>
    // Password strength checker
    const newPasswordInput = document.getElementById('new_password');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');

    const requirements = {
        length: document.getElementById('req-length'),
        uppercase: document.getElementById('req-uppercase'),
        lowercase: document.getElementById('req-lowercase'),
        number: document.getElementById('req-number'),
        symbol: document.getElementById('req-symbol')
    };

    function checkPasswordStrength(password) {
        let strength = 0;
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            symbol: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };

        // Update requirement indicators
        Object.keys(checks).forEach(key => {
            if (checks[key]) {
                requirements[key].classList.remove('not-met');
                requirements[key].classList.add('met');
                strength++;
            } else {
                requirements[key].classList.remove('met');
                requirements[key].classList.add('not-met');
            }
        });

        // Update strength meter
        strengthFill.className = 'strength-fill';
        if (password.length === 0) {
            strengthFill.style.width = '0%';
            strengthText.textContent = 'Enter password';
            strengthText.className = 'text-muted fw-semibold';
        } else if (strength <= 1) {
            strengthFill.classList.add('weak');
            strengthText.textContent = 'Weak';
            strengthText.className = 'text-danger fw-semibold';
        } else if (strength <= 2) {
            strengthFill.classList.add('fair');
            strengthText.textContent = 'Fair';
            strengthText.className = 'text-warning fw-semibold';
        } else if (strength <= 3) {
            strengthFill.classList.add('good');
            strengthText.textContent = 'Good';
            strengthText.className = 'text-warning fw-semibold';
        } else {
            strengthFill.classList.add('strong');
            strengthText.textContent = 'Strong';
            strengthText.className = 'text-success fw-semibold';
        }
    }

    newPasswordInput.addEventListener('input', function() {
        checkPasswordStrength(this.value);
    });

    // Form submission
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('new_password_confirmation').value;

        if (newPassword !== confirmPassword) {
            toastr.error('Passwords do not match!');
            return;
        }

        // Show loading overlay
        document.getElementById('loadingOverlay').style.display = 'flex';

        // Submit form
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('loadingOverlay').style.display = 'none';
            
            if (data.success) {
                toastr.success('Password updated successfully!');
                setTimeout(() => {
                    window.location.href = '{{ route("profile.manage") }}';
                }, 2000);
            } else {
                toastr.error(data.message || 'Failed to update password');
            }
        })
        .catch(error => {
            document.getElementById('loadingOverlay').style.display = 'none';
            console.error('Error:', error);
            toastr.error('An error occurred while updating password');
        });
    });

    // Initialize password strength on page load
    window.addEventListener('DOMContentLoaded', function() {
        checkPasswordStrength('');
    });
</script>
@endpush
