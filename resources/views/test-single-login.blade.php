<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Single Device Login Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .device-info {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 15px 0;
        }
        .session-info {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-active { background-color: #28a745; }
        .status-warning { background-color: #ffc107; }
        .status-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <span class="status-indicator status-active"></span>
                            Single Device Login Test
                        </h3>
                    </div>
                    <div class="card-body">
                        @if(Auth::check())
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle"></i> You are logged in!</h5>
                                <p><strong>User:</strong> {{ Auth::user()->name }} ({{ Auth::user()->email }})</p>
                            </div>

                            <div class="session-info">
                                <h6><i class="fas fa-desktop"></i> Current Session Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Session ID:</strong> <code>{{ session()->getId() }}</code></p>
                                        <p><strong>User Agent:</strong> {{ request()->userAgent() }}</p>
                                        <p><strong>IP Address:</strong> {{ request()->ip() }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Active Session in DB:</strong> 
                                            @if(Auth::user()->active_session_id)
                                                <code>{{ Auth::user()->active_session_id }}</code>
                                            @else
                                                <span class="text-muted">None recorded</span>
                                            @endif
                                        </p>
                                        <p><strong>Last Login:</strong> 
                                            @if(Auth::user()->last_login_at)
                                                {{ Auth::user()->last_login_at->format('Y-m-d H:i:s') }}
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </p>
                                        <p><strong>Session Match:</strong> 
                                            @if(Auth::user()->active_session_id === session()->getId())
                                                <span class="badge bg-success">✓ Valid</span>
                                            @elseif(!Auth::user()->active_session_id)
                                                <span class="badge bg-warning">⚠ No DB Record</span>
                                            @else
                                                <span class="badge bg-danger">✗ Invalid</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="device-info">
                                <h6><i class="fas fa-mobile-alt"></i> Device Information</h6>
                                @if(Auth::user()->last_device_info)
                                    @php
                                        $deviceInfo = Auth::user()->last_device_info;
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Browser:</strong> {{ $deviceInfo['browser'] ?? 'Unknown' }}</p>
                                            <p><strong>Platform:</strong> {{ $deviceInfo['platform'] ?? 'Unknown' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Device:</strong> {{ $deviceInfo['device'] ?? 'Unknown' }}</p>
                                            <p><strong>Version:</strong> {{ $deviceInfo['version'] ?? 'Unknown' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Last IP:</strong> {{ Auth::user()->last_login_ip ?? 'Unknown' }}</p>
                                            <p><strong>Robot:</strong> {{ isset($deviceInfo['robot']) && $deviceInfo['robot'] ? 'Yes' : 'No' }}</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-muted">No device information available</p>
                                @endif
                            </div>

                            <div class="mt-4">
                                <h6>Test Instructions:</h6>
                                <ol>
                                    <li>Open this page in another browser or incognito window</li>
                                    <li>Login with the same credentials</li>
                                    <li>Refresh this page - you should be automatically logged out</li>
                                    <li>Check the logs for session validation activities</li>
                                </ol>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                    <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                                </a>
                                <a href="{{ route('cart.index') }}" class="btn btn-info">
                                    <i class="fas fa-shopping-cart"></i> View Cart
                                </a>
                                <a href="{{ route('logout') }}" class="btn btn-secondary"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-outline-info" onclick="window.location.reload()">
                                    <i class="fas fa-sync-alt"></i> Refresh Page
                                </button>
                                <button class="btn btn-outline-warning" onclick="checkSessionStatus()">
                                    <i class="fas fa-heartbeat"></i> Check Session Status
                                </button>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle"></i> You are not logged in</h5>
                                <p>Please login to test the single device login functionality.</p>
                                <a href="{{ route('login') }}" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            </div>
                        @endif

                        <div class="mt-4">
                            <h6>Feature Details:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><strong>Rate Limiting:</strong> 3 attempts per 2 minutes</li>
                                        <li class="list-group-item"><strong>Session Tracking:</strong> Database-based validation</li>
                                        <li class="list-group-item"><strong>Device Detection:</strong> Browser/Platform identification</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><strong>Auto Logout:</strong> When login from new device</li>
                                        <li class="list-group-item"><strong>Middleware:</strong> SingleSessionMiddleware</li>
                                        <li class="list-group-item"><strong>Security Logging:</strong> All activities logged</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script>
        function checkSessionStatus() {
            fetch('/cart/sync-counts')
                .then(response => {
                    if (response.status === 401) {
                        alert('Session expired! You have been logged out from another device.');
                        window.location.reload();
                    } else if (response.ok) {
                        alert('Session is valid and active!');
                    } else {
                        alert('Unable to check session status.');
                    }
                })
                .catch(error => {
                    console.error('Error checking session:', error);
                    alert('Error checking session status.');
                });
        }

        // Auto-refresh every 30 seconds to demonstrate session validation
        setInterval(function() {
            console.log('Auto-checking session status...');
            fetch('/cart/sync-counts', {
                method: 'GET',
                credentials: 'same-origin'
            }).then(response => {
                if (response.status === 401) {
                    console.log('Session invalid - redirecting to login');
                    window.location.reload();
                }
            }).catch(error => {
                console.error('Session check error:', error);
            });
        }, 30000);
    </script>
</body>
</html>