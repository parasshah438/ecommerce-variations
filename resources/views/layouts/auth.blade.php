<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Authentication') - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @yield('additional-css')
    
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            max-width: @yield('container-width', '900px');
            width: 100%;
            min-height: @yield('container-height', '550px');
        }
        
        .auth-left {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .auth-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .auth-left h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .auth-left p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }
        
        .auth-right {
            padding: 60px 40px;
        }
        
        .form-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .form-subtitle {
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-floating > .form-control {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 1rem 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--secondary-color);
        }
        
        .form-floating > .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
            background: white;
        }
        
        .form-floating > label {
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .phone-input-container {
            position: relative;
            margin-bottom: 1.5rem;
            z-index: 10;
        }
        
        .phone-input-container .form-label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .iti {
            width: 100%;
            position: relative;
        }
        
        .iti__flag-container {
            border-radius: 12px 0 0 12px;
        }
        
        .iti__selected-flag {
            border-radius: 12px 0 0 12px;
            background: var(--secondary-color);
            border: 2px solid var(--border-color);
            border-right: none;
            transition: all 0.3s ease;
        }
        
        .iti__selected-flag:hover {
            background: white;
        }
        
        .iti__country-list {
            background: white !important;
            border: 2px solid var(--border-color) !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
            z-index: 9999 !important;
            max-height: 200px !important;
            overflow-y: auto !important;
            margin-top: 2px !important;
        }
        
        .iti__country {
            padding: 8px 12px !important;
            border-bottom: 1px solid var(--border-color) !important;
            transition: all 0.2s ease !important;
        }
        
        .iti__country:hover {
            background: var(--secondary-color) !important;
        }
        
        .iti__country.iti__highlight {
            background: var(--primary-color) !important;
            color: white !important;
        }
        
        .iti__country:last-child {
            border-bottom: none !important;
        }
        
        .iti__flag {
            margin-right: 8px !important;
        }
        
        .iti__country-name {
            color: var(--text-primary) !important;
            font-weight: 500 !important;
        }
        
        .iti__dial-code {
            color: var(--text-secondary) !important;
        }
        
        .iti__highlight .iti__country-name,
        .iti__highlight .iti__dial-code {
            color: white !important;
        }
        
        #mobile_number {
            border: 2px solid var(--border-color);
            border-radius: 0 12px 12px 0;
            padding: 1rem 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--secondary-color);
            border-left: none;
        }
        
        #mobile_number:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
            background: white;
        }
        
        #mobile_number:focus + .iti__selected-flag {
            border-color: var(--primary-color);
            background: white;
        }
        
        .iti__country-list::-webkit-scrollbar {
            width: 6px;
        }
        
        .iti__country-list::-webkit-scrollbar-track {
            background: var(--secondary-color);
            border-radius: 3px;
        }
        
        .iti__country-list::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 3px;
        }
        
        .iti__country-list::-webkit-scrollbar-thumb:hover {
            background: var(--text-secondary);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .btn-link:hover {
            color: var(--primary-dark);
        }
        
        .auth-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }
        
        .auth-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .auth-link a:hover {
            color: var(--primary-dark);
        }
        
        .invalid-feedback {
            display: block;
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .form-control.is-invalid {
            border-color: var(--error-color);
        }
        
        .form-check {
            margin-bottom: 1.5rem;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }
        
        .social-login {
            margin-bottom: 2rem;
        }
        
        .social-btn {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            background: white;
        }
        
        .social-btn:hover {
            border-color: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border-color);
        }
        
        .divider span {
            background: white;
            padding: 0 1rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 1.5rem;
            padding: 1rem 1.25rem;
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }
        
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: var(--border-color);
            margin-top: 0.25rem;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak .strength-fill { width: 25%; background: var(--error-color); }
        .strength-fair .strength-fill { width: 50%; background: #f59e0b; }
        .strength-good .strength-fill { width: 75%; background: #3b82f6; }
        .strength-strong .strength-fill { width: 100%; background: var(--success-color); }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .security-notice {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
        }
        
        .security-notice i {
            color: var(--warning-color);
            margin-right: 0.75rem;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }
        
        .security-notice-content {
            flex: 1;
        }
        
        .security-notice-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .security-notice-text {
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.4;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        @media (min-width: 576px) {
            .action-buttons {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
            
            .action-buttons .btn-primary {
                width: auto;
                margin-top: 0;
            }
            
            .action-buttons .btn-link {
                margin-top: 0;
            }
        }
        
        @media (max-width: 768px) {
            .auth-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .auth-left {
                padding: 40px 30px;
                text-align: center;
            }
            
            .auth-left h1 {
                font-size: 2rem;
            }
            
            .auth-right {
                padding: 40px 30px;
            }
            
            .form-title {
                font-size: 1.75rem;
            }
        }
        
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .auth-left, .auth-right {
                padding: 30px 20px;
            }
        }
        
        @yield('additional-styles')
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="row g-0 h-100">
            @hasSection('left-panel')
                <!-- Left Side - Information Section -->
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="auth-left h-100">
                        <div>
                            @yield('left-panel')
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Right Side - Form Section -->
            <div class="@hasSection('left-panel') col-lg-7 @else col-12 @endif">
                <div class="auth-right h-100 d-flex align-items-center">
                    <div class="w-100">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('additional-js')
    
    <script>
        // Common JavaScript functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth transitions to form elements
            const formElements = document.querySelectorAll('.form-control, .btn, .social-btn');
            formElements.forEach(element => {
                element.style.transition = 'all 0.3s ease';
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
            
            // Common form submission loading state
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    form.addEventListener('submit', function(e) {
                        const spinner = submitBtn.querySelector('.spinner-border');
                        if (spinner) {
                            submitBtn.disabled = true;
                            spinner.style.display = 'inline-block';
                        }
                    });
                }
            });
            
            // Real-time validation for required inputs
            const inputs = document.querySelectorAll('input[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid') && this.value.trim() !== '') {
                        this.classList.remove('is-invalid');
                    }
                });
            });
        });
        
        @yield('scripts')
    </script>
</body>
</html>