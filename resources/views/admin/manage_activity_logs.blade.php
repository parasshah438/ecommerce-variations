@extends('admin.layout')

@section('title', 'User Activity Logs')
@section('page-title', 'User Activity Logs')
@section('page-description', 'Monitor and manage user activity logs')

@push('styles')
<style>
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }
    
    .activity-item {
        border-left: 3px solid #e9ecef;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }
    
    .activity-item.recent {
        border-left-color: #28a745;
    }
    
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .btn-group .btn {
        margin: 0 2px;
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
    <button type="button" class="btn btn-danger" id="clearAllLogsBtn">
        <i class="fas fa-trash me-1"></i>Clear All Logs
    </button>
    <button type="button" class="btn btn-success" id="exportLogsBtn">
        <i class="fas fa-download me-1"></i>Export Logs
    </button>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Toolbar -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="d-flex gap-2 flex-wrap">
                <select class="form-select form-select-sm" id="userFilter" style="width: auto;">
                    <option value="">All Users</option>
                </select>
                <input type="date" class="form-control form-control-sm" id="dateFilter" style="width: auto;">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">
                    <i class="fas fa-refresh"></i> Reset
                </button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="d-flex gap-2 justify-content-md-end">
                <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
        </div>
    </div>

    <!-- Activity Logs Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i>Activity Logs
            </h5>
        </div>
        <div class="card-body">
            <div class="loading-spinner" id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading activity logs...</p>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="activityLogsTable">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Activity</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>IP Address</th>
                            <th>Date</th>
                            <th width="120">Actions</th>
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

<!-- View Activity Modal -->
<div class="modal fade" id="viewActivityModal" tabindex="-1" aria-labelledby="viewActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewActivityModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Activity Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="activityDetailsContent">
                <!-- Activity details will be loaded here -->
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
<script>
$(document).ready(function() {
    // Initialize DataTables
    const activityLogsTable = $('#activityLogsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.user-activities.data') }}",
            type: 'POST',
            data: function(d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                d.user_filter = $('#userFilter').val();
                d.date_filter = $('#dateFilter').val();
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
            { 
                data: 'log_description',
                render: function(data, type, row) {
                    return `
                        <div class="activity-item">
                            <div class="fw-semibold text-truncate" style="max-width: 300px;" title="${data}">
                                ${data}
                            </div>
                        </div>
                    `;
                }
            },
            { data: 'name' },
            { data: 'email' },
            { data: 'ip_address' },
            { data: 'created_at' },
            { 
                data: 'actions', 
                orderable: false, 
                searchable: false 
            }
        ],
        order: [[5, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: 'No activity logs found',
            zeroRecords: 'No matching activity logs found'
        },
        drawCallback: function() {
            updateBulkActionButtons();
        }
    });

    // Filter handlers
    $('#userFilter, #dateFilter').on('change', function() {
        activityLogsTable.draw();
    });

    $('#resetFilters').on('click', function() {
        $('#userFilter, #dateFilter').val('').trigger('change');
    });

    // Select all functionality
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulkActionButtons();
    });

    $(document).on('change', '.row-checkbox', function() {
        updateBulkActionButtons();
        
        const totalCheckboxes = $('.row-checkbox').length;
        const checkedCheckboxes = $('.row-checkbox:checked').length;
        
        $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
        $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes);
    });

    // Update bulk action buttons
    function updateBulkActionButtons() {
        const selectedCount = $('.row-checkbox:checked').length;
        $('#bulkDeleteBtn').prop('disabled', selectedCount === 0);
    }

    // View Activity
    $(document).on('click', '.view-activity', function() {
        const userId = $(this).data('id');
        
        $.ajax({
            url: "{{ route('admin.user-activities.view') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: userId
            },
            success: function(response) {
                if (response.success) {
                    displayActivityDetails(response.user, response.activities);
                    $('#viewActivityModal').modal('show');
                }
            },
            error: function() {
                showToast('Error loading activity details', 'error');
            }
        });
    });

    // Delete Activity
    $(document).on('click', '.delete-activity', function() {
        const activityId = $(this).data('id');
        
        showConfirmModal(
            'Are you sure you want to delete this activity log? This action cannot be undone.',
            function() {
                deleteActivity(activityId);
            }
        );
    });

    // Bulk Delete
    $('#bulkDeleteBtn').on('click', function() {
        const selectedIds = $('.row-checkbox:checked').map(function() {
            return this.value;
        }).get();
        
        showConfirmModal(
            `Are you sure you want to delete ${selectedIds.length} selected activity logs? This action cannot be undone.`,
            function() {
                bulkDeleteActivities(selectedIds);
            }
        );
    });

    // Clear All Logs
    $('#clearAllLogsBtn').on('click', function() {
        showConfirmModal(
            'Are you sure you want to clear ALL activity logs? This will permanently delete all activity records.',
            function() {
                clearAllLogs();
            }
        );
    });

    // Helper Functions
    function displayActivityDetails(user, activities) {
        let activitiesHtml = '';
        
        if (activities && activities.length > 0) {
            activities.forEach(function(activity) {
                activitiesHtml += `
                    <div class="activity-item mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">${activity.log_description}</div>
                                <small class="text-muted">IP: ${activity.ip_address}</small>
                            </div>
                            <small class="text-muted">${new Date(activity.created_at).toLocaleString()}</small>
                        </div>
                    </div>
                `;
            });
        } else {
            activitiesHtml = '<p class="text-muted">No activities found for this user.</p>';
        }

        const html = `
            <div class="row">
                <div class="col-md-4 text-center">
                    <h5>${user.name}</h5>
                    <p class="text-muted">${user.email}</p>
                </div>
                <div class="col-md-8">
                    <h6>Recent Activities:</h6>
                    <div class="activities-list" style="max-height: 400px; overflow-y: auto;">
                        ${activitiesHtml}
                    </div>
                </div>
            </div>
        `;
        
        $('#activityDetailsContent').html(html);
    }

    function deleteActivity(activityId) {
        $.ajax({
            url: "{{ route('admin.user-activities.delete') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: activityId
            },
            success: function(response) {
                if (response.success) {
                    activityLogsTable.draw();
                    showToast(response.message, 'success');
                }
            },
            error: function() {
                showToast('Error deleting activity log', 'error');
            }
        });
    }

    function bulkDeleteActivities(ids) {
        $.ajax({
            url: "{{ route('admin.user-activities.delete-multiple') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: ids
            },
            success: function(response) {
                if (response.success) {
                    activityLogsTable.draw();
                    $('#selectAll').prop('checked', false);
                    showToast(response.message, 'success');
                }
            },
            error: function() {
                showToast('Error deleting activity logs', 'error');
            }
        });
    }

    function clearAllLogs() {
        // This would need a separate endpoint to clear all logs
        showToast('Clear all logs functionality needs to be implemented', 'warning');
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