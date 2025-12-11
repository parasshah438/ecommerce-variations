@extends('admin.layout')


@push('styles')
<style>
    .slider-image-thumb {
        width: 60px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
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
    
    .stats-card i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    
    .stats-card h3 {
        font-size: 2rem;
        margin-bottom: 0.25rem;
    }
    
    .filter-card {
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

    .slider-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Sliders Management</h1>
                    <p class="text-muted">Manage your website sliders and banners</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sliderModal" id="add-slider-btn">
                        <i class="bi bi-plus-lg"></i> Add New Slider
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-12">
            <div class="filter-card">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label for="status-filter" class="form-label">Filter by Status:</label>
                        <select id="status-filter" class="form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bulk Actions:</label>
                        <div class="d-flex gap-2">
                            <select id="bulk-action" class="form-select">
                                <option value="">Select Action</option>
                                <option value="activate">Activate Selected</option>
                                <option value="deactivate">Deactivate Selected</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                            <button type="button" class="btn btn-secondary" id="apply-bulk-action">
                                Apply
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-outline-primary" id="refresh-table">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sliders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="loading-spinner" id="loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading sliders...</p>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="sliders-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" id="select-all" class="form-check-input">
                                    </th>
                                    <th width="80">Image</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th width="100">Status</th>
                                    <th width="80">Order</th>
                                    <th width="120">Created</th>
                                    <th width="150">Actions</th>
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
    </div>
</div>

<!-- Slider Modal -->
<div class="modal fade" id="sliderModal" tabindex="-1" aria-labelledby="sliderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sliderModalLabel">Add New Slider</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="sliderForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="link" class="form-label">Link URL</label>
                                <input type="url" class="form-control" id="link" name="link" placeholder="https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Button text field removed - not in database schema -->
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">
                                        Active Status
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="image" class="form-label">Slider Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div class="form-text">Recommended size: 1920x800px. Max file size: 5MB</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="image-preview" style="display: none;">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Image Preview</label>
                                <div>
                                    <img id="preview-img" src="" alt="Preview" class="img-fluid" style="max-height: 200px; border-radius: 8px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="sliderForm" class="btn btn-primary" id="save-slider-btn">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
                    Save Slider
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables CSS & JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.4/bootbox.min.js"></script>
<script>
$(document).ready(function() {
    let table;
    
    // Initialize DataTable
    function initializeTable() {
        $('#loading-spinner').show();
        
        table = $('#sliders-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.sliders.data") }}',
                data: function(d) {
                    d.status = $('#status-filter').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable Ajax Error:', error);
                    $('#loading-spinner').hide();
                    showAlert('Error loading sliders data', 'danger');
                }
            },
            columns: [
                {
                    data: 'id',
                    name: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return '<input type="checkbox" class="form-check-input row-checkbox" value="' + data + '">';
                    }
                },
                {
                    data: 'image',
                    name: 'image',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (data) {
                            return '<img src="' + data + '" class="slider-image-thumb" alt="Slider Image">';
                        }
                        return '<div class="bg-light d-flex align-items-center justify-content-center slider-image-thumb"><i class="bi bi-image"></i></div>';
                    }
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'description',
                    name: 'description',
                    render: function(data, type, row) {
                        if (data && data.length > 50) {
                            return data.substring(0, 50) + '...';
                        }
                        return data || '-';
                    }
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row) {
                        if (data) {
                            return '<span class="badge bg-success">Active</span>';
                        }
                        return '<span class="badge bg-secondary">Inactive</span>';
                    }
                },
                {
                    data: 'sort_order',
                    name: 'sort_order',
                    className: 'text-center'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data, type, row) {
                        return new Date(data).toLocaleDateString();
                    }
                },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let actions = '<div class="action-buttons">' +
                            '<button type="button" class="btn btn-sm btn-outline-info view-slider me-1" data-id="' + data + '" title="View Slider">' +
                                '<i class="bi bi-eye"></i>' +
                            '</button>' +
                            '<button type="button" class="btn btn-sm btn-outline-primary edit-slider me-1" data-id="' + data + '" title="Edit Slider">' +
                                '<i class="bi bi-pencil"></i>' +
                            '</button>' +
                            '<button type="button" class="btn btn-sm btn-outline-danger delete-slider" data-id="' + data + '" title="Delete Slider">' +
                                '<i class="bi bi-trash"></i>' +
                            '</button>' +
                        '</div>';
                        return actions;
                    }
                }
            ],
            order: [[6, 'desc']],
            pageLength: 25,
            responsive: true,
            drawCallback: function() {
                $('#loading-spinner').hide();
            }
        });
    }

    // Load statistics
    function loadStats() {
        $.get('{{ route("admin.sliders.data") }}?get_stats=1')
            .done(function(response) {
                if (response.stats) {
                    $('#total-sliders').text(response.stats.total);
                    $('#active-sliders').text(response.stats.active);
                    $('#inactive-sliders').text(response.stats.inactive);
                }
            })
            .fail(function() {
                console.error('Failed to load statistics');
            });
    }

    // Initialize everything
    initializeTable();
    loadStats();

    // Status filter change
    $('#status-filter').on('change', function() {
        table.ajax.reload();
    });

    // Refresh table
    $('#refresh-table').on('click', function() {
        table.ajax.reload();
        loadStats();
    });

    // Select all checkboxes
    $('#select-all').on('change', function() {
        $('.row-checkbox').prop('checked', this.checked);
    });

    // Bulk actions
    $('#apply-bulk-action').on('click', function() {
        let action = $('#bulk-action').val();
        let selectedIds = [];
        
        $('.row-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (!action) {
            showAlert('Please select an action', 'warning');
            return;
        }

        if (selectedIds.length === 0) {
            showAlert('Please select at least one slider', 'warning');
            return;
        }

        if (action === 'delete') {
            bootbox.confirm({
                title: "Bulk Delete Sliders",
                message: 'Are you sure you want to delete ' + selectedIds.length + ' selected slider(s)? This action cannot be undone.',
                buttons: {
                    cancel: {
                        label: '<i class="bi bi-x"></i> Cancel',
                        className: 'btn-secondary'
                    },
                    confirm: {
                        label: '<i class="bi bi-trash"></i> Delete All',
                        className: 'btn-danger'
                    }
                },
                callback: function (result) {
                    if (result) {
                        performBulkAction(action, selectedIds);
                    }
                }
            });
            return;
        }

        // Perform non-delete bulk actions immediately
        performBulkAction(action, selectedIds);
    });

    function performBulkAction(action, selectedIds) {
        $.post('{{ route("admin.sliders.bulk-action") }}', {
            _token: '{{ csrf_token() }}',
            action: action,
            ids: selectedIds
        })
        .done(function(response) {
            showAlert(response.message, 'success');
            table.ajax.reload();
            loadStats();
            $('#select-all').prop('checked', false);
        })
        .fail(function(xhr) {
            let message = 'Failed to perform bulk action';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert(message, 'danger');
        });
    }

    // Delete slider
    $(document).on('click', '.delete-slider', function() {
        let id = $(this).data('id');
        
        bootbox.confirm({
            title: "Delete Slider",
            message: "Are you sure you want to delete this slider? This action cannot be undone.",
            buttons: {
                cancel: {
                    label: '<i class="bi bi-x"></i> Cancel',
                    className: 'btn-secondary'
                },
                confirm: {
                    label: '<i class="bi bi-trash"></i> Delete',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if (result) {
                    $.ajax({
                        url: '/admin/sliders/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        }
                    })
                    .done(function(response) {
                        showAlert(response.message, 'success');
                        table.ajax.reload();
                        loadStats();
                    })
                    .fail(function(xhr) {
                        let message = 'Failed to delete slider';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showAlert(message, 'danger');
                    });
                }
            }
        });
    });

    // Alert helper function
    function showAlert(message, type) {
        let alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
        
        $('.container-fluid').prepend(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }

    // Modal form handling
    $('#add-slider-btn').on('click', function() {
        $('#sliderModalLabel').text('Add New Slider');
        $('#sliderForm')[0].reset();
        $('#image-preview').hide();
        $('#sliderForm').removeAttr('data-slider-id');
    });

    // Image preview
    $('#image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-img').attr('src', e.target.result);
                $('#image-preview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#image-preview').hide();
        }
    });

    // Form submission
    $('#sliderForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const isEdit = $(this).attr('data-slider-id');
        const url = isEdit ? '/admin/sliders/' + isEdit : '{{ route("admin.sliders.store") }}';
        const method = isEdit ? 'PUT' : 'POST';
        
        // Fix checkbox value
        formData.delete('is_active');
        formData.append('is_active', $('#is_active').is(':checked') ? '1' : '0');
        
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        // Show loading state
        const saveBtn = $('#save-slider-btn');
        const spinner = saveBtn.find('.spinner-border');
        const originalText = saveBtn.text();
        
        saveBtn.prop('disabled', true);
        spinner.show();
        saveBtn.find('span:not(.spinner-border)').text('Saving...');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .done(function(response) {
            showAlert(response.message || 'Slider saved successfully!', 'success');
            $('#sliderModal').modal('hide');
            table.ajax.reload();
            loadStats();
        })
        .fail(function(xhr) {
            let message = 'Failed to save slider';
            
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                // Validation errors
                const errors = xhr.responseJSON.errors;
                message = Object.values(errors).flat().join('<br>');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            
            showAlert(message, 'danger');
        })
        .always(function() {
            // Reset button state
            saveBtn.prop('disabled', false);
            spinner.hide();
            saveBtn.find('span:not(.spinner-border)').text(originalText.replace('Saving...', 'Save Slider'));
        });
    });

    // Edit slider functionality
    $(document).on('click', '.edit-slider', function() {
        const sliderId = $(this).data('id');
        
        // Load slider data
        $.get('/admin/sliders/' + sliderId + '/edit')
            .done(function(response) {
                if (response.slider) {
                    const slider = response.slider;
                    
                    $('#sliderModalLabel').text('Edit Slider');
                    $('#title').val(slider.title);
                    $('#description').val(slider.description);
                    $('#link').val(slider.link);
                    $('#sort_order').val(slider.sort_order);
                    $('#is_active').prop('checked', slider.is_active);
                    
                    // Set form to edit mode
                    $('#sliderForm').attr('data-slider-id', sliderId);
                    
                    // Show image preview if exists
                    if (slider.image_url) {
                        $('#preview-img').attr('src', slider.image_url);
                        $('#image-preview').show();
                    }
                    
                    $('#sliderModal').modal('show');
                }
            })
            .fail(function() {
                showAlert('Failed to load slider data', 'danger');
            });
    });
});
</script>
@endpush
