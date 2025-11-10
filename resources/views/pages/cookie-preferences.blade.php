<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Preferences - Your Company</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #007bff;
            --accent-color: #fd7e14;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 100px 0 60px;
        }
        
        .preference-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }
        
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 30px;
            background: #ccc;
            border-radius: 30px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .toggle-switch.active {
            background: var(--primary-color);
        }
        
        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 26px;
            height: 26px;
            background: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: left 0.3s;
        }
        
        .toggle-switch.active::after {
            left: 32px;
        }
    </style>
</head>
<body>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('welcome') }}">
                <i class="bi bi-shop"></i> Your Company
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('welcome') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('pages.cookie.preferences') }}">Cookie Preferences</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.cookie.policy') }}">Cookie Policy</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="text-center">
                <h1 class="display-4 fw-bold mb-4">
                    <i class="bi bi-sliders me-3"></i>Cookie Preferences
                </h1>
                <p class="lead">
                    Customize your cookie settings to control how we collect and use your data.
                </p>
            </div>
        </div>
    </section>

    <!-- Preferences Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    
                    <div class="preference-card">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <h5><i class="bi bi-shield-fill-check text-success me-2"></i>Essential Cookies</h5>
                                <p class="text-muted mb-0">Required for basic website functionality. Cannot be disabled.</p>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="toggle-switch active" data-required="true">
                                    <span class="visually-hidden">Essential cookies always enabled</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="preference-card">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <h5><i class="bi bi-bar-chart text-info me-2"></i>Analytics Cookies</h5>
                                <p class="text-muted mb-0">Help us understand how you use our website to improve your experience.</p>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="toggle-switch" data-cookie-type="analytics">
                                    <span class="visually-hidden">Toggle analytics cookies</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="preference-card">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <h5><i class="bi bi-bullseye text-warning me-2"></i>Marketing Cookies</h5>
                                <p class="text-muted mb-0">Used to show you relevant advertisements and measure their effectiveness.</p>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="toggle-switch" data-cookie-type="marketing">
                                    <span class="visually-hidden">Toggle marketing cookies</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="preference-card">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <h5><i class="bi bi-person-gear" style="color: #6f42c1;"></i> Preference Cookies</h5>
                                <p class="text-muted mb-0">Remember your settings and provide personalized features.</p>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="toggle-switch active" data-cookie-type="preferences">
                                    <span class="visually-hidden">Toggle preference cookies</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                        <button class="btn btn-outline-secondary" onclick="acceptOnlyEssential()">Accept Only Essential</button>
                        <button class="btn btn-success" onclick="acceptAllCookies()">Accept All</button>
                        <button class="btn btn-primary" onclick="savePreferences()">Save Preferences</button>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            <a href="{{ route('pages.cookie.policy') }}" class="text-decoration-none">Learn more about our Cookie Policy</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Toggle switch functionality
        document.querySelectorAll('.toggle-switch').forEach(toggle => {
            if (toggle.dataset.required !== 'true') {
                toggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                });
            }
        });

        // Cookie preference functions
        function acceptOnlyEssential() {
            document.querySelectorAll('.toggle-switch').forEach(toggle => {
                if (toggle.dataset.required !== 'true') {
                    toggle.classList.remove('active');
                }
            });
            savePreferences();
        }

        function acceptAllCookies() {
            document.querySelectorAll('.toggle-switch').forEach(toggle => {
                toggle.classList.add('active');
            });
            savePreferences();
        }

        function savePreferences() {
            const preferences = {};
            document.querySelectorAll('.toggle-switch[data-cookie-type]').forEach(toggle => {
                const type = toggle.dataset.cookieType;
                preferences[type] = toggle.classList.contains('active');
            });
            
            // Save to localStorage
            localStorage.setItem('cookiePreferences', JSON.stringify(preferences));
            
            // Show confirmation
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alert.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
            alert.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>
                <strong>Settings Saved!</strong> Your cookie preferences have been updated.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 3000);
        }

        // Load saved preferences on page load
        document.addEventListener('DOMContentLoaded', function() {
            const saved = localStorage.getItem('cookiePreferences');
            if (saved) {
                const preferences = JSON.parse(saved);
                Object.keys(preferences).forEach(type => {
                    const toggle = document.querySelector(`[data-cookie-type="${type}"]`);
                    if (toggle) {
                        if (preferences[type]) {
                            toggle.classList.add('active');
                        } else {
                            toggle.classList.remove('active');
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>