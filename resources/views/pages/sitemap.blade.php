<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitemap - Your Company | All Pages & Links</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Complete sitemap of Your Company website. Find all pages, products, policies, and resources organized for easy navigation and better user experience.">
    <meta name="keywords" content="sitemap, website map, navigation, pages, products, policies, help, support">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Your Company">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="Sitemap - Your Company">
    <meta property="og:description" content="Complete sitemap of all pages and sections on our website">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('pages.sitemap') }}">
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --accent-color: #fd7e14;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --dark-color: #212529;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 100px 0 60px;
            position: relative;
            overflow: hidden;
        }
        
        
        .sitemap-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .sitemap-section.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .sitemap-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .sitemap-section h3 {
            color: var(--primary-color);
            border-bottom: 3px solid var(--accent-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .sitemap-section h3::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary-color);
        }
        
        .sitemap-link {
            display: block;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            text-decoration: none;
            color: var(--dark-color);
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .sitemap-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .sitemap-link:hover {
            background: white;
            border-left-color: var(--primary-color);
            transform: translateX(10px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: var(--primary-color);
        }
        
        .sitemap-link:hover::before {
            left: 100%;
        }
        
        .sitemap-link-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .sitemap-link-description {
            font-size: 0.9rem;
            color: var(--secondary-color);
            margin: 0;
            line-height: 1.5;
        }
        
        .sitemap-link:hover .sitemap-link-description {
            color: var(--primary-color);
        }
        
        .auth-required {
            position: relative;
        }
        
        .auth-required::after {
            content: 'Login Required';
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: var(--warning-color);
            color: white;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-weight: bold;
        }
        
        .section-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            opacity: 0.8;
        }
        
        .stats-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: center;
        }
        
        .stats-item {
            text-align: center;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            display: block;
            line-height: 1;
        }
        
        .stats-label {
            font-size: 0.9rem;
            color: var(--secondary-color);
            margin-top: 0.5rem;
        }
        
        .breadcrumb-custom {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }
        
        .breadcrumb-custom .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }
        
        .breadcrumb-custom .breadcrumb-item.active {
            color: white;
        }
        
        .search-sitemap {
            background: white;
            border-radius: 50px;
            padding: 1rem 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            position: sticky;
            top: 100px;
            z-index: 1020;
        }
        
        .search-sitemap input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 1rem;
            background: transparent;
        }
        
        .search-sitemap input::placeholder {
            color: var(--secondary-color);
        }
        
        .floating-element {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .last-updated {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 40px;
            }
            
            .sitemap-section {
                padding: 1.5rem;
            }
            
            .sitemap-link {
                padding: 0.8rem;
            }
            
            .stats-number {
                font-size: 2rem;
            }
        }
        
        .category-main { border-left-color: var(--primary-color) !important; }
        .category-account { border-left-color: var(--success-color) !important; }
        .category-shopping { border-left-color: var(--warning-color) !important; }
        .category-information { border-left-color: var(--info-color) !important; }
        .category-policies { border-left-color: var(--secondary-color) !important; }
        .category-gallery { border-left-color: var(--accent-color) !important; }
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
                        <a class="nav-link" href="{{ route('products.index') }}">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.help') }}">Help & Support</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('pages.sitemap') }}">Sitemap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb breadcrumb-custom">
                            <li class="breadcrumb-item"><a href="{{ route('welcome') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Sitemap</li>
                        </ol>
                    </nav>
                    
                    <h1 class="display-4 fw-bold mb-4 floating-element">
                        <i class="bi bi-diagram-3 me-3"></i>Website Sitemap
                    </h1>
                    <p class="lead mb-4">
                        Explore our complete website structure and easily navigate to any page. 
                        This comprehensive sitemap helps you find exactly what you're looking for.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#main-pages" class="btn btn-light btn-lg">
                            <i class="bi bi-house me-2"></i>Main Pages
                        </a>
                        <a href="#search-sitemap" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-search me-2"></i>Search Pages
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="text-center floating-element">
                        <i class="bi bi-map text-white" style="font-size: 8rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-4">
        <div class="container">
            <div class="stats-section">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stats-item">
                            <span class="stats-number" data-target="{{ collect($routes)->flatten(1)->count() }}">0</span>
                            <p class="stats-label">Total Pages</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stats-item">
                            <span class="stats-number" data-target="{{ count($routes) }}">0</span>
                            <p class="stats-label">Categories</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stats-item">
                            <span class="stats-number" data-target="100">0</span>
                            <p class="stats-label">SEO Score</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stats-item">
                            <span class="stats-number" data-target="24">0</span>
                            <p class="stats-label">Hours Updated</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Last Updated Info -->
    <section class="py-2">
        <div class="container">
            <div class="last-updated">
                <i class="bi bi-calendar-check text-success me-2"></i>
                <strong>Last Updated:</strong> {{ date('F d, Y \a\t g:i A') }}
                <span class="ms-3">
                    <i class="bi bi-arrow-clockwise text-primary me-2"></i>
                    <strong>Auto-updated daily</strong>
                </span>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="py-3">
        <div class="container">
            <div class="search-sitemap" id="search-sitemap">
                <div class="d-flex align-items-center">
                    <i class="bi bi-search text-primary me-3" style="font-size: 1.5rem;"></i>
                    <input type="text" id="sitemapSearch" placeholder="Search pages, categories, or descriptions..." onkeyup="searchSitemap()">
                </div>
            </div>
        </div>
    </section>

    <!-- Sitemap Content -->
    <section class="py-5" id="main-pages">
        <div class="container">
            <div class="row">
                @php
                    $categoryConfig = [
                        'main' => ['title' => 'Main Pages', 'icon' => 'house-fill', 'class' => 'category-main'],
                        'account' => ['title' => 'Account & Authentication', 'icon' => 'person-circle', 'class' => 'category-account'],
                        'shopping' => ['title' => 'Shopping & Orders', 'icon' => 'cart-fill', 'class' => 'category-shopping'],
                        'information' => ['title' => 'Information & Support', 'icon' => 'info-circle-fill', 'class' => 'category-information'],
                        'policies' => ['title' => 'Policies & Legal', 'icon' => 'shield-fill-check', 'class' => 'category-policies'],
                        'gallery' => ['title' => 'Gallery & Media', 'icon' => 'images', 'class' => 'category-gallery'],
                    ];
                @endphp

                @foreach($routes as $categoryKey => $categoryRoutes)
                    <div class="col-lg-6 mb-4">
                        <div class="sitemap-section" data-category="{{ $categoryKey }}">
                            <div class="text-center mb-3">
                                <i class="bi bi-{{ $categoryConfig[$categoryKey]['icon'] }} section-icon"></i>
                            </div>
                            <h3>
                                <i class="bi bi-{{ $categoryConfig[$categoryKey]['icon'] }} me-2"></i>
                                {{ $categoryConfig[$categoryKey]['title'] }}
                            </h3>
                            
                            @foreach($categoryRoutes as $route)
                                <a href="{{ $route['url'] }}" 
                                   class="sitemap-link {{ $categoryConfig[$categoryKey]['class'] }} {{ isset($route['auth']) && $route['auth'] ? 'auth-required' : '' }}"
                                   data-search="{{ strtolower($route['name'] . ' ' . $route['description']) }}">
                                    <div class="sitemap-link-title">
                                        @if($categoryKey === 'main')
                                            <i class="bi bi-house-door"></i>
                                        @elseif($categoryKey === 'account')
                                            <i class="bi bi-person"></i>
                                        @elseif($categoryKey === 'shopping')
                                            <i class="bi bi-bag"></i>
                                        @elseif($categoryKey === 'information')
                                            <i class="bi bi-info-circle"></i>
                                        @elseif($categoryKey === 'policies')
                                            <i class="bi bi-file-earmark-text"></i>
                                        @elseif($categoryKey === 'gallery')
                                            <i class="bi bi-image"></i>
                                        @else
                                            <i class="bi bi-link"></i>
                                        @endif
                                        {{ $route['name'] }}
                                        @if(isset($route['auth']) && $route['auth'])
                                            <i class="bi bi-lock-fill text-warning ms-1" title="Login Required"></i>
                                        @endif
                                    </div>
                                    <p class="sitemap-link-description">{{ $route['description'] }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Additional Resources Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-tools text-primary me-2"></i>
                        Additional Resources
                    </h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="sitemap-section">
                        <div class="text-center mb-3">
                            <i class="bi bi-file-earmark-code section-icon"></i>
                        </div>
                        <h4 class="text-center">XML Sitemap</h4>
                        <p class="text-center text-muted mb-4">
                            Machine-readable sitemap for search engines
                        </p>
                        <div class="text-center">
                            <a href="/sitemap.xml" class="btn btn-outline-primary" target="_blank">
                                <i class="bi bi-download me-2"></i>View XML Sitemap
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="sitemap-section">
                        <div class="text-center mb-3">
                            <i class="bi bi-robot section-icon"></i>
                        </div>
                        <h4 class="text-center">Robots.txt</h4>
                        <p class="text-center text-muted mb-4">
                            Instructions for search engine crawlers
                        </p>
                        <div class="text-center">
                            <a href="/robots.txt" class="btn btn-outline-secondary" target="_blank">
                                <i class="bi bi-file-text me-2"></i>View Robots.txt
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="sitemap-section">
                        <div class="text-center mb-3">
                            <i class="bi bi-rss section-icon"></i>
                        </div>
                        <h4 class="text-center">RSS Feed</h4>
                        <p class="text-center text-muted mb-4">
                            Stay updated with our latest content
                        </p>
                        <div class="text-center">
                            <a href="/feed" class="btn btn-outline-success" target="_blank">
                                <i class="bi bi-rss-fill me-2"></i>Subscribe to Feed
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SEO Information Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="sitemap-section">
                        <h3 class="text-center">
                            <i class="bi bi-search me-2"></i>SEO & Navigation Benefits
                        </h3>
                        <div class="row text-center">
                            <div class="col-md-4 mb-4">
                                <i class="bi bi-speedometer2 text-primary" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Faster Discovery</h5>
                                <p class="text-muted">Search engines can quickly find and index all pages</p>
                            </div>
                            <div class="col-md-4 mb-4">
                                <i class="bi bi-people text-success" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Better UX</h5>
                                <p class="text-muted">Users can easily navigate and find desired content</p>
                            </div>
                            <div class="col-md-4 mb-4">
                                <i class="bi bi-graph-up-arrow text-info" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">SEO Boost</h5>
                                <p class="text-muted">Improved search engine ranking and visibility</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-info text-center">
                            <i class="bi bi-lightbulb me-2"></i>
                            <strong>Tip:</strong> Bookmark this page to quickly access any section of our website anytime!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-3">
                        <i class="bi bi-shop me-2"></i>Your Company
                    </h5>
                    <p class="text-muted">
                        This sitemap helps you navigate our website efficiently and discover 
                        all the resources and services we offer.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Quick Access</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('welcome') }}" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="{{ route('products.index') }}" class="text-muted text-decoration-none">Products</a></li>
                        <li><a href="{{ route('pages.about') }}" class="text-muted text-decoration-none">About Us</a></li>
                        <li><a href="{{ route('pages.help') }}" class="text-muted text-decoration-none">Help & Support</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Resources</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.sitemap') }}" class="text-muted text-decoration-none">HTML Sitemap</a></li>
                        <li><a href="/sitemap.xml" class="text-muted text-decoration-none">XML Sitemap</a></li>
                        <li><a href="/robots.txt" class="text-muted text-decoration-none">Robots.txt</a></li>
                        <li><a href="/feed" class="text-muted text-decoration-none">RSS Feed</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Sitemap Information</h6>
                    <div class="text-muted">
                        <p><i class="bi bi-calendar me-2"></i>Last Updated: {{ date('M d, Y') }}</p>
                        <p><i class="bi bi-link-45deg me-2"></i>Total Pages: {{ collect($routes)->flatten(1)->count() }}</p>
                        <p><i class="bi bi-check-circle me-2"></i>All Links Verified: Daily</p>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-muted">
                <p>&copy; {{ date('Y') }} Your Company. All rights reserved. | 
                   <a href="{{ route('pages.sitemap') }}" class="text-decoration-none">Sitemap</a> | 
                   <a href="{{ route('pages.privacy') }}" class="text-decoration-none">Privacy</a> | 
                   <a href="{{ route('pages.terms') }}" class="text-decoration-none">Terms</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Search functionality
        function searchSitemap() {
            const searchTerm = document.getElementById('sitemapSearch').value.toLowerCase();
            const sitemapLinks = document.querySelectorAll('.sitemap-link');
            const sections = document.querySelectorAll('.sitemap-section');
            
            if (searchTerm === '') {
                // Show all sections and links
                sections.forEach(section => {
                    section.style.display = 'block';
                    const links = section.querySelectorAll('.sitemap-link');
                    links.forEach(link => link.style.display = 'block');
                });
                return;
            }
            
            // Hide all sections first
            sections.forEach(section => section.style.display = 'none');
            
            // Show matching links and their parent sections
            sitemapLinks.forEach(link => {
                const searchData = link.getAttribute('data-search') || '';
                const parentSection = link.closest('.sitemap-section');
                
                if (searchData.includes(searchTerm)) {
                    link.style.display = 'block';
                    parentSection.style.display = 'block';
                } else {
                    link.style.display = 'none';
                }
            });
            
            // Hide sections with no visible links
            sections.forEach(section => {
                const visibleLinks = section.querySelectorAll('.sitemap-link[style*="block"]');
                if (visibleLinks.length === 0) {
                    section.style.display = 'none';
                }
            });
        }

        // Animate sections on scroll
        function animateSections() {
            const sections = document.querySelectorAll('.sitemap-section');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '50px'
            });

            sections.forEach(section => observer.observe(section));
        }

        // Animate counters
        function animateCounters() {
            const counters = document.querySelectorAll('.stats-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.dataset.target);
                const increment = target / 100;
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    counter.textContent = Math.floor(current);
                }, 20);
            });
        }

        // Highlight current page in sitemap
        function highlightCurrentPage() {
            const currentUrl = window.location.href;
            const sitemapLinks = document.querySelectorAll('.sitemap-link');
            
            sitemapLinks.forEach(link => {
                if (link.href === currentUrl) {
                    link.style.background = 'var(--primary-color)';
                    link.style.color = 'white';
                    link.querySelector('.sitemap-link-description').style.color = 'rgba(255, 255, 255, 0.8)';
                    
                    // Add current page indicator
                    const indicator = document.createElement('span');
                    indicator.innerHTML = '<i class="bi bi-arrow-right-circle-fill ms-2"></i>';
                    indicator.style.color = 'white';
                    link.querySelector('.sitemap-link-title').appendChild(indicator);
                }
            });
        }

        // Add keyboard navigation
        function addKeyboardNavigation() {
            let currentFocus = -1;
            const sitemapLinks = document.querySelectorAll('.sitemap-link');
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentFocus++;
                    if (currentFocus >= sitemapLinks.length) currentFocus = 0;
                    focusLink(currentFocus);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentFocus--;
                    if (currentFocus < 0) currentFocus = sitemapLinks.length - 1;
                    focusLink(currentFocus);
                } else if (e.key === 'Enter' && currentFocus >= 0) {
                    sitemapLinks[currentFocus].click();
                }
            });
            
            function focusLink(index) {
                sitemapLinks.forEach((link, i) => {
                    if (i === index) {
                        link.focus();
                        link.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            }
        }

        // Smooth scrolling for anchor links
        function addSmoothScrolling() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            animateSections();
            animateCounters();
            highlightCurrentPage();
            addKeyboardNavigation();
            addSmoothScrolling();
            
            // Focus search box on Ctrl+F or Cmd+F
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    document.getElementById('sitemapSearch').focus();
                }
            });
            
            // Add tooltips to auth required links
            const authLinks = document.querySelectorAll('.auth-required');
            authLinks.forEach(link => {
                link.title = 'This page requires you to be logged in';
            });
            
            // Add click tracking for analytics (placeholder)
            const allLinks = document.querySelectorAll('.sitemap-link');
            allLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // In a real application, you would send this to your analytics service
                    console.log('Sitemap link clicked:', this.href);
                });
            });
        });

        // Add service worker registration for offline access (optional)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('ServiceWorker registration successful');
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
    
   
</body>
</html>