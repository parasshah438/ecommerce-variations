@extends('layouts.app')

@section('title', 'My Addresses')

@section('breadcrumb')
    <li class="breadcrumb-item active">Addresses</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.css">
<style>
    .address-card {
        transition: all 0.3s ease;
    }
    .address-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
    .modal-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
        color: white;
        border-radius: 12px 12px 0 0;
    }
    .modal-header .btn-close {
        filter: invert(1);
    }
    .form-label {
        font-weight: 600;
        color: var(--text-primary);
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }
    .btn-primary {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }
    .btn-primary:hover {
        background: var(--primary-hover);
        border-color: var(--primary-hover);
    }
    .table th {
        background: var(--sidebar-hover);
        color: var(--text-primary);
        border: none;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0">
                                <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                                My Addresses
                            </h4>
                            <p class="text-muted mb-0">Manage your shipping and billing addresses</p>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addressModal">
                                <i class="bi bi-plus-lg me-2"></i>Add New Address
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="addressesTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Type</th>
                                    <th>Phone</th>
                                    <th>Default</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-geo-alt me-2"></i>
                    <span id="modalTitle">Add New Address</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addressForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="addressId" name="address_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="type" class="form-label">Address Type *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="shipping">Shipping</option>
                                <option value="billing">Billing</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-4">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="company" class="form-label">Company</label>
                            <input type="text" class="form-control" id="company" name="company">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address_line_1" class="form-label">Address Line 1 *</label>
                        <input type="text" class="form-control" id="address_line_1" name="address_line_1" required>
                    </div>

                    <div class="mb-3">
                        <label for="address_line_2" class="form-label">Address Line 2</label>
                        <input type="text" class="form-control" id="address_line_2" name="address_line_2">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="city" class="form-label">City *</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-4">
                            <label for="state" class="form-label">State *</label>
                            <input type="text" class="form-control" id="state" name="state" required>
                        </div>
                        <div class="col-md-4">
                            <label for="postal_code" class="form-label">Postal Code *</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="country" class="form-label">Country *</label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="">Select Country</option>
                                <option value="United States">United States</option>
                                <option value="Canada">Canada</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="Australia">Australia</option>
                                <option value="Germany">Germany</option>
                                <option value="France">France</option>
                                <option value="India">India</option>
                                <option value="China">China</option>
                                <option value="Japan">Japan</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input type="hidden" name="is_default" value="0">
                                <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1">
                                <label class="form-check-label" for="is_default">
                                    <i class="bi bi-star text-warning me-1"></i>Set as default address
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Save Address</span>
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#addressesTable').DataTable({
        processing: true,
        ajax: {
            url: '{{ route("addresses.data") }}',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'full_name',
                title: 'Name'
            },
            { 
                data: 'full_address',
                title: 'Address'
            },
            { 
                data: 'type_badge',
                title: 'Type',
                orderable: false,
                searchable: false
            },
            { 
                data: 'phone',
                title: 'Phone'
            },
            { 
                data: 'default_badge',
                title: 'Default',
                orderable: false,
                searchable: false
            },
            { 
                data: 'actions',
                title: 'Actions',
                orderable: false,
                searchable: false,
                width: '120px'
            }
        ],
        order: [[0, 'asc']],
        pageLength: 10,
        responsive: true,
        autoWidth: false,
        language: {
            emptyTable: "No addresses found. Click 'Add New Address' to create your first address.",
            zeroRecords: "No matching addresses found.",
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
        },
        drawCallback: function() {
            // Reinitialize tooltips after each draw
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Add/Update Address
    $('#addressForm').on('submit', function(e) {
        e.preventDefault();
        
        const isEdit = $('#addressId').val();
        const formData = $(this).serialize();
        let ajaxData = formData;
        
        // For PUT request, we need to add _method field
        if (isEdit) {
            ajaxData += '&_method=PUT';
        }
        
        const url = isEdit ? '{{ url("addresses") }}/' + $('#addressId').val() : '{{ route("addresses.store") }}';
        
        const submitBtn = $(this).find('button[type="submit"]');
        const btnText = submitBtn.find('.btn-text');
        const spinner = submitBtn.find('.spinner-border');
        
        // Show loading state
        submitBtn.prop('disabled', true);
        btnText.text(isEdit ? 'Updating...' : 'Saving...');
        spinner.removeClass('d-none');
        
        // Clear previous errors
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        $.ajax({
            url: url,
            method: 'POST',
            data: ajaxData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#addressModal').modal('hide');
                    table.ajax.reload(null, false); // Keep current page
                    toastr.success(response.message);
                    $('#addressForm')[0].reset();
                    $('#addressId').val('');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const input = $(`#${field}`);
                        input.addClass('is-invalid');
                        input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message || 'An error occurred');
                }
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false);
                btnText.text('Save Address');
                spinner.addClass('d-none');
            }
        });
    });

    // Edit Address
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '{{ url("addresses") }}/' + id + '/edit',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#modalTitle').text('Edit Address');
                    $('#addressId').val(data.id);
                    
                    // Populate form fields
                    $('#type').val(data.type);
                    $('#first_name').val(data.first_name);
                    $('#last_name').val(data.last_name);
                    $('#company').val(data.company);
                    $('#phone').val(data.phone);
                    $('#address_line_1').val(data.address_line_1);
                    $('#address_line_2').val(data.address_line_2);
                    $('#city').val(data.city);
                    $('#state').val(data.state);
                    $('#postal_code').val(data.postal_code);
                    $('#country').val(data.country);
                    $('#is_default').prop('checked', data.is_default);
                    
                    $('#addressModal').modal('show');
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed to load address');
            }
        });
    });

    // Delete Address
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        bootbox.confirm({
            title: '<i class="bi bi-exclamation-triangle text-warning me-2"></i>Delete Address',
            message: `Are you sure you want to delete the address for <strong>${name}</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
            size: 'small',
            buttons: {
                confirm: {
                    label: '<i class="bi bi-trash me-2"></i>Delete',
                    className: 'btn-danger'
                },
                cancel: {
                    label: '<i class="bi bi-x me-2"></i>Cancel',
                    className: 'btn-secondary'
                }
            },
            callback: function(result) {
                if (result) {
                    $.ajax({
                        url: '{{ url("addresses") }}/' + id,
                        method: 'POST',
                        data: {
                            '_method': 'DELETE'
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload(null, false);
                                toastr.success(response.message);
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Failed to delete address');
                        }
                    });
                }
            }
        });
    });

    // Set Default Address
    $(document).on('click', '.set-default-btn', function() {
        const id = $(this).data('id');
        const btn = $(this);
        
        // Show loading state
        btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>Setting...');
        
        $.ajax({
            url: '{{ url("addresses") }}/' + id + '/set-default',
            method: 'POST',
            data: {
                '_method': 'PATCH'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    table.ajax.reload(null, false);
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed to set default address');
                // Reset button state on error
                btn.prop('disabled', false).html('<i class="bi bi-star me-1"></i>Set Default');
            }
        });
    });

    // Reset form when modal is closed
    $('#addressModal').on('hidden.bs.modal', function() {
        $('#addressForm')[0].reset();
        $('#addressId').val('');
        $('#modalTitle').text('Add New Address');
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush