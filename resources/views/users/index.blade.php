@extends('layouts.admin_layout')

@section('title', 'User Management')

@push('styles')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<style>
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-radius: 0.5rem;
}
.stats-card {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #007bff;
    transition: transform 0.3s ease;
}
.stats-card:hover {
    transform: translateY(-5px);
}
.table-container {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.action-buttons .btn {
    margin: 0 2px;
    padding: 0.25rem 0.5rem;
}
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}
.status-active { background: #d4edda; color: #155724; }
.status-inactive { background: #f8d7da; color: #721c24; }
.status-suspended { background: #fff3cd; color: #856404; }
.role-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}
.role-admin { background: #e3f2fd; color: #1976d2; }
.role-manager { background: #f3e5f5; color: #7b1fa2; }
.role-user { background: #e8f5e8; color: #388e3c; }
.modal-header {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}
.form-group label {
    font-weight: 600;
    color: #495057;
}
.btn-group-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}
@media (max-width: 768px) {
    .btn-group-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .btn-group-actions .btn {
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header text-center">
        <h1 class="mb-2">
            <i class="fas fa-users"></i>
            User Management
        </h1>
        <p class="mb-0">Manage all users, roles, and permissions efficiently</p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h4 class="mb-0" id="totalUsers">0</h4>
                        <small class="text-muted">Total Users</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-user-check fa-2x text-success"></i>
                    </div>
                    <div>
                        <h4 class="mb-0" id="activeUsers">0</h4>
                        <small class="text-muted">Active Users</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-user-times fa-2x text-warning"></i>
                    </div>
                    <div>
                        <h4 class="mb-0" id="inactiveUsers">0</h4>
                        <small class="text-muted">Inactive Users</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-crown fa-2x text-info"></i>
                    </div>
                    <div>
                        <h4 class="mb-0" id="adminUsers">0</h4>
                        <small class="text-muted">Administrators</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="table-container">
                <!-- Table Header Controls -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h4 class="mb-0">
                            <i class="fas fa-list"></i> Users List
                        </h4>
                    </div>
                    <div class="col-md-6">
                        <div class="btn-group-actions justify-content-end d-flex">
                            <button type="button" class="btn btn-outline-secondary" onclick="UserManager.reloadTable();">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-primary" id="addUserBtn">
                                <i class="fas fa-plus"></i> Add User
                            </button>
                            <button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display: none;">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filters Row -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="statusFilter">Filter by Status</label>
                            <select class="form-control" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="roleFilter">Filter by Role</label>
                            <select class="form-control" id="roleFilter">
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="manager">Manager</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="searchInput">Search Users</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by name, email, or mobile...">
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- User View Modal -->
<div class="modal fade" id="userViewModal" tabindex="-1" aria-labelledby="userViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userViewModalLabel">
                    <i class="fas fa-user-circle"></i> User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userViewContent">
                <!-- User details will be loaded here -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading user details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="editFromViewBtn">
                    <i class="fas fa-edit"></i> Edit User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- User Add/Edit Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="userForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">
                        <i class="fas fa-user-plus"></i> Add New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="alertContainer"></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userName" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="userName" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="userEmail" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userMobile" class="form-label">Mobile Number</label>
                                <input type="text" class="form-control" id="userMobile" name="mobile_number">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userCountryCode" class="form-label">Country Code</label>
                                <input type="text" class="form-control" id="userCountryCode" name="country_code" placeholder="+1">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userRole" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-control" id="userRole" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="manager">Manager</option>
                                    <option value="user">User</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-control" id="userStatus" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="passwordFields">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userPassword" class="form-label">Password <span class="text-danger" id="passwordRequired">*</span></label>
                                <input type="password" class="form-control" id="userPassword" name="password">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userPasswordConfirmation" class="form-label">Confirm Password <span class="text-danger" id="confirmPasswordRequired">*</span></label>
                                <input type="password" class="form-control" id="userPasswordConfirmation" name="password_confirmation">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userDateOfBirth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="userDateOfBirth" name="date_of_birth">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userAvatar" class="form-label">Profile Image</label>
                                <input type="file" class="form-control" id="userAvatar" name="avatar" accept="image/*">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Supported formats: JPG, PNG, GIF. Max size: 2MB</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="userAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="userAddress" name="address" rows="2"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userCity" class="form-label">City</label>
                                <input type="text" class="form-control" id="userCity" name="city">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userCountry" class="form-label">Country</label>
                                <input type="text" class="form-control" id="userCountry" name="country">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="userBio" class="form-label">Bio</label>
                        <textarea class="form-control" id="userBio" name="bio" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveUserBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <i class="fas fa-save"></i> Save User
                    </button>
                </div>
                <input type="hidden" id="userId" name="id">
            </form>
        </div>
    </div>
</div>    

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
// User Management System
const UserManager = {
    table: null,
    isEditing: false,
    editUserId: null,
    
    init() {
        this.initDataTable();
        this.bindEvents();
        this.updateStats();
        this.setupToastr();
    },

    setupToastr() {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
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
    },

    initDataTable() {
        this.table = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('users.manage') }}",
                type: "GET",
                error: function(xhr, error, thrown) {
                    console.error('DataTable Error:', error, thrown);
                    toastr.error('Failed to load users data. Please try again.');
                }
            },
            columns: [
                { 
                    data: 'checkbox', 
                    orderable: false, 
                    searchable: false,
                    width: '30px'
                },
                { data: 'id', width: '50px' },
                { 
                    data: 'name', 
                    render: function(data, type, row) {
                        const avatar = row.avatar ? row.avatar : '/images/default-avatar.png';
                        return `
                            <div class="d-flex align-items-center">
                                <img src="${avatar}" alt="${data}" class="user-avatar me-2" 
                                     onerror="this.src='/images/default-avatar.png'">
                                <div>
                                    <div class="fw-bold">${data}</div>
                                    <small class="text-muted">ID: ${row.id}</small>
                                </div>
                            </div>
                        `;
                    }
                },
                { data: 'email' },
                { 
                    data: 'phone', 
                    render: function(data) {
                        return data || '-';
                    }
                },
                { 
                    data: 'role',
                    render: function(data) {
                        const roleClass = `role-${data.toLowerCase()}`;
                        return `<span class="role-badge ${roleClass}">${data}</span>`;
                    }
                },
                { 
                    data: 'status',
                    render: function(data) {
                        const statusClass = `status-${data.toLowerCase()}`;
                        return `<span class="status-badge ${statusClass}">${data}</span>`;
                    }
                },
                { data: 'created_at' },
                { 
                    data: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        return UserManager.generateActionButtons(row);
                    }
                }
            ],
            order: [[7, 'desc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip',
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
                emptyTable: "No users found",
                zeroRecords: "No matching users found"
            },
            drawCallback: function() {
                UserManager.updateCheckboxes();
            }
        });
    },

    generateActionButtons(row) {
        return `
            <div class="action-buttons">
                <button type="button" class="btn btn-outline-info btn-sm" 
                        onclick="UserManager.viewUser(${row.id})" title="View User">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" 
                        onclick="UserManager.editUser(${row.id})" title="Edit User">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-outline-${row.status === 'active' ? 'warning' : 'success'} btn-sm" 
                        onclick="UserManager.toggleStatus(${row.id})" 
                        title="${row.status === 'active' ? 'Deactivate' : 'Activate'} User">
                    <i class="fas fa-${row.status === 'active' ? 'pause' : 'play'}"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" 
                        onclick="UserManager.deleteUser(${row.id})" title="Delete User">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    },

    bindEvents() {
        // Add User Button
        $('#addUserBtn').on('click', () => this.showAddModal());
        
        // User Form Submit
        $('#userForm').on('submit', (e) => this.handleSubmit(e));
        
        // Select All Checkbox
        $('#selectAll').on('change', (e) => this.handleSelectAll(e));
        
        // Bulk Delete Button
        $('#bulkDeleteBtn').on('click', () => this.bulkDelete());
        
        // Filter Events
        $('#statusFilter, #roleFilter').on('change', () => this.applyFilters());
        $('#searchInput').on('keyup', this.debounce(() => this.applyFilters(), 500));
        
        // Edit from view modal
        $('#editFromViewBtn').on('click', () => this.editFromView());
    },

    debounce(func, delay) {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    },

    applyFilters() {
        const status = $('#statusFilter').val();
        const role = $('#roleFilter').val();
        const search = $('#searchInput').val();
        
        // Update DataTable URL with filters
        const url = new URL(this.table.ajax.url());
        url.searchParams.set('status', status);
        url.searchParams.set('role', role);
        url.searchParams.set('search', search);
        
        this.table.ajax.url(url.toString()).load();
    },

    showAddModal() {
        this.isEditing = false;
        this.editUserId = null;
        this.resetForm();
        $('#userModalLabel').html('<i class="fas fa-user-plus"></i> Add New User');
        $('#saveUserBtn').html('<i class="fas fa-save"></i> Save User');
        $('#passwordRequired, #confirmPasswordRequired').show();
        $('#userPassword, #userPasswordConfirmation').prop('required', true);
        
        const modal = new bootstrap.Modal(document.getElementById('userModal'));
        modal.show();
    },

    async editUser(userId) {
        try {
            this.showLoader();
            
            const response = await fetch(`{{ url('view_user') }}?id=${userId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            const result = await response.json();
            
            if (result.success && result.data) {
                const user = result.data;
                this.isEditing = true;
                this.editUserId = userId;
                this.populateForm(user);
                
                $('#userModalLabel').html('<i class="fas fa-user-edit"></i> Edit User');
                $('#saveUserBtn').html('<i class="fas fa-save"></i> Update User');
                $('#passwordRequired, #confirmPasswordRequired').hide();
                $('#userPassword, #userPasswordConfirmation').prop('required', false);
                
                const modal = new bootstrap.Modal(document.getElementById('userModal'));
                modal.show();
            } else {
                throw new Error(result.message || 'Failed to load user data');
            }
        } catch (error) {
            console.error('Error loading user:', error);
            toastr.error('Failed to load user data: ' + error.message);
        } finally {
            this.hideLoader();
        }
    },

    async viewUser(userId) {
        try {
            $('#userViewContent').html(`
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading user details...</p>
                </div>
            `);
            
            const modal = new bootstrap.Modal(document.getElementById('userViewModal'));
            modal.show();
            
            const response = await fetch(`{{ url('view_user') }}?id=${userId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            const result = await response.json();
            
            if (result.success && result.data) {
                this.renderUserView(result.data);
                $('#editFromViewBtn').data('user-id', userId);
            } else {
                throw new Error(result.message || 'Failed to load user data');
            }
        } catch (error) {
            console.error('Error loading user:', error);
            $('#userViewContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Failed to load user data: ${error.message}
                </div>
            `);
        }
    },

    renderUserView(user) {
        const avatar = user.avatar || '/images/default-avatar.png';
        const content = `
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="${avatar}" alt="${user.name}" class="img-fluid rounded-circle mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover;"
                         onerror="this.src='/images/default-avatar.png'">
                    <h4>${user.name}</h4>
                    <p class="text-muted">${user.email}</p>
                    <span class="role-badge role-${user.role.toLowerCase()}">${user.role}</span>
                    <span class="status-badge status-${user.status.toLowerCase()} ms-2">${user.status}</span>
                </div>
                <div class="col-md-8">
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>ID:</strong></div>
                        <div class="col-sm-8">${user.id}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>Mobile:</strong></div>
                        <div class="col-sm-8">${user.mobile_number || user.phone || '-'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>Date of Birth:</strong></div>
                        <div class="col-sm-8">${user.date_of_birth || '-'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>Address:</strong></div>
                        <div class="col-sm-8">${user.address || '-'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>City:</strong></div>
                        <div class="col-sm-8">${user.city || '-'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>Country:</strong></div>
                        <div class="col-sm-8">${user.country || '-'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>Bio:</strong></div>
                        <div class="col-sm-8">${user.bio || '-'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>Joined:</strong></div>
                        <div class="col-sm-8">${user.created_at}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>Last Login:</strong></div>
                        <div class="col-sm-8">${user.last_login_at || 'Never'}</div>
                    </div>
                </div>
            </div>
        `;
        
        $('#userViewContent').html(content);
    },

    editFromView() {
        const userId = $('#editFromViewBtn').data('user-id');
        const viewModal = bootstrap.Modal.getInstance(document.getElementById('userViewModal'));
        viewModal.hide();
        
        setTimeout(() => {
            this.editUser(userId);
        }, 300);
    },

    populateForm(user) {
        $('#userName').val(user.name || '');
        $('#userEmail').val(user.email || '');
        $('#userMobile').val(user.mobile_number || user.phone || '');
        $('#userCountryCode').val(user.country_code || '');
        $('#userRole').val(user.role || '');
        $('#userStatus').val(user.status || '');
        $('#userDateOfBirth').val(user.date_of_birth || '');
        $('#userAddress').val(user.address || '');
        $('#userCity').val(user.city || '');
        $('#userCountry').val(user.country || '');
        $('#userBio').val(user.bio || '');
        $('#userId').val(user.id || '');
    },

    resetForm() {
        $('#userForm')[0].reset();
        $('#userId').val('');
        this.clearValidationErrors();
    },

    clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#alertContainer').empty();
    },

    async handleSubmit(e) {
        e.preventDefault();
        
        const submitBtn = $('#saveUserBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        try {
            spinner.removeClass('d-none');
            submitBtn.prop('disabled', true);
            this.clearValidationErrors();
            
            const formData = new FormData(e.target);
            const url = this.isEditing ? 
                `{{ url('update_user') }}` : 
                `{{ url('add_user') }}`;
            
            if (this.isEditing) {
                formData.append('user_id', this.editUserId);
            }
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('userModal'));
                modal.hide();
                
                toastr.success(result.message || 'User saved successfully');
                this.reloadTable();
                this.updateStats();
            } else {
                if (result.errors) {
                    this.showValidationErrors(result.errors);
                } else {
                    throw new Error(result.message || 'Failed to save user');
                }
            }
        } catch (error) {
            console.error('Error saving user:', error);
            toastr.error('Failed to save user: ' + error.message);
        } finally {
            spinner.addClass('d-none');
            submitBtn.prop('disabled', false);
        }
    },

    showValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = $(`[name="${field}"]`);
            if (input.length) {
                input.addClass('is-invalid');
                input.siblings('.invalid-feedback').text(errors[field][0]);
            }
        });
    },

    async toggleStatus(userId) {
        if (!confirm('Are you sure you want to toggle this user\'s status?')) {
            return;
        }
        
        try {
            const response = await fetch('{{ url("change_user_status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({ id: userId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                toastr.success(result.message || 'User status updated successfully');
                this.reloadTable();
                this.updateStats();
            } else {
                throw new Error(result.message || 'Failed to update user status');
            }
        } catch (error) {
            console.error('Error toggling user status:', error);
            toastr.error('Failed to update user status: ' + error.message);
        }
    },

    async deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            return;
        }
        
        try {
            const response = await fetch('{{ url("delete_user") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({ id: userId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                toastr.success(result.message || 'User deleted successfully');
                this.reloadTable();
                this.updateStats();
            } else {
                throw new Error(result.message || 'Failed to delete user');
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            toastr.error('Failed to delete user: ' + error.message);
        }
    },

    handleSelectAll(e) {
        const checkboxes = $('.user-checkbox');
        checkboxes.prop('checked', e.target.checked);
        this.updateBulkActions();
    },

    updateCheckboxes() {
        $('.user-checkbox').on('change', () => {
            this.updateBulkActions();
        });
    },

    updateBulkActions() {
        const checkedBoxes = $('.user-checkbox:checked');
        const bulkDeleteBtn = $('#bulkDeleteBtn');
        
        if (checkedBoxes.length > 0) {
            bulkDeleteBtn.show();
        } else {
            bulkDeleteBtn.hide();
        }
        
        // Update select all checkbox state
        const allCheckboxes = $('.user-checkbox');
        const selectAll = $('#selectAll');
        
        if (checkedBoxes.length === 0) {
            selectAll.prop('indeterminate', false);
            selectAll.prop('checked', false);
        } else if (checkedBoxes.length === allCheckboxes.length) {
            selectAll.prop('indeterminate', false);
            selectAll.prop('checked', true);
        } else {
            selectAll.prop('indeterminate', true);
        }
    },

    async bulkDelete() {
        const checkedBoxes = $('.user-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            toastr.warning('Please select at least one user to delete');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete ${checkedBoxes.length} selected users? This action cannot be undone.`)) {
            return;
        }
        
        try {
            const userIds = checkedBoxes.map(function() {
                return parseInt(this.value);
            }).get();
            
            const response = await fetch('{{ url("delete_all_user") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({ ids: userIds })
            });
            
            const result = await response.json();
            
            if (result.success) {
                toastr.success(result.message || 'Selected users deleted successfully');
                this.reloadTable();
                this.updateStats();
                $('#selectAll').prop('checked', false);
                $('#bulkDeleteBtn').hide();
            } else {
                throw new Error(result.message || 'Failed to delete selected users');
            }
        } catch (error) {
            console.error('Error bulk deleting users:', error);
            toastr.error('Failed to delete selected users: ' + error.message);
        }
    },

    reloadTable() {
        if (this.table) {
            this.table.ajax.reload(null, false);
        }
    },

    async updateStats() {
        try {
            const response = await fetch('{{ url("user_stats") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            const stats = await response.json();
            
            if (stats.success) {
                $('#totalUsers').text(stats.data.total || 0);
                $('#activeUsers').text(stats.data.active || 0);
                $('#inactiveUsers').text(stats.data.inactive || 0);
                $('#adminUsers').text(stats.data.admin || 0);
            }
        } catch (error) {
            console.error('Error updating stats:', error);
            // Set default values on error
            $('#totalUsers, #activeUsers, #inactiveUsers, #adminUsers').text('0');
        }
    },

    showLoader() {
        // Add a global loader if needed
    },

    hideLoader() {
        // Hide global loader if needed
    }
};

// Initialize when document is ready
$(document).ready(function() {
    UserManager.init();
});

// Make UserManager available globally for onclick handlers
window.UserManager = UserManager;
</script>
@endpush


@endsection