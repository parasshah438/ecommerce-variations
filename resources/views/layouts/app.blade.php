<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'ECommerce')) - User Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --sidebar-bg: #ffffff;
            --sidebar-text: #6b7280;
            --sidebar-hover: #f3f4f6;
            --sidebar-active: #6366f1;
            --main-bg: #f9fafb;
            --card-bg: #ffffff;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] {
            --primary-color: #818cf8;
            --primary-hover: #6366f1;
            --sidebar-bg: #1f2937;
            --sidebar-text: #9ca3af;
            --sidebar-hover: #374151;
            --sidebar-active: #6366f1;
            --main-bg: #111827;
            --card-bg: #1f2937;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
            --border-color: #374151;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px -1px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -4px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--main-bg);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 3px;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
        }

        .logo-text h5 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .logo-text small {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .nav-section {
            padding: 1rem 0;
        }

        .nav-section-title {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            margin: 0.25rem 0.75rem;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--primary-color);
            transform: scaleY(0);
            transition: transform 0.2s ease;
        }

        .sidebar .nav-link:hover {
            background: var(--sidebar-hover);
            color: var(--text-primary);
            transform: translateX(2px);
        }

        .sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.1), transparent);
            color: var(--primary-color);
        }

        .sidebar .nav-link.active::before {
            transform: scaleY(1);
        }

        .sidebar .nav-link i {
            font-size: 1.125rem;
            margin-right: 0.75rem;
            width: 24px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Top Navigation */
        .top-nav {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            animation: slideInLeft 0.5s ease;
        }

        .top-nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Theme Toggle */
        .theme-toggle {
            position: relative;
            width: 50px;
            height: 26px;
            background: var(--sidebar-hover);
            border-radius: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid var(--border-color);
        }

        .theme-toggle::before {
            content: '☀️';
            position: absolute;
            left: 2px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        [data-theme="dark"] .theme-toggle::before {
            content: '🌙';
            left: calc(100% - 22px);
        }

        .theme-toggle-slider {
            position: absolute;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            top: 1px;
            left: 2px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        [data-theme="dark"] .theme-toggle-slider {
            left: calc(100% - 22px);
            background: #1f2937;
        }

        /* Notification Badge */
        .notification-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--sidebar-hover);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .notification-btn:hover {
            background: var(--sidebar-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 18px;
            height: 18px;
            background: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.625rem;
            color: white;
            font-weight: 600;
            animation: pulse 2s infinite;
        }

        /* User Dropdown */
        .user-dropdown {
            position: relative;
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            background: var(--sidebar-hover);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .user-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Content Area */
        .content-wrapper {
            padding: 2rem 1.5rem;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--sidebar-hover);
            border: 1px solid var(--border-color);
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .mobile-menu-toggle:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Animations */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .user-info {
                display: none;
            }

            .top-nav {
                padding: 1rem;
            }

            .content-wrapper {
                padding: 1.5rem 1rem;
            }
        }

        /* Loading States */
        .qty-input.loading {
            background: linear-gradient(90deg, var(--sidebar-hover) 0%, var(--border-color) 50%, var(--sidebar-hover) 100%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Demo Content Card */
        .demo-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            animation: fadeIn 0.5s ease;
            border: 1px solid var(--border-color);
        }

        .demo-card h3 {
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .demo-card p {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon.primary {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
        }

        .stat-icon.success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .stat-icon.warning {
            background: rgba(251, 146, 60, 0.1);
            color: #fb923c;
        }

        .stat-icon.danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .stat-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        /* Badge Styles */
        .badge {
            font-size: 0.625rem;
            padding: 0.25em 0.5em;
        }

        /* Dropdown Enhancements */
        .dropdown-menu {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
        }

        .dropdown-item {
            color: var(--text-primary);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            margin: 0.125rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: var(--sidebar-hover);
            color: var(--text-primary);
        }

        .dropdown-item.text-danger:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Breadcrumb Styles */
        .breadcrumb-item + .breadcrumb-item::before {
            content: ">";
            color: var(--text-secondary);
        }

        .breadcrumb-item a:hover {
            color: var(--primary-color) !important;
        }

        .breadcrumb-item.active {
            color: var(--text-primary);
        }

        /* Loading Animation for Actions */
        .btn.loading {
            position: relative;
            pointer-events: none;
        }

        .btn.loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-top: -8px;
            margin-left: -8px;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: currentColor;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="app">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-icon">{{ strtoupper(substr(config('app.name', 'E'), 0, 1)) }}</div>
                <div class="logo-text">
                    <h5>{{ config('app.name', 'ECommerce') }}</h5>
                    <small>User Dashboard</small>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="">
                    <i class="bi bi-person-circle"></i>My Profile
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Shopping</div>
                <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                    <i class="bi bi-bag-check"></i>My Orders
                    @if(auth()->user()->orders()->where('status', 'pending')->count() > 0)
                        <span class="badge bg-warning rounded-pill ms-auto">{{ auth()->user()->orders()->where('status', 'pending')->count() }}</span>
                    @endif
                </a>
                <a class="nav-link {{ request()->routeIs('wishlist.*') ? 'active' : '' }}" href="{{ route('wishlist.index') }}">
                    <i class="bi bi-heart"></i>Wishlist
                    @if(auth()->user()->wishlist()->count() > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ auth()->user()->wishlist()->count() }}</span>
                    @endif
                </a>
                <a class="nav-link {{ request()->routeIs('cart.*') ? 'active' : '' }}" href="{{ route('cart.index') }}">
                    <i class="bi bi-cart3"></i>Shopping Cart
                    @if(session('cart') && count(session('cart')) > 0)
                        <span class="badge bg-primary rounded-pill ms-auto">{{ count(session('cart')) }}</span>
                    @endif
                </a>
                <a class="nav-link {{ request()->routeIs('addresses.*') ? 'active' : '' }}" href="">
                    <i class="bi bi-geo-alt"></i>Addresses
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Support</div>
                <a class="nav-link {{ request()->routeIs('support.*') ? 'active' : '' }}" href="">
                    <i class="bi bi-headset"></i>Help Center
                </a>
                <a class="nav-link" href="{{ route('home') }}" target="_blank">
                    <i class="bi bi-shop"></i>Visit Store
                    <i class="bi bi-box-arrow-up-right ms-auto" style="font-size: 0.75rem;"></i>
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <div class="top-nav">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div class="mobile-menu-toggle" onclick="toggleSidebar()">
                        <i class="bi bi-list" style="font-size: 1.25rem; color: var(--text-primary);"></i>
                    </div>
                    <h1 class="page-title">@yield('title', 'Dashboard')</h1>
                </div>
                
                <div class="top-nav-actions">
                    <!-- Theme Toggle -->
                    <div class="theme-toggle" onclick="toggleTheme()">
                        <div class="theme-toggle-slider"></div>
                    </div>

                    <!-- Cart Notification -->
                    <a href="{{ route('cart.index') }}" class="notification-btn" style="text-decoration: none;">
                        <i class="bi bi-cart3" style="font-size: 1.125rem; color: var(--text-primary);"></i>
                        @if(session('cart') && count(session('cart')) > 0)
                            <span class="notification-badge">{{ count(session('cart')) }}</span>
                        @endif
                    </a>

                    <!-- Notifications -->
                    <div class="notification-btn" data-bs-toggle="dropdown">
                        <i class="bi bi-bell" style="font-size: 1.125rem; color: var(--text-primary);"></i>
                        @if(auth()->user()->notifications->count() > 0)
                            <span class="notification-badge">{{ auth()->user()->notifications->count() }}</span>
                        @endif
                    </div>
                    <!-- Notification Dropdown (you can expand this later) -->

                    <!-- User Dropdown -->
                    <div class="user-dropdown dropdown">
                        <div class="user-btn" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                            <div class="user-avatar">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email, 0, 1)) }}
                                @endif
                            </div>
                            <div class="user-info">
                                <span class="user-name">{{ auth()->user()->name ?? 'User' }}</span>
                                <span class="user-role">Customer</span>
                            </div>
                            <i class="bi bi-chevron-down" style="color: var(--text-secondary);"></i>
                        </div>
                        
                        <ul class="dropdown-menu dropdown-menu-end" style="border: 1px solid var(--border-color); box-shadow: var(--shadow-lg); border-radius: 12px; padding: 0.5rem;">
                            <li><a class="dropdown-item" href="">
                                <i class="bi bi-person me-2"></i>Profile Settings
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}">
                                <i class="bi bi-bag-check me-2"></i>My Orders
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Breadcrumb -->
            @if(!empty(trim($__env->yieldContent('breadcrumb'))))
            <div class="breadcrumb-wrapper" style="padding: 1rem 1.5rem; background: var(--card-bg); border-bottom: 1px solid var(--border-color);">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="background: transparent; margin: 0;">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" style="color: var(--text-secondary); text-decoration: none;">
                                <i class="bi bi-house-door me-1"></i>Dashboard
                            </a>
                        </li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
            @endif

            <!-- Content -->
            @yield('content')
        </main>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Theme Toggle
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);

        // Mobile Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });

        // Initialize toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Active nav link highlighting
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Laravel Session Notifications
        @if(session('success'))
            toastr.success('{{ session('success') }}', 'Success!');
        @endif

        @if(session('error'))
            toastr.error('{{ session('error') }}', 'Error!');
        @endif

        @if(session('warning'))
            toastr.warning('{{ session('warning') }}', 'Warning!');
        @endif

        @if(session('info'))
            toastr.info('{{ session('info') }}', 'Info');
        @endif

        // Display validation errors
        @if($errors->any())
            @foreach($errors->all() as $error)
                toastr.error('{{ $error }}', 'Validation Error');
            @endforeach
        @endif

        // CSRF Token Setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Welcome toast (only on first load)
        @if(!session()->has('dashboard_visited'))
            setTimeout(() => {
                toastr.success('Welcome to your dashboard!', 'Hello {{ auth()->user()->name ?? "User" }}!');
            }, 500);
            @php session(['dashboard_visited' => true]); @endphp
        @endif
    </script>
    @stack('scripts')
</body>
</html>