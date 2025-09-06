@extends('layouts.otp')

@section('title', 'Verify OTP')

@section('content')
<div class="auth-header text-center mb-4">
    <div class="brand-logo mb-3">
        <div class="logo-circle">
            <i class="fas fa-shield-alt"></i>
        </div>
    </div>
    <h1 class="auth-title">Verify OTP</h1>
    <p class="auth-subtitle mb-2">Enter the 6-digit code sent to</p>
    <p class="email-display">{{ $email }}</p>
</div>

    <div class="auth-body">
        <div id="alertContainer"></div>
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <!-- OTP Form -->
        <form id="otpForm" method="POST" action="{{ route('otp.verify') }}">
            @csrf
            <div class="mb-4">
                <label for="otp" class="form-label">Verification Code</label>
                <div class="otp-input-container">
                    <input type="text" class="form-control otp-input text-center @error('otp') is-invalid @enderror" 
                           id="otp" name="otp" placeholder="000000" maxlength="6" autocomplete="off" required>
                    @error('otp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="countdown-info">
                    <small class="form-text text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Code expires in <span id="countdown" class="fw-bold text-primary">5:00</span>
                    </small>
                </div>
            </div>
            
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg btn-verify" id="verifyOtpBtn">
                    <span id="verifyOtpText">
                        <i class="fas fa-check me-2"></i>
                        Verify & Login
                    </span>
                    <span id="verifyOtpLoader" class="d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Verifying...
                    </span>
                </button>
            </div>
            
            <!-- Option to disable AJAX for testing (hidden by default) -->
            <div class="mb-3 d-none">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="useAjax" checked>
                    <label class="form-check-label small text-muted" for="useAjax">
                        Use AJAX submission (uncheck for regular form submission)
                    </label>
                </div>
            </div>

            <!-- Resend Section -->
            <div class="resend-section text-center">
                <p class="resend-text">Didn't receive the code?</p>
                <button type="button" class="btn btn-outline-primary btn-resend" id="resendBtn">
                    <span id="resendText">
                        <i class="fas fa-redo me-2"></i>
                        Resend Code
                    </span>
                    <span id="resendLoader" class="d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Sending...
                    </span>
                    <span id="resendCooldown" class="d-none">
                        Resend in <span id="resendCountdown">60</span>s
                    </span>
                </button>
            </div>
        </form>

        <!-- Actions -->
        <div class="action-buttons">
            <a href="{{ route('otp.login') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Change Email
            </a>
            <button type="button" class="btn btn-outline-danger" id="cancelBtn">
                <i class="fas fa-times me-2"></i>
                Cancel
            </button>
        </div>

        <!-- Security Info -->
        <div class="security-info">
            <h6 class="security-title">
                <i class="fas fa-shield-alt me-2"></i>
                Security Tips
            </h6>
            <ul class="security-list">
                <li><i class="fas fa-check text-success me-2"></i>Never share your OTP with anyone</li>
                <li><i class="fas fa-clock text-info me-2"></i>Code expires in 5 minutes</li>
                <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Maximum 3 attempts allowed</li>
            </ul>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpForm = document.getElementById('otpForm');
    const otpInput = document.getElementById('otp');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const verifyOtpText = document.getElementById('verifyOtpText');
    const verifyOtpLoader = document.getElementById('verifyOtpLoader');
    const resendBtn = document.getElementById('resendBtn');
    const resendText = document.getElementById('resendText');
    const resendLoader = document.getElementById('resendLoader');
    const resendCooldown = document.getElementById('resendCooldown');
    const resendCountdown = document.getElementById('resendCountdown');
    const cancelBtn = document.getElementById('cancelBtn');
    const alertContainer = document.getElementById('alertContainer');
    const countdown = document.getElementById('countdown');

    let otpExpiry = null;
    let resendCooldownTimer = null;
    let countdownTimer = null;

    // Initialize OTP status
    initializeOtpStatus();

    // Show alert function
    function showAlert(message, type = 'danger', autoHide = true) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        alertContainer.innerHTML = alertHtml;
        
        if (autoHide && type === 'success') {
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 3000);
        }
    }

    // Set loading state for verify button
    function setVerifyLoading(loading) {
        verifyOtpBtn.disabled = loading;
        if (loading) {
            verifyOtpText.classList.add('d-none');
            verifyOtpLoader.classList.remove('d-none');
        } else {
            verifyOtpText.classList.remove('d-none');
            verifyOtpLoader.classList.add('d-none');
        }
    }

    // Set loading state for resend button
    function setResendLoading(loading) {
        resendBtn.disabled = loading;
        if (loading) {
            resendText.classList.add('d-none');
            resendLoader.classList.remove('d-none');
            resendCooldown.classList.add('d-none');
        } else {
            resendText.classList.remove('d-none');
            resendLoader.classList.add('d-none');
        }
    }

    // Initialize OTP status
    function initializeOtpStatus() {
        fetch('{{ route("otp.status") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.status.has_active_otp) {
                startCountdown(data.status.remaining_seconds);
                
                if (data.status.cooldown_remaining > 0) {
                    startResendCooldown(data.status.cooldown_remaining);
                }
            } else {
                // Default to 5 minutes if no active OTP found
                startCountdown(300);
            }
        })
        .catch(error => {
            console.error('Error getting OTP status:', error);
            // Default to 5 minutes if request fails
            startCountdown(300);
        });
    }

    // Start countdown timer
    function startCountdown(seconds) {
        if (countdownTimer) {
            clearInterval(countdownTimer);
        }

        // Convert to integer to handle decimal seconds from server
        let remaining = Math.floor(parseFloat(seconds));
        
        // Ensure we don't start with negative time
        if (remaining <= 0) {
            countdown.textContent = 'Expired';
            countdown.parentElement.innerHTML = '<i class="fas fa-exclamation-triangle text-danger me-1"></i>Code has expired';
            verifyOtpBtn.disabled = true;
            return;
        }
        
        // Update display immediately
        const minutes = Math.floor(remaining / 60);
        const secs = remaining % 60;
        countdown.textContent = `${minutes}:${secs.toString().padStart(2, '0')}`;
        
        countdownTimer = setInterval(() => {
            remaining--;
            
            if (remaining <= 0) {
                clearInterval(countdownTimer);
                countdown.textContent = 'Expired';
                countdown.parentElement.innerHTML = '<i class="fas fa-exclamation-triangle text-danger me-1"></i>Code has expired';
                verifyOtpBtn.disabled = true;
                return;
            }

            const minutes = Math.floor(remaining / 60);
            const secs = remaining % 60;
            countdown.textContent = `${minutes}:${secs.toString().padStart(2, '0')}`;
        }, 1000);
    }

    // Start resend cooldown
    function startResendCooldown(seconds) {
        if (resendCooldownTimer) {
            clearInterval(resendCooldownTimer);
        }

        // Convert to integer to handle decimal seconds from server
        let remaining = Math.floor(parseFloat(seconds));
        
        // Ensure we don't start with negative time
        if (remaining <= 0) {
            resendBtn.disabled = false;
            resendText.classList.remove('d-none');
            resendCooldown.classList.add('d-none');
            return;
        }
        
        resendBtn.disabled = true;
        resendText.classList.add('d-none');
        resendCooldown.classList.remove('d-none');
        
        // Update display immediately
        resendCountdown.textContent = remaining;
        
        resendCooldownTimer = setInterval(() => {
            remaining--;
            
            if (remaining <= 0) {
                clearInterval(resendCooldownTimer);
                resendBtn.disabled = false;
                resendText.classList.remove('d-none');
                resendCooldown.classList.add('d-none');
                return;
            }

            resendCountdown.textContent = remaining;
        }, 1000);
    }

    // Auto-format OTP input
    otpInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 6) {
            value = value.slice(0, 6);
        }
        this.value = value;

        // Auto-submit when 6 digits entered
        if (value.length === 6) {
            setTimeout(() => {
                otpForm.dispatchEvent(new Event('submit'));
            }, 500);
        }
    });

    // Handle form submission
    otpForm.addEventListener('submit', function(e) {
        const useAjax = document.getElementById('useAjax').checked;
        
        // If AJAX is disabled, let the form submit normally
        if (!useAjax) {
            return; // Allow normal form submission
        }
        
        // Prevent default for AJAX submission
        e.preventDefault();
        
        const otp = otpInput.value.trim();
        
        if (!otp || otp.length !== 6) {
            showAlert('Please enter a valid 6-digit OTP');
            otpInput.focus();
            return;
        }

        setVerifyLoading(true);
        alertContainer.innerHTML = '';

        fetch('{{ route("otp.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ otp: otp })
        })
        .then(response => response.json())
        .then(data => {
            setVerifyLoading(false);
            
            if (data.success) {
                showAlert('Login successful! Redirecting...', 'success');
                
                setTimeout(() => {
                    window.location.href = data.redirect_url || '{{ route("dashboard") }}';
                }, 1500);
                
            } else {
                let errorMessage = data.message;
                
                if (data.attempts_remaining !== undefined) {
                    errorMessage += ` (${data.attempts_remaining} attempts remaining)`;
                }
                
                showAlert(errorMessage, 'danger', false);
                otpInput.value = '';
                otpInput.focus();
            }
        })
        .catch(error => {
            setVerifyLoading(false);
            console.error('Error:', error);
            showAlert('Something went wrong. Please try again later.');
            otpInput.focus();
        });
    });

    // Handle resend - Fixed implementation
    resendBtn.addEventListener('click', function() {
        setResendLoading(true);
        alertContainer.innerHTML = '';

        fetch('{{ route("otp.resend") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            setResendLoading(false);
            
            if (data.success) {
                let message = 'New OTP sent successfully!';
                
                @if(config('app.debug'))
                if (data.test_otp) {
                    message += `<br><strong>Test OTP:</strong> <span class="badge bg-warning text-dark">${data.test_otp}</span>`;
                }
                @endif
                
                showAlert(message, 'success');
                
                // Start new countdown
                startCountdown(300); // 5 minutes
                startResendCooldown(60); // 1 minute cooldown
                
                otpInput.value = '';
                otpInput.focus();
                
            } else {
                let errorMessage = data.message;
                
                if (data.type === 'cooldown' && data.remaining_seconds) {
                    startResendCooldown(data.remaining_seconds);
                } else {
                    showAlert(errorMessage, 'warning', false);
                }
            }
        })
        .catch(error => {
            setResendLoading(false);
            console.error('Error:', error);
            showAlert('Failed to resend OTP. Please try again later.');
        });
    });

    // Handle cancel
    cancelBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to cancel the OTP verification?')) {
            fetch('{{ route("otp.cancel") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(() => {
                window.location.href = '{{ route("otp.login") }}';
            })
            .catch(error => {
                console.error('Error:', error);
                window.location.href = '{{ route("otp.login") }}';
            });
        }
    });

    // Auto-focus OTP field
    otpInput.focus();

    // Cleanup timers on page unload
    window.addEventListener('beforeunload', function() {
        if (countdownTimer) clearInterval(countdownTimer);
        if (resendCooldownTimer) clearInterval(resendCooldownTimer);
    });
});
</script>
@endpush

@push('styles')
<style>
.auth-header {
    margin-bottom: 2rem;
}

.logo-circle {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.logo-circle i {
    font-size: 2rem;
    color: white;
}

.auth-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.auth-subtitle {
    font-size: 1rem;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.email-display {
    font-size: 1.1rem;
    font-weight: 600;
    color: #667eea;
    word-break: break-all;
}

.otp-input {
    font-size: 2.5rem;
    font-weight: bold;
    letter-spacing: 0.8rem;
    padding: 1.5rem 1rem;
    border-radius: 15px;
    border: 3px solid #e2e8f0;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.otp-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    outline: none;
    background: white;
    transform: scale(1.02);
}

.countdown-info {
    text-align: center;
    margin-top: 1rem;
}

.btn-verify {
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-verify:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-verify:disabled {
    opacity: 0.6;
    transform: none;
    box-shadow: none;
}

.resend-section {
    margin: 2rem 0;
}

.resend-text {
    font-size: 0.9rem;
    color: #64748b;
    margin-bottom: 1rem;
}

.btn-resend {
    border: 2px solid #667eea;
    color: #667eea;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.btn-resend:hover {
    background: #667eea;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.action-buttons .btn {
    flex: 1;
    min-width: 120px;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-weight: 500;
}

.security-info {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 15px;
    padding: 1.5rem;
    margin-top: 2rem;
    border: 1px solid #e2e8f0;
}

.security-title {
    font-size: 1rem;
    font-weight: 600;
    color: #667eea;
    margin-bottom: 1rem;
}

.security-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.security-list li {
    font-size: 0.9rem;
    color: #475569;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.security-list li:last-child {
    margin-bottom: 0;
}

/* Responsive Design */
@media (max-width: 576px) {
    .otp-input {
        font-size: 2rem;
        letter-spacing: 0.5rem;
        padding: 1rem 0.5rem;
    }
    
    .auth-title {
        font-size: 1.5rem;
    }
    
    .logo-circle {
        width: 60px;
        height: 60px;
    }
    
    .logo-circle i {
        font-size: 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons .btn {
        flex: none;
        width: 100%;
    }
}

@media (max-width: 768px) {
    .email-display {
        font-size: 1rem;
    }
    
    .btn-verify {
        padding: 0.875rem 1.5rem;
        font-size: 1rem;
    }
}

/* Animation for alerts */
.alert {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading states */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Focus improvements */
.btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.5);
}

.form-control:focus {
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}
</style>
@endpush
