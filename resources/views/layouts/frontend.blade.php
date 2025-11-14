<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name', 'E-Commerce Store'))</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    @yield('styles')
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .product-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .product-image {
            aspect-ratio: 1;
            object-fit: cover;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 8px;
            font-weight: 500;
            padding: 8px 18px;
        }
        
        .footer {
            background: #212529;
            color: #adb5bd;
        }
        
        .footer a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer a:hover {
            color: #fff;
        }
        
        .search-box {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            transition: border-color 0.2s;
        }
        
        .search-box:focus {
            border-color: var(--primary-color);
            box-shadow: none;
        }
        
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.2rem;
            }
            
            .search-box {
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="bi bi-shop me-2"></i>{{ config('app.name', 'E-Store') }}
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('products.index') }}">
                            <i class="bi bi-grid me-1"></i>Products
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-list me-1"></i>Categories
                        </a>
                        <ul class="dropdown-menu">
                            <!-- Add dynamic categories here -->
                            <li><a class="dropdown-item" href="#">Electronics</a></li>
                            <li><a class="dropdown-item" href="#">Clothing</a></li>
                            <li><a class="dropdown-item" href="#">Home & Garden</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('order.track.public') }}">
                            <i class="bi bi-truck me-1"></i>Track Order
                        </a>
                    </li>
                </ul>
                
                <!-- Search Form -->
                <form class="d-flex me-3" style="min-width: 300px;" action="{{ route('products.search') }}">
                    <div class="input-group">
                        <input class="form-control search-box" type="search" name="q" placeholder="Search products..." value="{{ request('q') }}">
                        <button class="btn btn-outline-light" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- User Navigation -->
                <ul class="navbar-nav">
                    <!-- Cart -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                            <i class="bi bi-cart3"></i>
                            <span id="cart-badge" class="cart-badge">0</span>
                        </a>
                    </li>
                    
                    <!-- Wishlist -->
                    @auth
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('wishlist.index') }}" title="My Wishlist">
                            <i class="bi bi-heart"></i>
                            @php $wishlistCount = \App\Models\Wishlist::where('user_id', auth()->id())->count(); @endphp
                            <span class="cart-badge wishlist-badge {{ $wishlistCount > 0 ? '' : 'd-none' }}">{{ $wishlistCount }}</span>
                        </a>
                    </li>
                    @endauth
                    
                    <!-- Authentication -->
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="bi bi-person-plus me-1"></i>Register
                            </a>
                        </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">
                                    <i class="bi bi-bag me-2"></i>My Orders
                                </a></li>
                                <li><a class="dropdown-item" href="#">
                                    <i class="bi bi-person me-2"></i>Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}" 
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    @yield('breadcrumb')

    <!-- Main Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-white">{{ config('app.name', 'E-Store') }}</h5>
                    <p>Your one-stop destination for quality products at amazing prices.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-decoration-none"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-decoration-none"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-decoration-none"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-decoration-none"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-white">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li><a href="{{ route('products.index') }}">Products</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-white">Customer Service</h6>
                    <ul class="list-unstyled">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Returns</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Track Order</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-white">Contact Info</h6>
                    <p><i class="bi bi-envelope me-2"></i>support@example.com</p>
                    <p><i class="bi bi-phone me-2"></i>+1 (555) 123-4567</p>
                    <p><i class="bi bi-geo-alt me-2"></i>123 Business St, City, State 12345</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'E-Store') }}. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span>Payment Methods:</span>
                    <i class="bi bi-credit-card ms-2"></i>
                    <i class="bi bi-paypal ms-1"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- Sale Timer Script -->
    <script src="{{ asset('js/sale-timer.js') }}"></script>
    
    <script>
       
        // Initialize toastr
        if (typeof toastr !== 'undefined') {
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
        }

        // Load cart count on page load
        $(document).ready(function() {
            loadCartCount();
        });

        function loadCartCount() {
            const cartBadge = document.getElementById('cart-badge');
            if (cartBadge) {
                cartBadge.textContent = '{{ session('cart_count', 0) }}';
            }
        }
        
        // Add jQuery easing if not available
        $(document).ready(function() {
            if (typeof $.easing.easeOutQuart === 'undefined') {
                $.extend($.easing, {
                    easeOutQuart: function (x, t, b, c, d) {
                        return -c * ((t=t/d-1)*t*t*t - 1) + b;
                    }
                });
            }
        });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>