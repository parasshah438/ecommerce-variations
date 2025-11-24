<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Admin Styles -->
    <style>
        :root {
            --admin-primary: #3b82f6;
            --admin-secondary: #64748b;
            --admin-success: #10b981;
            --admin-danger: #ef4444;
            --admin-warning: #f59e0b;
            --admin-info: #06b6d4;
            --admin-light: #f8fafc;
            --admin-dark: #1e293b;
            --admin-border: #e2e8f0;
            --admin-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .admin-sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand i {
            font-size: 2rem;
            color: var(--admin-primary);
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            color: #cbd5e1;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            color: white;
            background-color: rgba(59, 130, 246, 0.1);
            border-left-color: var(--admin-primary);
        }

        .nav-link i {
            width: 1.25rem;
            text-align: center;
            font-size: 1rem;
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .admin-main.expanded {
            margin-left: 0;
        }

        /* Top Bar */
        .admin-topbar {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--admin-shadow);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--admin-secondary);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .sidebar-toggle:hover {
            background-color: var(--admin-light);
            color: var(--admin-primary);
        }

        .topbar-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--admin-dark);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-dropdown {
            position: relative;
        }

        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid var(--admin-border);
            transition: border-color 0.2s;
        }

        .user-avatar:hover {
            border-color: var(--admin-primary);
        }

        /* Content Area */
        .admin-content {
            padding: 2rem;
            min-height: calc(100vh - 5rem);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .admin-content {
                padding: 1rem;
            }
        }

        /* Utility Classes */
        .btn {
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: var(--admin-shadow);
        }

        .form-control {
            border: 1px solid var(--admin-border);
            border-radius: 0.5rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1055;
        }

        .toast {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                    <i class="fas fa-crown"></i>
                    <span>Admin Panel</span>
                </a>
            </div>
            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Request::is('admin') || Request::is('admin/dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ Request::is('admin/users*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ Request::is('admin/products*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ Request::is('admin/categories*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ Request::is('admin/orders*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.attributes.index') }}" class="nav-link {{ Request::is('admin/attributes*') ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                        <span>Attributes</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.sales.index') }}" class="nav-link {{ Request::is('admin/sales*') ? 'active' : '' }}">
                        <i class="fas fa-percent"></i>
                        <span>Sales & Offers</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.stock.dashboard') }}" class="nav-link {{ Request::is('admin/stock*') ? 'active' : '' }}">
                        <i class="fas fa-warehouse"></i>
                        <span>Stock Management</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.email-logs.index') }}" class="nav-link {{ Request::is('admin/email-logs*') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i>
                        <span>Email Logs</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main" id="adminMain">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button type="button" class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="topbar-title">@yield('title', 'Admin Panel')</h1>
                </div>
                <div class="topbar-right">
                    <div class="user-dropdown">
                        <img src="{{ auth()->user()->avatar ?? asset('images/default-avatar.png') }}" 
                             alt="User Avatar" class="user-avatar" 
                             onclick="toggleUserMenu()">
                        <div class="dropdown-menu dropdown-menu-end" id="userDropdown">
                            <a class="dropdown-item" href="{{ route('admin.dashboard.index') }}">
                                <i class="fas fa-user me-2"></i> Profile
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                            <hr class="dropdown-divider">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="admin-content">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');

            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('show');
                } else {
                    sidebar.classList.toggle('collapsed');
                    main.classList.toggle('expanded');
                }
            });

            // Close sidebar on mobile when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('show');
                }
            });
        });

        // User dropdown toggle
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdown');
            const avatar = document.querySelector('.user-avatar');
            
            if (!dropdown.contains(e.target) && !avatar.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Toast notification system
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toastId = 'toast-' + Date.now();
            
            const toastHtml = `
                <div class="toast" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-${type} text-white">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                        <strong class="me-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 5000
            });
            
            toast.show();
            
            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }

        // Global error handler for AJAX requests
        window.addEventListener('unhandledrejection', function(event) {
            console.error('Unhandled promise rejection:', event.reason);
        });
    </script>
    
    @stack('scripts')
</body>
</html>