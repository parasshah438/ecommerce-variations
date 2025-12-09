<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Admin Sidebar CSS -->
    <link href="{{ asset('css/admin-sidebar.css') }}" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            --box-shadow-hover: 0 8px 30px rgba(0,0,0,0.12);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-bs-theme="dark"] {
            --bs-body-bg: #0f172a;
            --bs-body-color: #e2e8f0;
            --bs-border-color: #334155;
            --sidebar-bg: #1e293b;
            --card-bg: #1e293b;
            --primary-gradient: linear-gradient(135deg, #4338ca 0%, #7c3aed 100%);
        }

        [data-bs-theme="light"] {
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) transparent;
        }

        *::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        *::-webkit-scrollbar-track {
            background: transparent;
        }

        *::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bs-body-bg);
            overflow-x: hidden;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 3rem;
            height: 3rem;
        }



        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: var(--transition);
        }

        .sidebar-container.collapsed + .main-wrapper {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Top Header */
        .top-header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--bs-border-color);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: between;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--box-shadow);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--bs-body-color);
            font-size: 1.2rem;
            padding: 0.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .sidebar-toggle:hover {
            background: var(--bs-secondary-bg);
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .breadcrumb-item {
            color: var(--bs-secondary-color);
        }

        .breadcrumb-item.active {
            color: var(--bs-body-color);
            font-weight: 500;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-left: auto;
        }

        .header-action {
            position: relative;
            background: none;
            border: none;
            color: var(--bs-body-color);
            padding: 0.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-action:hover {
            background: var(--bs-secondary-bg);
        }

        .notification-badge {
            position: absolute;
            top: 0.2rem;
            right: 0.2rem;
            background: var(--danger-color);
            color: white;
            font-size: 0.65rem;
            padding: 0.1rem 0.3rem;
            border-radius: 10px;
            min-width: 16px;
            text-align: center;
        }

        .theme-toggle {
            background: var(--bs-secondary-bg);
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
            padding: 0.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .theme-toggle:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .user-dropdown .dropdown-toggle {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .user-dropdown .dropdown-toggle:hover {
            background: var(--bs-secondary-bg);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Main Content Area */
        .main-content {
            padding: 2rem;
            background: var(--bs-body-bg);
            min-height: calc(100vh - 80px);
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--bs-body-color);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--bs-secondary-color);
            font-size: 1rem;
            margin-bottom: 0;
        }

        /* Cards */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: var(--box-shadow-hover);
            transform: translateY(-2px);
        }

        .card-header {
            background: none;
            border-bottom: 1px solid var(--bs-border-color);
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Stats Cards */
        .stats-card {
            background: var(--card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--box-shadow-hover);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stats-icon.primary {
            background: rgba(var(--primary-color), 0.1);
            color: var(--primary-color);
        }

        .stats-icon.success {
            background: rgba(var(--success-color), 0.1);
            color: var(--success-color);
        }

        .stats-icon.warning {
            background: rgba(var(--warning-color), 0.1);
            color: var(--warning-color);
        }

        .stats-icon.danger {
            background: rgba(var(--danger-color), 0.1);
            color: var(--danger-color);
        }

        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--bs-body-color);
            margin-bottom: 0.25rem;
        }

        .stats-label {
            color: var(--bs-secondary-color);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .stats-change {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .stats-change.positive {
            color: var(--success-color);
        }

        .stats-change.negative {
            color: var(--danger-color);
        }

        /* Buttons */
        .btn {
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-primary:hover {
            background: var(--primary-gradient);
            filter: brightness(1.1);
        }

        /* Forms */
        .form-control, .form-select {
            border-radius: var(--border-radius);
            border: 1px solid var(--bs-border-color);
            background: var(--card-bg);
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-color), 0.25);
        }

        /* Alerts */
        .alert {
            border-radius: var(--border-radius);
            border: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: var(--card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        /* DataTables Styling */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--border-radius);
            border: 1px solid var(--bs-border-color);
            background: var(--card-bg);
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: var(--border-radius);
            border: 1px solid var(--bs-border-color);
            background: var(--card-bg);
        }

        /* Pagination */
        .pagination .page-link {
            border-radius: var(--border-radius);
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
            background: var(--card-bg);
            transition: var(--transition);
            margin: 0 0.1rem;
        }

        .pagination .page-link:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-gradient);
            border-color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar-container {
                transform: translateX(-100%);
            }

            .sidebar-container.show {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .main-content {
                padding: 1rem;
            }

            .top-header {
                padding: 1rem;
            }
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-in {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border loading-spinner text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    {{-- Include Sidebar Component --}}
    @include('admin.partials.sidebar')

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <nav class="breadcrumb-nav d-none d-md-flex">
                    <span class="breadcrumb-item">@yield('breadcrumb-section', 'Dashboard')</span>
                    <i class="fas fa-chevron-right mx-2"></i>
                    <span class="breadcrumb-item active">@yield('breadcrumb-page', 'Overview')</span>
                </nav>
            </div>

            <div class="header-right">
                <!-- Search -->
                <button class="header-action d-none d-md-flex" title="Search">
                    <i class="fas fa-search"></i>
                </button>

                <!-- Notifications -->
                <button class="header-action" title="Notifications" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <h6 class="dropdown-header">Notifications</h6>
                    <a class="dropdown-item" href="#">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <i class="fas fa-shopping-cart text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">New Order</div>
                                <div class="small text-muted">Order #1234 received</div>
                            </div>
                        </div>
                    </a>
                    <a class="dropdown-item" href="#">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <i class="fas fa-user text-success"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">New User</div>
                                <div class="small text-muted">User registered</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Messages -->
                <button class="header-action d-none d-md-flex" title="Messages">
                    <i class="fas fa-envelope"></i>
                    <span class="notification-badge">2</span>
                </button>

                <!-- Theme Toggle -->
                <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>

                <!-- User Dropdown -->
                <div class="dropdown user-dropdown">
                    <button class="dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="user-avatar">A</div>
                        <div class="d-none d-md-block">
                            <div class="fw-semibold">Admin User</div>
                            <small class="text-muted">Administrator</small>
                        </div>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header fade-in">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                        <p class="page-subtitle">@yield('page-description', 'Welcome to your admin dashboard')</p>
                    </div>
                    <div class="d-flex gap-2">
                        @yield('page-actions')
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show slide-in" role="alert">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show slide-in" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show slide-in" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Main Content Area -->
            <div class="fade-in">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Theme Management
        class ThemeManager {
            constructor() {
                this.theme = localStorage.getItem('theme') || 'light';
                this.init();
            }

            init() {
                this.setTheme(this.theme);
                this.bindEvents();
            }

            setTheme(theme) {
                this.theme = theme;
                document.documentElement.setAttribute('data-bs-theme', theme);
                localStorage.setItem('theme', theme);
                
                const icon = document.querySelector('#themeToggle i');
                if (icon) {
                    icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                }
            }

            toggle() {
                this.setTheme(this.theme === 'light' ? 'dark' : 'light');
            }

            bindEvents() {
                document.getElementById('themeToggle')?.addEventListener('click', () => {
                    this.toggle();
                });
            }
        }

        // Sidebar Search Management
        class SidebarSearch {
            constructor() {
                this.searchInput = document.getElementById('sidebarSearchInput');
                this.searchResults = document.getElementById('searchResults');
                this.searchResultsList = document.getElementById('searchResultsList');
                this.noResults = document.getElementById('noSearchResults');
                this.clearBtn = document.getElementById('searchClear');
                this.sidebarNav = document.querySelector('.sidebar-nav');
                
                this.searchData = this.buildSearchData();
                this.currentHighlight = -1;
                this.searchTimeout = null;
                
                this.init();
            }

            init() {
                if (!this.searchInput) return;
                
                this.bindEvents();
            }

            buildSearchData() {
                const navItems = document.querySelectorAll('.nav-link');
                const searchData = [];

                navItems.forEach(item => {
                    const icon = item.querySelector('.nav-icon');
                    const text = item.querySelector('.nav-text');
                    const badge = item.querySelector('.nav-badge');
                    const section = item.closest('.nav-section');
                    const sectionTitle = section?.querySelector('.nav-section-title');

                    if (text) {
                        searchData.push({
                            title: text.textContent.trim(),
                            section: sectionTitle ? sectionTitle.textContent.trim() : 'Navigation',
                            icon: icon ? icon.className : 'fas fa-link',
                            href: item.href,
                            badge: badge ? badge.textContent.trim() : null,
                            element: item,
                            sectionElement: section,
                            keywords: [
                                text.textContent.trim(),
                                sectionTitle ? sectionTitle.textContent.trim() : '',
                                item.getAttribute('data-keywords') || ''
                            ].join(' ').toLowerCase()
                        });
                    }
                });

                return searchData;
            }

            bindEvents() {
                // Input events
                this.searchInput.addEventListener('input', (e) => {
                    const query = e.target.value.trim();
                    this.handleSearch(query);
                });

                this.searchInput.addEventListener('keydown', (e) => {
                    this.handleKeyNavigation(e);
                });

                this.searchInput.addEventListener('focus', () => {
                    if (this.searchInput.value.trim()) {
                        this.searchResults.classList.remove('d-none');
                    }
                });

                // Clear button
                this.clearBtn.addEventListener('click', () => {
                    this.clearSearch();
                });

                // Click outside to close
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.sidebar-search')) {
                        this.hideResults();
                    }
                });

                // Result clicks
                this.searchResultsList.addEventListener('click', (e) => {
                    const resultItem = e.target.closest('.search-result-item');
                    if (resultItem) {
                        const href = resultItem.dataset.href;
                        if (href && href !== '#') {
                            window.location.href = href;
                        }
                        this.hideResults();
                    }
                });
            }

            handleSearch(query) {
                clearTimeout(this.searchTimeout);
                
                if (!query) {
                    this.clearSearch();
                    return;
                }

                this.searchTimeout = setTimeout(() => {
                    this.performSearch(query);
                }, 150);

                // Show/hide clear button
                this.clearBtn.classList.toggle('d-none', !query);
            }

            performSearch(query) {
                const results = this.searchData.filter(item => 
                    item.keywords.includes(query.toLowerCase())
                );

                this.displayResults(results, query);
                this.filterSidebarItems(query);
            }

            displayResults(results, query) {
                this.searchResultsList.innerHTML = '';
                this.currentHighlight = -1;

                if (results.length === 0) {
                    this.noResults.classList.remove('d-none');
                    this.searchResults.classList.remove('d-none');
                    return;
                }

                this.noResults.classList.add('d-none');

                results.forEach((item, index) => {
                    const resultElement = document.createElement('div');
                    resultElement.className = 'search-result-item';
                    resultElement.dataset.href = item.href;
                    resultElement.dataset.index = index;

                    const highlightedTitle = this.highlightMatch(item.title, query);
                    
                    resultElement.innerHTML = `
                        <div class="search-result-icon">
                            <i class="${item.icon}"></i>
                        </div>
                        <div class="search-result-content">
                            <div class="search-result-title">${highlightedTitle}</div>
                            <div class="search-result-path">${item.section}</div>
                        </div>
                        ${item.badge ? `<span class="search-result-badge">${item.badge}</span>` : ''}
                    `;

                    this.searchResultsList.appendChild(resultElement);
                });

                this.searchResults.classList.remove('d-none');
            }

            filterSidebarItems(query) {
                // Add search-active class to sidebar
                this.sidebarNav.classList.add('search-active');

                // Hide all sections first
                const sections = document.querySelectorAll('.nav-section');
                sections.forEach(section => {
                    section.classList.add('hidden');
                    section.classList.remove('filtered');
                });

                // Hide all nav items
                const navItems = document.querySelectorAll('.nav-item');
                navItems.forEach(item => {
                    item.classList.add('hidden');
                    item.classList.remove('filtered');
                });

                // Show matching items and their sections
                const matchingItems = this.searchData.filter(item => 
                    item.keywords.includes(query.toLowerCase())
                );

                const visibleSections = new Set();

                matchingItems.forEach(item => {
                    // Show the nav item
                    const navItem = item.element.closest('.nav-item');
                    if (navItem) {
                        navItem.classList.remove('hidden');
                        navItem.classList.add('filtered');
                    }

                    // Show the section
                    if (item.sectionElement) {
                        item.sectionElement.classList.remove('hidden');
                        item.sectionElement.classList.add('filtered');
                        visibleSections.add(item.sectionElement);
                    }
                });
            }

            highlightMatch(text, query) {
                if (!query) return text;
                
                const regex = new RegExp(`(${this.escapeRegExp(query)})`, 'gi');
                return text.replace(regex, '<span class="search-highlight">$1</span>');
            }

            escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            handleKeyNavigation(e) {
                const results = this.searchResultsList.querySelectorAll('.search-result-item');
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.currentHighlight = Math.min(this.currentHighlight + 1, results.length - 1);
                    this.updateHighlight(results);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.currentHighlight = Math.max(this.currentHighlight - 1, -1);
                    this.updateHighlight(results);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (this.currentHighlight >= 0 && results[this.currentHighlight]) {
                        results[this.currentHighlight].click();
                    }
                } else if (e.key === 'Escape') {
                    this.hideResults();
                    this.searchInput.blur();
                }
            }

            updateHighlight(results) {
                results.forEach((item, index) => {
                    item.classList.toggle('highlighted', index === this.currentHighlight);
                });

                // Scroll highlighted item into view
                if (this.currentHighlight >= 0 && results[this.currentHighlight]) {
                    results[this.currentHighlight].scrollIntoView({
                        block: 'nearest',
                        behavior: 'smooth'
                    });
                }
            }

            clearSearch() {
                this.searchInput.value = '';
                this.hideResults();
                this.clearBtn.classList.add('d-none');
                this.resetSidebarFilter();
            }

            hideResults() {
                this.searchResults.classList.add('d-none');
                this.currentHighlight = -1;
            }

            resetSidebarFilter() {
                // Remove search-active class
                this.sidebarNav.classList.remove('search-active');

                // Show all sections and items
                const sections = document.querySelectorAll('.nav-section');
                sections.forEach(section => {
                    section.classList.remove('hidden', 'filtered');
                });

                const navItems = document.querySelectorAll('.nav-item');
                navItems.forEach(item => {
                    item.classList.remove('hidden', 'filtered');
                });
            }

            // Public method to programmatically search
            search(query) {
                this.searchInput.value = query;
                this.handleSearch(query);
                this.searchInput.focus();
            }

            // Public method to add search keywords to nav items
            addKeywords(selector, keywords) {
                const element = document.querySelector(selector);
                if (element) {
                    element.setAttribute('data-keywords', keywords);
                    // Rebuild search data
                    this.searchData = this.buildSearchData();
                }
            }
        }

        // Sidebar Management
        class SidebarManager {
            constructor() {
                this.sidebar = document.getElementById('sidebar');
                this.toggle = document.getElementById('sidebarToggle');
                this.isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                this.init();
            }

            init() {
                if (this.isCollapsed) {
                    this.collapse();
                }
                this.bindEvents();
            }

            collapse() {
                this.sidebar?.classList.add('collapsed');
                this.isCollapsed = true;
                localStorage.setItem('sidebarCollapsed', 'true');
            }

            expand() {
                this.sidebar?.classList.remove('collapsed');
                this.isCollapsed = false;
                localStorage.setItem('sidebarCollapsed', 'false');
            }

            toggleSidebar() {
                if (this.isCollapsed) {
                    this.expand();
                } else {
                    this.collapse();
                }
            }

            bindEvents() {
                this.toggle?.addEventListener('click', () => {
                    this.toggleSidebar();
                });

                // Mobile sidebar toggle
                if (window.innerWidth <= 768) {
                    this.toggle?.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.sidebar?.classList.toggle('show');
                    });
                }
            }
        }

        // Toast Notifications
        class ToastManager {
            constructor() {
                this.container = document.getElementById('toastContainer');
            }

            show(message, type = 'info', duration = 5000) {
                const toast = this.createToast(message, type);
                this.container.appendChild(toast);

                const bsToast = new bootstrap.Toast(toast, { delay: duration });
                bsToast.show();

                toast.addEventListener('hidden.bs.toast', () => {
                    toast.remove();
                });
            }

            createToast(message, type) {
                const icons = {
                    success: 'fa-check-circle text-success',
                    error: 'fa-exclamation-triangle text-danger',
                    warning: 'fa-exclamation-circle text-warning',
                    info: 'fa-info-circle text-info'
                };

                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.innerHTML = `
                    <div class="toast-header">
                        <i class="fas ${icons[type]} me-2"></i>
                        <strong class="me-auto">Notification</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                `;
                return toast;
            }
        }

        // Loading Manager
        class LoadingManager {
            constructor() {
                this.overlay = document.getElementById('loadingOverlay');
            }

            show() {
                this.overlay.style.display = 'flex';
            }

            hide() {
                this.overlay.style.display = 'none';
            }
        }

        // Global instances
        let themeManager, sidebarManager, sidebarSearch, toastManager, loadingManager;

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize managers
            themeManager = new ThemeManager();
            sidebarManager = new SidebarManager();
            sidebarSearch = new SidebarSearch();
            toastManager = new ToastManager();
            loadingManager = new LoadingManager();

            // Initialize DataTables for any table with 'data-table' class
            $('.data-table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'
                    }
                }
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                $('.alert:not(.alert-permanent)').fadeOut();
            }, 5000);

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Hide loading overlay
            setTimeout(() => {
                loadingManager.hide();
            }, 1000);
        });

        // Utility functions
        window.showToast = function(message, type = 'info') {
            toastManager.show(message, type);
        };

        window.showLoading = function() {
            loadingManager.show();
        };

        window.hideLoading = function() {
            loadingManager.hide();
        };

        // CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    {{-- Advanced Form Components --}}
    @include('admin.components.advanced-forms-script')
    
    @stack('scripts')
</body>
</html>
