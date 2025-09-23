<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Login Integration Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .demo-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .demo-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .provider-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        .provider-card:hover {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1);
        }
        .provider-enabled {
            border-color: #28a745;
            background: #f8fff8;
        }
        .provider-disabled {
            border-color: #6c757d;
            background: #f8f9fa;
            opacity: 0.7;
        }
        .status-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid white;
        }
        .status-enabled { background-color: #28a745; }
        .status-disabled { background-color: #6c757d; }
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            color: white;
        }
        .social-btn-demo {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .social-btn-demo:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="demo-card p-5">
                        <div class="text-center mb-5">
                            <h1 class="display-4 fw-bold text-primary mb-3">
                                <i class="bi bi-shield-check me-3"></i>Social Login Integration
                            </h1>
                            <p class="lead text-muted">Dynamic, scalable social authentication system</p>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="feature-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <i class="bi bi-gear-fill"></i>
                                    </div>
                                    <h5>Dynamic Configuration</h5>
                                    <p class="text-muted">Easy to add/remove providers via environment variables</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="feature-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                        <i class="bi bi-shield-lock-fill"></i>
                                    </div>
                                    <h5>Secure Authentication</h5>
                                    <p class="text-muted">OAuth 2.0 with single device login enforcement</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="feature-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                    <h5>User Management</h5>
                                    <p class="text-muted">Automatic user creation and social account linking</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-4"><i class="bi bi-list-check me-2"></i>Supported Providers</h4>
                                
                                <div id="providers-list">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading providers...</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h4 class="mb-4"><i class="bi bi-play-circle me-2"></i>Test Social Login</h4>
                                
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Note:</strong> To test social login, configure the OAuth credentials in your .env file.
                                </div>

                                <!-- Live Social Login Buttons -->
                                <div id="social-buttons">
                                    @if(isset($socialProviders) && count($socialProviders) > 0)
                                        @foreach($socialProviders as $key => $provider)
                                            @if($provider['enabled'])
                                                <a href="{{ route('social.redirect', $key) }}" 
                                                   class="social-btn-demo text-dark"
                                                   style="background: {{ $provider['background'] }}; color: {{ $provider['text_color'] }};">
                                                    
                                                    @if($key === 'google')
                                                        <svg width="20" height="20" viewBox="0 0 24 24" class="me-2">
                                                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                                        </svg>
                                                    @elseif($key === 'facebook')
                                                        <i class="bi bi-facebook me-2" style="color: {{ $provider['color'] }};"></i>
                                                    @elseif($key === 'github')
                                                        <i class="bi bi-github me-2" style="color: {{ $provider['color'] }};"></i>
                                                    @elseif($key === 'linkedin')
                                                        <i class="bi bi-linkedin me-2" style="color: {{ $provider['color'] }};"></i>
                                                    @elseif($key === 'twitter')
                                                        <i class="bi bi-twitter-x me-2" style="color: {{ $provider['color'] }};"></i>
                                                    @endif
                                                    
                                                    Continue with {{ $provider['name'] }}
                                                </a>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            No social providers configured. Add OAuth credentials to your .env file.
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4">
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-100">
                                        <i class="bi bi-arrow-right me-2"></i>Go to Login Page
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-12">
                                <h4 class="mb-4"><i class="bi bi-code-square me-2"></i>Implementation Details</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="bi bi-file-earmark-code me-2"></i>Controller</h6>
                                                <p class="card-text small">SocialLoginController handles all OAuth flows dynamically</p>
                                                <code class="small">app/Http/Controllers/Auth/SocialLoginController.php</code>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="bi bi-diagram-3 me-2"></i>Routes</h6>
                                                <p class="card-text small">Single route pattern handles all providers</p>
                                                <code class="small">/auth/{provider}<br>/auth/{provider}/callback</code>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="bi bi-database me-2"></i>Database</h6>
                                                <p class="card-text small">JSON column stores social provider data</p>
                                                <code class="small">users.social_providers</code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load providers dynamically
        function loadProviders() {
            fetch('/auth/providers')
                .then(response => response.json())
                .then(data => {
                    displayProviders(data.providers);
                })
                .catch(error => {
                    console.error('Error loading providers:', error);
                    document.getElementById('providers-list').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Failed to load providers. Check console for details.
                        </div>
                    `;
                });
        }

        function displayProviders(providers) {
            const container = document.getElementById('providers-list');
            
            if (Object.keys(providers).length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No providers configured. Add OAuth credentials to enable social login.
                    </div>
                `;
                return;
            }

            let html = '';
            for (const [key, provider] of Object.entries(providers)) {
                html += `
                    <div class="provider-card p-3 mb-3 position-relative ${provider.enabled ? 'provider-enabled' : 'provider-disabled'}">
                        <div class="status-badge ${provider.enabled ? 'status-enabled' : 'status-disabled'}"></div>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                ${getProviderIcon(key, provider.color)}
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${provider.name}</h6>
                                <small class="text-muted">
                                    Status: <span class="badge ${provider.enabled ? 'bg-success' : 'bg-secondary'}">
                                        ${provider.enabled ? 'Enabled' : 'Disabled'}
                                    </span>
                                </small>
                            </div>
                            ${provider.enabled ? `
                                <a href="/auth/${key}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </a>
                            ` : `
                                <span class="text-muted small">Configure in .env</span>
                            `}
                        </div>
                    </div>
                `;
            }
            container.innerHTML = html;
        }

        function getProviderIcon(key, color) {
            const iconMap = {
                'google': `<svg width="24" height="24" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>`,
                'facebook': `<i class="bi bi-facebook" style="color: ${color}; font-size: 24px;"></i>`,
                'github': `<i class="bi bi-github" style="color: ${color}; font-size: 24px;"></i>`,
                'linkedin': `<i class="bi bi-linkedin" style="color: ${color}; font-size: 24px;"></i>`,
                'twitter': `<i class="bi bi-twitter-x" style="color: ${color}; font-size: 24px;"></i>`
            };
            return iconMap[key] || `<i class="bi bi-box-arrow-in-right" style="color: ${color}; font-size: 24px;"></i>`;
        }

        // Load providers on page load
        loadProviders();
    </script>
</body>
</html>