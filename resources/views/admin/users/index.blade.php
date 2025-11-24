@extends('admin.layout')

@push('styles')
<style>
/* Ecommerce metrics styling */
.dropdown-menu .row .border {
    border: 1px solid #dee2e6 !important;
}

/* Enhanced modal styling for ecommerce overview */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.border-2 {
    border-width: 2px !important;
}

.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
}

#viewUserModal .card {
    transition: all 0.3s ease;
}

#viewUserModal .modal-dialog {
    max-width: 900px;
}

.ecommerce-overview {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border: none !important;
    color: white !important;
}

.ecommerce-overview:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4) !important;
}
</style>
@endpush

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-description', 'Manage system users, roles and permissions')

@push('styles')
<style>
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .btn-group .btn {
        margin: 0 2px;
    }
    
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px;
        padding: 8px 15px;
        border: 1px solid #ddd;
    }
    
    .dataTables_wrapper .dataTables_length select {
        border-radius: 5px;
        padding: 5px 10px;
    }
    
    .stats-cards {
        margin-bottom: 2rem;
    }
    
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .stats-card h3 {
        font-size: 2rem;
        margin: 0.5rem 0;
        font-weight: bold;
    }
    
    .stats-card i {
        font-size: 2.5rem;
        opacity: 0.8;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0;
    }

    .modal-header .btn-close {
        filter: invert(1);
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        transform: translateY(-2px);
    }

    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }

    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    .toolbar {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .action-buttons .btn {
        margin-right: 5px;
    }

    .loading-spinner {
        display: none;
        text-align: center;
        padding: 20px;
    }
</style>
@endpush

@section('page-actions')
<div class="d-flex gap-2">
    <button type="button" class="btn btn-success" id="exportUsersBtn">
        <i class="fas fa-download me-1"></i>Export Users
    </button>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" id="addUserBtn">
        <i class="fas fa-plus me-1"></i>Add New User
    </button>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row stats-cards">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <i class="fas fa-users"></i>
                <h3 id="totalUsers">0</h3>
                <p class="mb-0">Total Users</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <i class="fas fa-user-check"></i>
                <h3 id="activeUsers">0</h3>
                <p class="mb-0">Active Users</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);">
                <i class="fas fa-user-times"></i>
                <h3 id="inactiveUsers">0</h3>
                <p class="mb-0">Inactive Users</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #3742fa 0%, #2f3542 100%);">
                <i class="fas fa-user-shield"></i>
                <h3 id="adminUsers">0</h3>
                <p class="mb-0">Administrators</p>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex gap-2 flex-wrap">
                    <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select class="form-select form-select-sm" id="roleFilter" style="width: auto;">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="user">User</option>
                    </select>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">
                        <i class="fas fa-refresh"></i> Reset
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2 justify-content-md-end action-buttons">
                    <button type="button" class="btn btn-warning btn-sm" id="bulkActivateBtn" disabled>
                        <i class="fas fa-check"></i> Activate Selected
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" id="bulkDeactivateBtn" disabled>
                        <i class="fas fa-pause"></i> Deactivate Selected
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-users me-2"></i>Users List
            </h5>
        </div>
        <div class="card-body">
            <div class="loading-spinner" id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading users...</p>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- User Modal (Add/Edit) -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="userId" name="user_id">
                    <input type="hidden" id="formMethod" name="_method" value="POST">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="userName" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="userName" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="userEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="userEmail" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="userMobile" class="form-label">Mobile Number</label>
                                <div class="input-group">
                                    <select class="form-select" id="countryCode" name="country_code" style="max-width: 100px;">
                                        <option value="+1">+1</option>
                                        <option value="+91" selected>+91</option>
                                        <option value="+44">+44</option>
                                        <option value="+86">+86</option>
                                    </select>
                                    <input type="text" class="form-control" id="userMobile" name="mobile_number">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="userRole" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="userRole" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="user">User</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Administrator</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="userStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="userStatus" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="userBirth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="userBirth" name="date_of_birth">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="passwordSection">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="userPassword" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="userPassword" name="password">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirmPassword" name="password_confirmation">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="userAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="userAddress" name="address" rows="2"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="userCity" class="form-label">City</label>
                                <input type="text" class="form-control" id="userCity" name="city">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="userCountry" class="form-label">Country</label>
                                <input type="text" class="form-control" id="userCountry" name="country">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="userBio" class="form-label">Bio</label>
                        <textarea class="form-control" id="userBio" name="bio" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-1"></i>Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewUserModalLabel">
                    <i class="fas fa-user me-2"></i>User Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- User details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Action
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmMessage">
                <!-- Confirmation message will be set here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmActionBtn">
                    <i class="fas fa-check me-1"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/ecommerce-admin.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize DataTables
    const usersTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.users.data') }}",
            type: 'POST',
            data: function(d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                d.status = $('#statusFilter').val();
                d.role = $('#roleFilter').val();
            }
        },
        columns: [
            { 
                data: null, 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="form-check-input row-checkbox" value="${row.id}">`;
                }
            },
            { data: 'id' },
            { 
                data: 'name',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center">
                            <img src="${row.avatar || '{{ asset('images/default-avatar.png') }}'}" 
                                 class="user-avatar me-2" alt="Avatar">
                            <div>
                                <div class="fw-semibold">${data}</div>
                                <small class="text-muted">ID: ${row.id}</small>
                            </div>
                        </div>
                    `;
                }
            },
            { data: 'email' },
            { data: 'mobile_number' },
            { 
                data: 'role',
                render: function(data, type, row) {
                    const roleColors = {
                        'admin': 'danger',
                        'manager': 'warning',
                        'user': 'primary'
                    };
                    return `<span class="badge bg-${roleColors[data] || 'secondary'}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    const statusClass = data === 'active' ? 'success' : 'secondary';
                    return `<span class="badge bg-${statusClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }
            },
            { data: 'created_at' },
            { 
                data: 'actions', 
                orderable: false, 
                searchable: false 
            }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: 'No users found',
            zeroRecords: 'No matching users found'
        },
        initComplete: function() {
            loadUserStats();
        },
        drawCallback: function() {
            updateBulkActionButtons();
        }
    });

    // Filter handlers
    $('#statusFilter, #roleFilter').on('change', function() {
        usersTable.draw();
    });

    $('#resetFilters').on('click', function() {
        $('#statusFilter, #roleFilter').val('').trigger('change');
    });

    // Load user statistics
    function loadUserStats() {
        $.ajax({
            url: "{{ route('admin.users.data') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                get_stats: true
            },
            success: function(response) {
                if (response.stats) {
                    $('#totalUsers').text(response.stats.total || 0);
                    $('#activeUsers').text(response.stats.active || 0);
                    $('#inactiveUsers').text(response.stats.inactive || 0);
                    $('#adminUsers').text(response.stats.admins || 0);
                }
            }
        });
    }

    // Select all functionality
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulkActionButtons();
    });

    $(document).on('change', '.row-checkbox', function() {
        updateBulkActionButtons();
        
        // Update select all checkbox
        const totalCheckboxes = $('.row-checkbox').length;
        const checkedCheckboxes = $('.row-checkbox:checked').length;
        
        $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
        $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes);
    });

    // Update bulk action buttons
    function updateBulkActionButtons() {
        const selectedCount = $('.row-checkbox:checked').length;
        $('#bulkActivateBtn, #bulkDeactivateBtn, #bulkDeleteBtn').prop('disabled', selectedCount === 0);
    }

    // Add User Modal
    $('#addUserBtn').on('click', function() {
        resetUserForm();
        $('#userModalLabel').html('<i class="fas fa-user-plus me-2"></i>Add New User');
        $('#formMethod').val('POST');
        $('#passwordSection').show();
        $('#userPassword, #confirmPassword').prop('required', true);
    });

    // Edit User
    $(document).on('click', '.edit-user', function() {
        const userId = $(this).data('id');
        
        $.ajax({
            url: `{{ url('admin/users') }}/${userId}/edit`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    populateUserForm(response.user);
                    $('#userModalLabel').html('<i class="fas fa-user-edit me-2"></i>Edit User');
                    $('#formMethod').val('PUT');
                    $('#userId').val(userId);
                    $('#passwordSection').hide();
                    $('#userPassword, #confirmPassword').prop('required', false);
                    $('#userModal').modal('show');
                } else {
                    showToast(response.message || 'Failed to load user data', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('Edit User Error:', xhr.responseJSON);
                let message = 'Error loading user data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 401) {
                    message = 'Authentication required. Please log in again.';
                } else if (xhr.status === 404) {
                    message = 'User not found.';
                }
                showToast(message, 'error');
            }
        });
    });

    // User Form Submit
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const userId = $('#userId').val();
        const isEdit = $('#formMethod').val() === 'PUT';
        
        let url = "{{ route('admin.users.store') }}";
        if (isEdit) {
            url = `{{ url('admin/users') }}/${userId}`;
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#userModal').modal('hide');
                    usersTable.draw();
                    loadUserStats();
                    showToast(response.message, 'success');
                } else {
                    showToast(response.message || 'Operation failed', 'error');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    displayFormErrors(errors);
                } else {
                    showToast('An error occurred', 'error');
                }
            }
        });
    });

    // View User (Eye Icon)
    $(document).on('click', '.view-user', function() {
        const userId = $(this).data('id');
        
        $.ajax({
            url: "{{ route('admin.users.ajax.view') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: userId
            },
            success: function(response) {
                if (response.success) {
                    displayUserDetails(response.user);
                    $('#viewUserModalLabel').html('<i class="fas fa-user me-2"></i>User Details - ' + response.user.name);
                    $('#viewUserModal').modal('show');
                }
            },
            error: function(xhr, status, error) {
                console.error('View User Error:', xhr.responseJSON);
                showToast('Error loading user details: ' + (xhr.responseJSON?.message || error), 'error');
            }
        });
    });

    // Ecommerce Overview Button  
    $(document).on('click', '.ecommerce-overview', function() {
        const userId = $(this).data('id');
        
        // Show loading
        showToast('Loading ecommerce overview...', 'info');
        
        $.ajax({
            url: "{{ route('admin.users.ajax.view') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: userId
            },
            success: function(response) {
                if (response.success) {
                    displayUserDetails(response.user);
                    $('#viewUserModalLabel').html('<i class="fas fa-chart-pie me-2"></i>Ecommerce Overview - ' + response.user.name);
                    $('#viewUserModal').modal('show');
                }
            },
            error: function(xhr, status, error) {
                console.error('Ecommerce Overview Error:', xhr.responseJSON);
                showToast('Error loading ecommerce overview: ' + (xhr.responseJSON?.message || error), 'error');
            }
        });
    });

    // Delete User
    $(document).on('click', '.delete-user', function() {
        const userId = $(this).data('id');
        const userName = $(this).closest('tr').find('td:eq(2)').text().trim();
        
        showConfirmModal(
            `Are you sure you want to delete user "${userName}"? This action cannot be undone.`,
            function() {
                deleteUser(userId);
            }
        );
    });

    // Toggle Status
    $(document).on('click', '.toggle-status', function() {
        const userId = $(this).data('id');
        const newStatus = $(this).data('status');
        
        $.ajax({
            url: "{{ route('admin.users.ajax.toggle-status') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: userId,
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    usersTable.draw();
                    loadUserStats();
                    showToast(response.message, 'success');
                }
            },
            error: function() {
                showToast('Error updating user status', 'error');
            }
        });
    });

    // Bulk Actions
    $('#bulkActivateBtn').on('click', function() {
        performBulkAction('activate');
    });

    $('#bulkDeactivateBtn').on('click', function() {
        performBulkAction('deactivate');
    });

    $('#bulkDeleteBtn').on('click', function() {
        const selectedIds = $('.row-checkbox:checked').map(function() {
            return this.value;
        }).get();
        
        showConfirmModal(
            `Are you sure you want to delete ${selectedIds.length} selected users? This action cannot be undone.`,
            function() {
                performBulkAction('delete');
            }
        );
    });

    // Export Users
    $('#exportUsersBtn').on('click', function() {
        const status = $('#statusFilter').val();
        const role = $('#roleFilter').val();
        
        let url = "{{ route('admin.users.export.all') }}?";
        if (status) url += `status=${status}&`;
        if (role) url += `role=${role}&`;
        
        window.open(url, '_blank');
    });

    // Helper Functions
    function resetUserForm() {
        $('#userForm')[0].reset();
        $('#userId').val('');
        clearFormErrors();
    }

    function populateUserForm(user) {
        $('#userName').val(user.name);
        $('#userEmail').val(user.email);
        $('#userMobile').val(user.mobile_number);
        $('#countryCode').val(user.country_code);
        $('#userRole').val(user.role);
        $('#userStatus').val(user.status);
        $('#userBirth').val(user.date_of_birth);
        $('#userAddress').val(user.address);
        $('#userCity').val(user.city);
        $('#userCountry').val(user.country);
        $('#userBio').val(user.bio);
        clearFormErrors();
    }

    function displayUserDetails(user) {
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>User Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <img src="${user.avatar}" class="img-fluid rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                                <h6>${user.name}</h6>
                                <span class="badge bg-${user.status === 'active' ? 'success' : 'secondary'} me-1">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</span>
                                <span class="badge bg-primary">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Email:</strong></div>
                                <div class="col-8">${user.email}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Mobile:</strong></div>
                                <div class="col-8">${user.mobile_number || 'Not provided'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Birth:</strong></div>
                                <div class="col-8">${user.date_of_birth || 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Address:</strong></div>
                                <div class="col-8">${user.address || 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>City:</strong></div>
                                <div class="col-8">${user.city || 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Country:</strong></div>
                                <div class="col-8">${user.country || 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Joined:</strong></div>
                                <div class="col-8">${user.created_at}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Updated:</strong></div>
                                <div class="col-8">${user.updated_at || 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Last Login:</strong></div>
                                <div class="col-8">${user.last_login_at}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-header bg-gradient-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-store me-2"></i>Ecommerce Overview</h6>
                        </div>
                        <div class="card-body">
                            <!-- Ecommerce Metrics Grid -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="card border-danger border-2 h-100 hover-lift">
                                        <div class="card-body text-center p-3">
                                            <i class="fas fa-heart text-danger mb-2" style="font-size: 1.5rem;"></i>
                                            <div class="fw-bold fs-4 text-danger">${user.wishlist_count || 0}</div>
                                            <small class="text-muted fw-medium">Wishlist Items</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card border-warning border-2 h-100 hover-lift">
                                        <div class="card-body text-center p-3">
                                            <i class="fas fa-shopping-cart text-warning mb-2" style="font-size: 1.5rem;"></i>
                                            <div class="fw-bold fs-4 text-warning">${user.cart_count || 0}</div>
                                            <small class="text-muted fw-medium">Cart Items</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="card border-info border-2 h-100 hover-lift">
                                        <div class="card-body text-center p-3">
                                            <i class="fas fa-eye text-info mb-2" style="font-size: 1.5rem;"></i>
                                            <div class="fw-bold fs-4 text-info">${user.recent_views || 0}</div>
                                            <small class="text-muted fw-medium">Recent Views</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card border-primary border-2 h-100 hover-lift">
                                        <div class="card-body text-center p-3">
                                            <i class="fas fa-shopping-bag text-primary mb-2" style="font-size: 1.5rem;"></i>
                                            <div class="fw-bold fs-4 text-primary">${user.orders_count || 0}</div>
                                            <small class="text-muted fw-medium">Total Orders</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Summary -->
                            <div class="border-top pt-3">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-credit-card text-primary me-2"></i>Payment Summary
                                </h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="card bg-success bg-opacity-10 border-success h-100">
                                            <div class="card-body text-center p-2">
                                                <i class="fas fa-check-circle text-success mb-1"></i>
                                                <div class="fw-bold text-success">$${user.successful_payments || '0.00'}</div>
                                                <small class="text-muted">Successful</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-danger bg-opacity-10 border-danger h-100">
                                            <div class="card-body text-center p-2">
                                                <i class="fas fa-times-circle text-danger mb-1"></i>
                                                <div class="fw-bold text-danger">$${user.failed_payments || '0.00'}</div>
                                                <small class="text-muted">Failed</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ${user.bio ? `<div class="mt-3"><div class="card"><div class="card-body"><strong>Bio:</strong><p class="mt-2 mb-0">${user.bio}</p></div></div></div>` : ''}
        `;
        $('#userDetailsContent').html(html);
    }

    function displayFormErrors(errors) {
        clearFormErrors();
        
        Object.keys(errors).forEach(function(field) {
            const input = $(`#user${field.charAt(0).toUpperCase() + field.slice(1).replace('_', '')}`);
            if (input.length) {
                input.addClass('is-invalid');
                input.siblings('.invalid-feedback').text(errors[field][0]);
            }
        });
    }

    function clearFormErrors() {
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    function performBulkAction(action) {
        const selectedIds = $('.row-checkbox:checked').map(function() {
            return this.value;
        }).get();

        if (selectedIds.length === 0) {
            showToast('Please select users to perform this action', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.users.bulk-action') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                action: action,
                ids: selectedIds
            },
            success: function(response) {
                if (response.success) {
                    usersTable.draw();
                    loadUserStats();
                    $('#selectAll').prop('checked', false);
                    showToast(response.message, 'success');
                }
            },
            error: function() {
                showToast('Error performing bulk action', 'error');
            }
        });
    }

    function deleteUser(userId) {
        $.ajax({
            url: "{{ route('admin.users.ajax.delete') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: userId
            },
            success: function(response) {
                if (response.success) {
                    usersTable.draw();
                    loadUserStats();
                    showToast(response.message, 'success');
                }
            },
            error: function() {
                showToast('Error deleting user', 'error');
            }
        });
    }

    function showConfirmModal(message, callback) {
        $('#confirmMessage').text(message);
        $('#confirmModal').modal('show');
        
        $('#confirmActionBtn').off('click').on('click', function() {
            $('#confirmModal').modal('hide');
            callback();
        });
    }

    function showToast(message, type = 'info') {
        // Simple toast implementation
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const toast = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(function() {
            toast.alert('close');
        }, 5000);
    }
});
</script>
@endpush