@extends('layouts.auth')

@section('title', 'Verify OTP')

@section('left-panel')
    <h1>Almost There!</h1>
    <p>We've sent a 6-digit verification code to your email address. Simply enter the code in the field below to complete your secure login.</p>
    <div class="mt-4">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-clock me-3" style="font-size: 1.25rem;"></i>
            <span>Code expires in 10 minutes</span>
        </div>
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-lightning me-3" style="font-size: 1.25rem;"></i>
            <span>Auto-submits when complete</span>
        </div>
        <div class="d-flex align-items-center">
            <i class="bi bi-envelope me-3" style="font-size: 1.25rem;"></i>
            <span>Check your spam folder if needed</span>
        </div>
    </div>
@endsection

@section('content')
    <h2 class="form-title">Enter Verification Code</h2>
    <p class="form-subtitle">
        We sent a code to 
        <strong>{{ session('otp_email') ? Str::mask(session('otp_email'), '*', 3, -4) : 'your email' }}</strong>
        <span class="badge bg-primary ms-2">Email</span>
    </p>
    
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

    <!-- OTP Status Display -->
    <div id="otpStatus" class="alert" style="display: none;"></div>
    
    <form method="POST" action="{{ route('otp.verify') }}" id="verifyForm">
        @csrf
        
        <!-- OTP Input Field -->
        <div class="form-floating mb-3">
            <input id="otp" type="text" 
                   class="form-control text-center @error('otp') is-invalid @enderror" 
                   name="otp" maxlength="6" 
                   autocomplete="one-time-code" autofocus required
                   placeholder="000000"
                   style="">
            <label for="otp">
                <i class="bi bi-shield-lock me-2"></i>Enter 6-digit OTP
            </label>
            
            @error('otp')
                <div class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </div>
            @enderror
        </div>
        
        <!-- Countdown Timer -->
        <div class="countdown-timer" id="countdown">
            <i class="bi bi-clock me-1"></i>
            <span id="timer">Checking OTP status...</span>
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary" id="verifyBtn" disabled>
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
            <i class="bi bi-check-circle me-2"></i>Verify & Login
        </button>
        
        <div class="text-center mt-2">
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                The form will auto-submit when you enter all 6 digits
            </small>
        </div>
    </form>
    
    <!-- Action Buttons -->
    <div class="text-center mt-3">
        <button type="button" class="btn btn-outline-primary btn-sm me-2" id="resendBtn" disabled>
            <i class="bi bi-arrow-clockwise me-2"></i>Resend Code
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="cancelBtn">
            <i class="bi bi-x-circle me-2"></i>Cancel
        </button>
    </div>
    
    <!-- Help Section -->
    <div class="auth-link">
        <p class="mb-0">
            Having trouble? 
            <a href="#" data-bs-toggle="modal" data-bs-target="#helpModal">Get Help</a> |
            <a href="{{ route('otp.login') }}">Try Different Method</a>
        </p>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">OTP Verification Help</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Code not received?</h6>
                    <ul class="list-unstyled mb-3">
                        <li class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i>Check your spam/junk folder</li>
                        <li class="mb-2"><i class="bi bi-clock me-2 text-primary"></i>Wait a few moments for delivery</li>
                        <li class="mb-2"><i class="bi bi-arrow-clockwise me-2 text-primary"></i>Click "Resend Code" after countdown</li>
                    </ul>
                    
                    <h6>Code not working?</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Make sure you're using the latest code</li>
                        <li class="mb-2"><i class="bi bi-type me-2 text-info"></i>Enter digits only, no spaces</li>
                        <li class="mb-2"><i class="bi bi-stopwatch me-2 text-danger"></i>Code expires in 10 minutes</li>
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
    let countdownInterval;
    let otpExpiryTime;
    let canResend = false;

    document.addEventListener('DOMContentLoaded', function() {
        initializeOtpInputs();
        checkOtpStatus();
        
        // Set up periodic status checks
        setInterval(checkOtpStatus, 30000); // Check every 30 seconds
    });

    function initializeOtpInputs() {
        const otpInput = document.getElementById('otp');
        const verifyBtn = document.getElementById('verifyBtn');

        otpInput.addEventListener('input', function(e) {
            // Only allow numbers
            const value = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = value;
            
            // Enable/disable submit button based on length
            verifyBtn.disabled = value.length !== 6;
            
            // Auto-submit when 6 digits are entered
            if (value.length === 6) {
                setTimeout(() => {
                    if (!verifyBtn.disabled) {
                        document.getElementById('verifyForm').submit();
                    }
                }, 500);
            }
        });

        otpInput.addEventListener('keydown', function(e) {
            // Allow backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

        otpInput.addEventListener('paste', function(e) {
            setTimeout(() => {
                const pastedData = e.target.value.replace(/[^0-9]/g, '').slice(0, 6);
                e.target.value = pastedData;
                verifyBtn.disabled = pastedData.length !== 6;
            }, 10);
        });

        otpInput.addEventListener('focus', function() {
            this.select();
        });

        // Auto-focus input
        otpInput.focus();
    }

    function checkOtpStatus() {
        fetch('{{ route("otp.status") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCountdown(data.data);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('Error checking OTP status:', error);
        });
    }

    function updateCountdown(data) {
        const statusDiv = document.getElementById('otpStatus');
        const countdownDiv = document.getElementById('countdown');
        const timerSpan = document.getElementById('timer');
        const resendBtn = document.getElementById('resendBtn');

        if (data.status === 'expired') {
            statusDiv.className = 'alert alert-warning';
            statusDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Your OTP has expired. Please request a new code.';
            statusDiv.style.display = 'block';
            
            timerSpan.textContent = 'Code expired';
            countdownDiv.className = 'countdown-timer text-danger';
            resendBtn.disabled = false;
            canResend = true;
            
        } else if (data.status === 'active') {
            statusDiv.style.display = 'none';
            
            const expiryTime = new Date(data.expires_at);
            const now = new Date();
            const timeLeft = Math.max(0, Math.floor((expiryTime - now) / 1000));
            
            if (timeLeft > 0) {
                startCountdown(timeLeft);
                countdownDiv.className = 'countdown-timer active';
                
                // Enable resend after 60 seconds
                resendBtn.disabled = timeLeft > (data.total_validity - 60);
                canResend = timeLeft <= (data.total_validity - 60);
            } else {
                timerSpan.textContent = 'Code expired';
                countdownDiv.className = 'countdown-timer text-danger';
                resendBtn.disabled = false;
                canResend = true;
            }
        } else {
            statusDiv.className = 'alert alert-info';
            statusDiv.innerHTML = '<i class="bi bi-info-circle me-2"></i>No active OTP found. Please request a new code.';
            statusDiv.style.display = 'block';
            
            timerSpan.textContent = 'No active code';
            resendBtn.disabled = false;
            canResend = true;
        }
    }

    function startCountdown(seconds) {
        clearInterval(countdownInterval);
        
        countdownInterval = setInterval(() => {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            
            document.getElementById('timer').textContent = 
                `Code expires in ${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
            
            if (seconds <= 60 && !canResend) {
                document.getElementById('resendBtn').disabled = false;
                canResend = true;
            }
            
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                document.getElementById('timer').textContent = 'Code expired';
                document.getElementById('countdown').className = 'countdown-timer text-danger';
                checkOtpStatus();
            }
            
            seconds--;
        }, 1000);
    }

    // Form submission
    document.getElementById('verifyForm').addEventListener('submit', function(e) {
        const verifyBtn = document.getElementById('verifyBtn');
        verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verifying...';
        verifyBtn.disabled = true;
    });

    // Resend OTP
    document.getElementById('resendBtn').addEventListener('click', function() {
        if (!canResend) return;
        
        const btn = this;
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
        btn.disabled = true;
        
        fetch('{{ route("otp.resend") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('New verification code sent successfully!');
                checkOtpStatus();
                clearOtpInputs();
            } else {
                showError(data.message || 'Failed to resend OTP');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            showError('Failed to resend OTP. Please try again.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });

    // Cancel OTP
    document.getElementById('cancelBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to cancel OTP verification?')) {
            fetch('{{ route("otp.cancel") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(() => {
                window.location.href = '{{ route("otp.login") }}';
            });
        }
    });

    function clearOtpInputs() {
        const otpInput = document.getElementById('otp');
        otpInput.value = '';
        document.getElementById('verifyBtn').disabled = true;
        otpInput.focus();
    }

    function showSuccess(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success position-fixed top-0 end-0 m-3';
        alert.style.zIndex = '9999';
        alert.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + message;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    }

    function showError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger position-fixed top-0 end-0 m-3';
        alert.style.zIndex = '9999';
        alert.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + message;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }
@endsection