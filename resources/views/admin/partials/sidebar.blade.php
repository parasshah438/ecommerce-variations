{{-- Admin Sidebar Component --}}
<!-- Sidebar -->
<div class="sidebar-container" id="sidebar">
    <div class="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <div class="brand-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="brand-text">
                    <h5 class="mb-0 fw-bold">AdminPro</h5>
                    <small class="text-muted">Dashboard</small>
                </div>
            </a>
        </div>

        <!-- Sidebar Search -->
        <div class="sidebar-search">
            <div class="search-input-wrapper">
                <input type="text" class="form-control sidebar-search-input" 
                       placeholder="Search menu..." 
                       id="sidebarSearchInput" 
                       autocomplete="off">
                <div class="search-icon">
                    <i class="fas fa-search"></i>
                </div>
                <button class="search-clear d-none" id="searchClear" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Search Results Dropdown -->
            <div class="search-results d-none" id="searchResults">
                <div class="search-results-header">
                    <small class="text-muted">Search Results</small>
                </div>
                <div class="search-results-list" id="searchResultsList">
                    <!-- Results will be populated by JavaScript -->
                </div>
                <div class="no-search-results d-none" id="noSearchResults">
                    <div class="text-center py-3">
                        <i class="fas fa-search text-muted mb-2"></i>
                        <p class="mb-0 text-muted small">No menu items found</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}" 
                       href="{{ route('admin.dashboard') }}"
                       data-keywords="home overview main stats metrics">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="#" 
                       data-bs-toggle="collapse" 
                       data-bs-target="#analyticsMenu"
                       data-keywords="charts graphs data visualization metrics stats">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <span class="nav-text">Analytics</span>
                        <i class="fas fa-chevron-right ms-auto nav-arrow"></i>
                    </a>
                    <div class="collapse" id="analyticsMenu">
                        <div class="nav-sub-menu">
                            <a class="nav-link" href="#" data-keywords="reports export data analysis">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <span class="nav-text">Reports</span>
                            </a>
                            <a class="nav-link" href="#" data-keywords="statistics numbers metrics data">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <span class="nav-text">Statistics</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">E-Commerce</div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}" 
                       href="{{ route('admin.products.index') }}"
                       data-keywords="items inventory catalog goods merchandise stock">
                        <i class="nav-icon fas fa-box"></i>
                        <span class="nav-text">Products</span>
                        <span class="nav-badge">25</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}" 
                       href="{{ route('admin.categories.index') }}"
                       data-keywords="groups classification taxonomy organize sections">
                        <i class="nav-icon fas fa-folder-open"></i>
                        <span class="nav-text">Categories</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.attributes*') ? 'active' : '' }}" 
                       href="{{ route('admin.attributes.index') }}"
                       data-keywords="properties features specifications tags labels">
                        <i class="nav-icon fas fa-tags"></i>
                        <span class="nav-text">Attributes</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.attribute-values*') ? 'active' : '' }}" 
                       href="{{ route('admin.attribute-values.index') }}"
                       data-keywords="options values choices variants properties specifications">
                        <i class="nav-icon fas fa-list"></i>
                        <span class="nav-text">Attribute Values</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="#" data-keywords="purchases sales transactions checkout cart">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <span class="nav-text">Orders</span>
                        <span class="nav-badge">12</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">User Management</div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" 
                       href="{{ route('admin.users.index') }}" 
                       data-keywords="users management members customers clients admin">
                        <i class="nav-icon fas fa-users"></i>
                        <span class="nav-text">All Users</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.user-activities*') ? 'active' : '' }}" 
                       href="{{ route('admin.user-activities.index') }}" 
                       data-keywords="activity logs history tracking audit">
                        <i class="nav-icon fas fa-history"></i>
                        <span class="nav-text">User Activities</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Reports & Analytics</div>
                <div class="nav-item">
                    <a class="nav-link" href="#" data-keywords="sales revenue profit income financial reports">
                        <i class="nav-icon fas fa-chart-area"></i>
                        <span class="nav-text">Sales Reports</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="#" data-keywords="inventory stock levels warehouse products">
                        <i class="nav-icon fas fa-boxes"></i>
                        <span class="nav-text">Inventory Report</span>
                        <span class="nav-badge">New</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="#" data-keywords="customers users behavior analytics demographics">
                        <i class="nav-icon fas fa-user-chart"></i>
                        <span class="nav-text">Customer Analytics</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">System</div>
                <div class="nav-item">
                    <a class="nav-link" href="#" data-keywords="configuration options preferences setup config">
                        <i class="nav-icon fas fa-cog"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="#" data-keywords="protection safety authentication permissions access">
                        <i class="nav-icon fas fa-shield-alt"></i>
                        <span class="nav-text">Security</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="#" data-keywords="backup restore database export import">
                        <i class="nav-icon fas fa-database"></i>
                        <span class="nav-text">Database Backup</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>