<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Admin CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn {
            border-radius: 8px;
        }
        .form-control, .form-select {
            border-radius: 8px;
        }
        .variation-item {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        /* Pagination Design - Clean and Consistent */
        .pagination {
            margin: 0;
            gap: 2px;
        }
        
        .pagination .page-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.2s ease;
            min-width: 40px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .pagination .page-link:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
            box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);
        }
        
        .pagination .page-item.disabled .page-link {
            color: #adb5bd;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
            transform: none;
        }
        
        .pagination .page-item.disabled .page-link:hover {
            background-color: #fff;
            transform: none;
            box-shadow: none;
        }
        
        /* Previous/Next arrow specific styling */
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            font-weight: 500;
        }
        
        /* Bootstrap Icons in pagination */
        .pagination .page-link .bi {
            font-size: 12px;
            line-height: 1;
        }
        
        /* Ensure proper spacing for icons */
        .pagination .page-link .bi-chevron-left,
        .pagination .page-link .bi-chevron-right {
            font-size: 11px;
            font-weight: bold;
        }
        
        /* Responsive pagination */
        @media (max-width: 768px) {
            .pagination .page-link {
                padding: 0.375rem 0.5rem;
                font-size: 0.8rem;
                min-width: 35px;
                height: 35px;
            }
        }
        
        .variation-item:hover {
            border-color: #0d6efd;
            background: #f8f9ff;
        }
        .image-preview {
            position: relative;
            display: inline-block;
        }
        .image-preview img {
            border-radius: 8px;
        }
        .image-preview .remove-image {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-2">
                <div class="sidebar p-3">
                    <div class="d-flex align-items-center mb-4">
                        <i class="bi bi-shop text-white fs-3 me-2"></i>
                        <h5 class="text-white mb-0">Admin Panel</h5>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2 me-2"></i>
                            Dashboard
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                            <i class="bi bi-box-seam me-2"></i>
                            Products
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                            <i class="bi bi-folder me-2"></i>
                            Categories
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.attributes*') ? 'active' : '' }}" href="{{ route('admin.attributes.index') }}">
                            <i class="bi bi-tags me-2"></i>
                            Attributes
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.attribute-values*') ? 'active' : '' }}" href="{{ route('admin.attribute-values.index') }}">
                            <i class="bi bi-list-ul me-2"></i>
                            Attribute Values
                        </a>
                        <a class="nav-link" href="#">
                            <i class="bi bi-people me-2"></i>
                            Customers
                        </a>
                        <a class="nav-link" href="#">
                            <i class="bi bi-cart3 me-2"></i>
                            Orders
                        </a>
                        <a class="nav-link" href="#">
                            <i class="bi bi-graph-up me-2"></i>
                            Analytics
                        </a>
                        <a class="nav-link" href="#">
                            <i class="bi bi-gear me-2"></i>
                            Settings
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10">
                <div class="main-content p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-1">@yield('page-title', 'Dashboard')</h2>
                            <p class="text-muted mb-0">@yield('page-description', 'Welcome to admin panel')</p>
                        </div>
                        <div>
                            <button class="btn btn-outline-secondary me-2">
                                <i class="bi bi-bell"></i>
                            </button>
                            <button class="btn btn-primary">
                                <i class="bi bi-person-circle me-1"></i>
                                Admin
                            </button>
                        </div>
                    </div>
                    
                    <!-- Alerts -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <!-- Main Content -->
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>
