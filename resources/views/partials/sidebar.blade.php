{{-- User Sidebar Component --}}
<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon">{{ strtoupper(substr(config('app.name', 'E'), 0, 1)) }}</div>
        <div class="logo-text">
            <h5>{{ config('app.name', 'ECommerce') }}</h5>
            <small>User Dashboard</small>
        </div>
    </div>
    
    <!-- Sidebar Search -->
    <div class="sidebar-search">
        <div class="search-input-wrapper">
            <input type="text" class="form-control sidebar-search-input" 
                   placeholder="Search menu..." 
                   id="sidebarSearchInput" 
                   autocomplete="off">
            <div class="search-icon">
                <i class="bi bi-search"></i>
            </div>
            <button class="search-clear d-none" id="searchClear" type="button">
                <i class="bi bi-x"></i>
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
                    <i class="bi bi-search text-muted mb-2"></i>
                    <p class="mb-0 text-muted small">No menu items found</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="nav-section">
        <div class="nav-section-title">Main</div>
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
           href="{{ route('dashboard') }}"
           data-keywords="home overview main stats metrics">
            <i class="bi bi-speedometer2"></i>Dashboard
        </a>
        <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" 
           href=""
           data-keywords="profile account settings personal information user">
            <i class="bi bi-person-circle"></i>My Profile
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Account Management</div>
        <a class="nav-link {{ request()->routeIs('profile.manage') ? 'active' : '' }}" 
           href="{{ route('profile.manage') }}"
           data-keywords="manage profile edit update personal information details">
            <i class="bi bi-person-gear text-primary"></i>Manage Profile
        </a>
        
        <a class="nav-link {{ request()->routeIs('profile.password') ? 'active' : '' }}" 
           href="{{ route('profile.password') }}"
           data-keywords="change password security update credentials">
            <i class="bi bi-shield-lock text-warning"></i>Change Password
        </a>
        
        <a class="nav-link text-danger" 
           href="#" 
           onclick="showDeleteAccountModal()"
           data-keywords="delete account remove close terminate">
            <i class="bi bi-person-x text-danger"></i>Delete Account
            <i class="bi bi-exclamation-triangle ms-auto text-danger" style="font-size: 0.75rem;" title="Permanent action"></i>
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Shopping</div>
        <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" 
           href="{{ route('orders.index') }}"
           data-keywords="orders purchases history transactions buying shopping">
            <i class="bi bi-bag-check"></i>My Orders
            @if(auth()->user()->orders()->where('status', 'pending')->count() > 0)
                <span class="badge bg-warning rounded-pill ms-auto">{{ auth()->user()->orders()->where('status', 'pending')->count() }}</span>
            @endif
        </a>
        <a class="nav-link {{ request()->routeIs('wishlist.*') ? 'active' : '' }}" 
           href="{{ route('wishlist.index') }}"
           data-keywords="wishlist favorites saved items heart love">
            <i class="bi bi-heart-fill text-danger"></i>Wishlist
            @php
                $wishlistCount = auth()->user()->wishlist()->count();
            @endphp
            @if($wishlistCount > 0)
                <span class="badge bg-danger rounded-pill ms-auto">{{ $wishlistCount }}</span>
            @endif
        </a>
        <a class="nav-link {{ request()->routeIs('recent-views.*') ? 'active' : '' }}" 
           href="{{ route('recent-views.index') }}"
           data-keywords="recent viewed history browse products items">
            <i class="bi bi-clock-history"></i>Recent Views
            @php
                try {
                    $recentViewsCount = \App\Models\RecentView::where('user_id', auth()->id())->count();
                } catch (\Exception $e) {
                    $recentViewsCount = 0;
                }
            @endphp
            @if($recentViewsCount > 0)
                <span class="badge bg-info rounded-pill ms-auto">{{ $recentViewsCount }}</span>
            @endif
        </a>
        <a class="nav-link {{ request()->routeIs('cart.*') ? 'active' : '' }}" 
           href="{{ route('cart.index') }}"
           data-keywords="cart shopping basket checkout buy purchase">
            <i class="bi bi-cart3"></i>Shopping Cart
            @if(session('cart') && count(session('cart')) > 0)
                <span class="badge bg-primary rounded-pill ms-auto">{{ count(session('cart')) }}</span>
            @endif
        </a>
        <a class="nav-link {{ request()->routeIs('addresses.*') ? 'active' : '' }}" 
           href="{{ route('addresses.index') }}"
           data-keywords="addresses shipping delivery location home work">
            <i class="bi bi-geo-alt"></i>Addresses
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Support</div>
        <a class="nav-link {{ request()->routeIs('support.*') ? 'active' : '' }}" 
           href=""
           data-keywords="help support assistance customer service contact faq">
            <i class="bi bi-headset"></i>Help Center
        </a>
        <a class="nav-link" href="{{ route('home') }}" target="_blank"
           data-keywords="store shop products browse catalog buy">
            <i class="bi bi-shop"></i>Visit Store
            <i class="bi bi-box-arrow-up-right ms-auto" style="font-size: 0.75rem;"></i>
        </a>
    </div>
</nav>

<!-- Delete Account Confirmation Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold" id="deleteAccountModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Delete Account Permanently
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Warning Icon -->
                <div class="text-center mb-4">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-person-x-fill text-danger" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                
                <!-- Warning Message -->
                <div class="text-center mb-4">
                    <h5 class="text-danger fw-bold mb-3">This action cannot be undone!</h5>
                    <p class="text-muted mb-0">
                        Deleting your account will permanently remove:
                    </p>
                </div>
                
                <!-- What will be deleted -->
                <div class="bg-light rounded-3 p-3 mb-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-circle text-danger me-3"></i>
                                <div>
                                    <strong>Personal Information</strong>
                                    <div class="small text-muted">Name, email, phone, and profile data</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-bag-check text-danger me-3"></i>
                                <div>
                                    <strong>Order History</strong>
                                    <div class="small text-muted">All past orders and transaction records</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-heart-fill text-danger me-3"></i>
                                <div>
                                    <strong>Wishlist & Preferences</strong>
                                    <div class="small text-muted">Saved items and personalized settings</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt-fill text-danger me-3"></i>
                                <div>
                                    <strong>Saved Addresses</strong>
                                    <div class="small text-muted">Delivery and billing addresses</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Confirmation Input -->
                <div class="mb-3">
                    <label for="confirmDeleteText" class="form-label fw-semibold">
                        Type "DELETE" to confirm:
                    </label>
                    <input type="text" class="form-control form-control-lg" id="confirmDeleteText" 
                           placeholder="Type DELETE here" maxlength="6" autocomplete="off">
                    <div class="form-text text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        This confirms you understand the consequences
                    </div>
                </div>
                
                <!-- Final Warning -->
                <div class="alert alert-danger d-flex align-items-center mb-0">
                    <i class="bi bi-shield-exclamation me-2 flex-shrink-0"></i>
                    <div class="small">
                        <strong>Security Note:</strong> You will be immediately logged out and won't be able to recover this account or its data.
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn" disabled onclick="deleteAccount()">
                    <i class="bi bi-trash3-fill me-1"></i>Delete My Account
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Handle delete account modal
function showDeleteAccountModal() {
    const modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
    modal.show();
    
    // Reset form
    document.getElementById('confirmDeleteText').value = '';
    document.getElementById('confirmDeleteBtn').disabled = true;
}

// Enable delete button when correct text is typed
document.addEventListener('DOMContentLoaded', function() {
    const confirmText = document.getElementById('confirmDeleteText');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    if (confirmText && confirmBtn) {
        confirmText.addEventListener('input', function() {
            const isValid = this.value.trim().toUpperCase() === 'DELETE';
            confirmBtn.disabled = !isValid;
            
            // Add visual feedback
            if (isValid) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else if (this.value.length > 0) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
    }
});

// Handle account deletion
function deleteAccount() {
    const confirmText = document.getElementById('confirmDeleteText').value.trim().toUpperCase();
    
    if (confirmText !== 'DELETE') {
        alert('Please type "DELETE" to confirm account deletion.');
        return;
    }
    
    // Show loading state
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const originalText = deleteBtn.innerHTML;
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    
    // Make AJAX request to delete account
    fetch('{{ route("profile.delete") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            confirmation: 'DELETE'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message and redirect
            alert('Your account has been successfully deleted. You will now be logged out.');
            window.location.href = '{{ route("home") ?? "/" }}';
        } else {
            throw new Error(data.message || 'Failed to delete account');
        }
    })
    .catch(error => {
        console.error('Delete account error:', error);
        alert('An error occurred while deleting your account: ' + error.message);
        
        // Reset button
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
    });
}

// Enhanced sidebar functionality for responsive behavior
document.addEventListener('DOMContentLoaded', function() {
    // Handle responsive sidebar collapse on mobile
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.querySelector('[data-bs-toggle="sidebar"]');
    
    // Add mobile responsiveness
    if (window.innerWidth <= 768) {
        sidebar?.classList.add('sidebar-collapsed');
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            sidebar?.classList.add('sidebar-mobile');
        } else {
            sidebar?.classList.remove('sidebar-mobile', 'sidebar-collapsed');
        }
    });
    
    // Improve navigation active states
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Add loading state for navigation
            if (this.href && this.href !== '#' && !this.hasAttribute('onclick')) {
                const icon = this.querySelector('i');
                if (icon && !icon.classList.contains('spinner-border')) {
                    const originalClass = icon.className;
                    icon.className = 'spinner-border spinner-border-sm';
                    
                    // Reset after navigation or timeout
                    setTimeout(() => {
                        icon.className = originalClass;
                    }, 2000);
                }
            }
        });
    });
    
    // Enhanced search functionality
    const searchInput = document.getElementById('sidebarSearchInput');
    const searchResults = document.getElementById('searchResults');
    const searchResultsList = document.getElementById('searchResultsList');
    const noSearchResults = document.getElementById('noSearchResults');
    const searchClear = document.getElementById('searchClear');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (query.length > 0) {
                searchClear?.classList.remove('d-none');
                performSearch(query);
            } else {
                searchClear?.classList.add('d-none');
                searchResults?.classList.add('d-none');
            }
        });
        
        searchClear?.addEventListener('click', function() {
            searchInput.value = '';
            this.classList.add('d-none');
            searchResults?.classList.add('d-none');
        });
    }
    
    function performSearch(query) {
        const navLinks = document.querySelectorAll('.nav-link[data-keywords]');
        const results = [];
        
        navLinks.forEach(link => {
            const keywords = link.getAttribute('data-keywords');
            const text = link.textContent.toLowerCase();
            
            if (text.includes(query) || keywords.includes(query)) {
                results.push({
                    text: link.textContent.trim(),
                    href: link.href,
                    icon: link.querySelector('i')?.className || 'bi bi-arrow-right'
                });
            }
        });
        
        displaySearchResults(results);
    }
    
    function displaySearchResults(results) {
        if (results.length > 0) {
            searchResultsList.innerHTML = results.map(result => `
                <a href="${result.href}" class="search-result-item">
                    <i class="${result.icon}"></i>
                    ${result.text}
                </a>
            `).join('');
            noSearchResults?.classList.add('d-none');
        } else {
            searchResultsList.innerHTML = '';
            noSearchResults?.classList.remove('d-none');
        }
        
        searchResults?.classList.remove('d-none');
    }
});
</script>

<style>
/* Enhanced sidebar styles for professional appearance */
.sidebar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.sidebar .nav-section-title {
    color: rgba(255, 255, 255, 0.8);
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    padding: 0.5rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar .nav-link {
    color: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    margin: 0.2rem 0.8rem;
    padding: 0.8rem 1rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
}

.sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    transform: translateX(5px);
}

.sidebar .nav-link.active {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    box-shadow: inset 3px 0 0 #fff;
}

.sidebar .nav-link i {
    width: 20px;
    margin-right: 0.8rem;
    flex-shrink: 0;
}

.sidebar .nav-link .badge {
    margin-left: auto;
}

/* Delete account modal enhancements */
.modal-content {
    border-radius: 15px;
}

.modal-header.bg-danger {
    border-radius: 15px 15px 0 0;
}

#confirmDeleteText.is-valid {
    border-color: #dc3545;
    background-color: rgba(220, 53, 69, 0.1);
}

#confirmDeleteText.is-invalid {
    border-color: #ffc107;
    background-color: rgba(255, 193, 7, 0.1);
}

/* Search results styling */
.search-results {
    background: white;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 8px;
    margin-top: 0.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.search-result-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #333;
    text-decoration: none;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    transition: background-color 0.2s ease;
}

.search-result-item:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
}

.search-result-item i {
    width: 20px;
    margin-right: 0.8rem;
    font-size: 0.9rem;
}

/* Responsive enhancements */
@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1050;
        width: 280px;
        height: 100vh;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform: translateX(-100%);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .sidebar-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .sidebar-backdrop.active {
        opacity: 1;
        visibility: visible;
    }
}

/* Professional badge styling */
.badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

/* Loading states */
.nav-link .spinner-border-sm {
    width: 0.9rem;
    height: 0.9rem;
}

/* Enhanced modal animations */
.modal.fade .modal-dialog {
    transform: translateY(-30px) scale(0.95);
    transition: transform 0.3s ease;
}

.modal.show .modal-dialog {
    transform: translateY(0) scale(1);
}
</style>