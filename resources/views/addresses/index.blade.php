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
                    
                    <!-- Location Picker Widget -->
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            Quick Location Setup
                        </h6>
                        <div id="locationPicker">
                            <div class="location-picker">
                                <div class="location-detect mb-3">
                                    <button type="button" class="btn btn-primary location-detect-btn">
                                        <i class="bi bi-geo-alt me-2"></i>
                                        Use My Current Location
                                    </button>
                                    <div class="location-loading" style="display: none;">
                                        <div class="spinner-border spinner-border-sm me-2"></div>
                                        Detecting location...
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <div class="location-search-container position-relative">
                                            <input type="text" class="form-control location-search-input" placeholder="Search for area, city, or landmark..."
                                             autocomplete="off" name="">
                                            <div class="location-search-results position-absolute w-100 bg-white border rounded shadow-sm" 
                                            style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <input type="text" class="form-control location-pincode-input" 
                                        placeholder="Enter Pincode" maxlength="10" title="Enter postal/ZIP code (5-10 characters)" name="">
                                        <div class="pincode-feedback mt-1"></div>
                                    </div>
                                </div>
                                
                                <div class="location-info" style="display: none;">
                                    <div class="alert alert-success">
                                        <strong>Location Detected:</strong>
                                        <span class="location-display"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="type" class="form-label">Address Type *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="home">Home</option>
                                <option value="work">Work</option>
                                <option value="other">Other</option>
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

<!-- Geolocation SDK -->
<script src="{{ asset('js/geolocation.js') }}"></script>

<script>
$(document).ready(function() {
    // Initialize location features
    initializeLocationFeatures();
    
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
        
        // Get form data but exclude location picker inputs
        const formData = new FormData(this);
        
        // Convert to URL encoded string for AJAX
        const urlParams = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            // Only include actual form fields, exclude empty names
            if (key && key.trim() !== '') {
                console.log(`Form field: ${key} = ${value} (type: ${typeof value})`);
                urlParams.append(key, value);
            }
        }
        
        // Add unchecked checkbox value for is_default if not present
        if (!formData.has('is_default')) {
            urlParams.append('is_default', '0');
        }
        
        let ajaxData = urlParams.toString();
        
        // For PUT request, we need to add _method field
        if (isEdit) {
            ajaxData += '&_method=PUT';
        }
        
        // Validate critical fields
        const typeField = document.getElementById('type');
        const typeValue = typeField.value;
        console.log('Type field value:', typeValue);
        console.log('Type field valid options:', ['home', 'work', 'other']);
        
        if (!['home', 'work', 'other'].includes(typeValue)) {
            console.error('Invalid type value detected:', typeValue);
            alert('Please select a valid address type');
            return;
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
        
        // Debug: Inspect form fields
        console.log('Form fields inspection:');
        const formFields = this.querySelectorAll('input, select, textarea');
        formFields.forEach(field => {
            if (field.name) {
                console.log(`Field: ${field.name}, Value: ${field.value}, Type: ${field.type}`);
            }
        });
        
        // Debug: Log form data being sent
        console.log('Form data being sent:', ajaxData);
        console.log('URL:', url);
        console.log('Is edit:', isEdit);
        
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
                console.log('Error response:', xhr.responseJSON);
                console.log('Error status:', xhr.status);
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    console.log('Validation errors:', errors);
                    
                    $.each(errors, function(field, messages) {
                        console.log(`Error for field ${field}:`, messages);
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
        $('.form-control, .form-select').removeClass('is-invalid is-valid');
        $('.invalid-feedback').remove();
        
        // Clear location picker inputs (they don't have names so won't be submitted)
        $('.location-search-input').val('');
        $('.location-pincode-input').val('');
        $('.pincode-feedback').html('');
        $('.location-info').hide();
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Initialize location picker for address form
    initializeLocationPickerForForm();
});

// Show location message function
function showLocationMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.alert-message');
    existingMessages.forEach(msg => msg.remove());
    
    const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
    const icon = type === 'error' ? 'bi-exclamation-triangle' : 'bi-check-circle';
    
    const messageHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show alert-message mt-3" role="alert">
            <i class="bi ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.querySelector('.content-wrapper').insertAdjacentHTML('beforeend', messageHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert-message');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Initialize Location Features
function initializeLocationFeatures() {
    console.log('Initializing location features for addresses');
    
    // Add pincode change handler to existing postal_code field
    const postalCodeField = document.getElementById('postal_code');
    const postalCodeContainer = postalCodeField ? postalCodeField.closest('.col-md-4') : null;
    let postalCodeTimeout = null;
    
    if (postalCodeField) {
        // Create feedback div if it doesn't exist
        let feedbackDiv = postalCodeContainer.querySelector('.postal-code-feedback');
        if (!feedbackDiv) {
            feedbackDiv = document.createElement('div');
            feedbackDiv.className = 'postal-code-feedback mt-1';
            postalCodeField.parentNode.insertBefore(feedbackDiv, postalCodeField.nextSibling);
        }
        
        postalCodeField.addEventListener('input', function(e) {
            // Clean input - remove non-alphanumeric
            e.target.value = e.target.value.replace(/[^0-9A-Za-z]/g, '');
            
            const pincode = e.target.value.trim();
            
            // Show real-time validation feedback
            if (pincode.length === 0) {
                feedbackDiv.innerHTML = '';
                postalCodeField.classList.remove('is-valid', 'is-invalid');
                return;
            }
            
            const isValid = /^[0-9A-Za-z]{5,10}$/.test(pincode);
            if (isValid) {
                feedbackDiv.innerHTML = `<small class="text-success"><i class="bi bi-check-circle me-1"></i>Valid format</small>`;
                postalCodeField.classList.add('is-valid');
                postalCodeField.classList.remove('is-invalid');
            } else {
                feedbackDiv.innerHTML = `<small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Invalid format (5-10 characters)</small>`;
                postalCodeField.classList.add('is-invalid');
                postalCodeField.classList.remove('is-valid');
            }
            
            // Clear previous timeout
            if (postalCodeTimeout) {
                clearTimeout(postalCodeTimeout);
            }
            
            // Auto-fill address if valid pincode (6 digits for India)
            if (/^[0-9]{6}$/.test(pincode)) {
                postalCodeTimeout = setTimeout(async () => {
                    try {
                        feedbackDiv.innerHTML = `
                            <small class="text-info">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Looking up location...
                            </small>
                        `;
                        
                        const response = await fetch(`https://api.postalpincode.in/pincode/${pincode}`);
                        const data = await response.json();
                        
                        if (data && data[0] && data[0].Status === 'Success' && data[0].PostOffice && data[0].PostOffice.length > 0) {
                            const postOffice = data[0].PostOffice[0];
                            const location = {
                                city: postOffice.District,
                                state: postOffice.State,
                                area: postOffice.Name,
                                pincode: pincode
                            };
                            
                            // Fill form fields with location data
                            fillAddressForm(location, false);
                            
                            feedbackDiv.innerHTML = `
                                <small class="text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Location found: ${location.city || 'N/A'}, ${location.state || 'N/A'}
                                </small>
                            `;
                        } else {
                            throw new Error('Pincode not found in database');
                        }
                    } catch (error) {
                        console.error('Pincode lookup failed:', error);
                        feedbackDiv.innerHTML = `
                            <small class="text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Could not find location for this pincode
                            </small>
                        `;
                    }
                }, 800);
            }
        });
    }
}

// Initialize location picker for form
function initializeLocationPickerForForm() {
    const locationPickerContainer = document.getElementById('locationPicker');
    if (!locationPickerContainer) return;
    
    // Set up detect location button
    const detectBtn = locationPickerContainer.querySelector('.location-detect-btn');
    const loadingDiv = locationPickerContainer.querySelector('.location-loading');
    
    if (detectBtn) {
        detectBtn.addEventListener('click', function() {
            detectBtn.style.display = 'none';
            loadingDiv.style.display = 'block';
            
            // Use browser's geolocation API
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async function(position) {
                        try {
                            // Reverse geocode to get address details
                            const location = await reverseGeocode(position.coords.latitude, position.coords.longitude);
                            fillAddressForm(location);
                            
                            // Show location info
                            const locationInfo = locationPickerContainer.querySelector('.location-info');
                            const locationDisplay = locationPickerContainer.querySelector('.location-display');
                            if (locationInfo && locationDisplay) {
                                locationDisplay.textContent = `${location.city || 'Unknown'}, ${location.state || 'Unknown'}`;
                                locationInfo.style.display = 'block';
                            }
                            
                            showLocationMessage('Location detected successfully!', 'success');
                        } catch (error) {
                            console.error('Reverse geocoding failed:', error);
                            showLocationMessage('Location detected but unable to get address details.', 'warning');
                        }
                    },
                    function(error) {
                        console.error('Geolocation failed:', error);
                        showLocationMessage('Failed to detect location. Please enter manually.', 'error');
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            } else {
                showLocationMessage('Location detection not supported by your browser.', 'error');
            }
            
            // Reset button state after timeout
            setTimeout(() => {
                detectBtn.style.display = 'block';
                loadingDiv.style.display = 'none';
            }, 10000);
        });
    }
    
    // Add pincode change handler to location picker pincode input
    const locationPincodeInput = locationPickerContainer.querySelector('.location-pincode-input');
    const locationPincodeFeedback = locationPickerContainer.querySelector('.pincode-feedback');
    let locationPincodeTimeout = null;
    
    if (locationPincodeInput && locationPincodeFeedback) {
        locationPincodeInput.addEventListener('input', function(e) {
            // Clean input - remove non-alphanumeric
            e.target.value = e.target.value.replace(/[^0-9A-Za-z]/g, '');
            
            const pincode = e.target.value.trim();
            
            // Show real-time validation feedback
            if (pincode.length === 0) {
                locationPincodeFeedback.innerHTML = '';
                locationPincodeInput.classList.remove('is-valid', 'is-invalid');
                return;
            }
            
            const isValid = /^[0-9A-Za-z]{5,10}$/.test(pincode);
            if (isValid) {
                locationPincodeFeedback.innerHTML = `<small class="text-success"><i class="bi bi-check-circle me-1"></i>Valid format</small>`;
                locationPincodeInput.classList.add('is-valid');
                locationPincodeInput.classList.remove('is-invalid');
            } else {
                locationPincodeFeedback.innerHTML = `<small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Invalid format (5-10 characters)</small>`;
                locationPincodeInput.classList.add('is-invalid');
                locationPincodeInput.classList.remove('is-valid');
            }
            
            // Clear previous timeout
            if (locationPincodeTimeout) {
                clearTimeout(locationPincodeTimeout);
            }
            
            // Auto-fill address if valid pincode (6 digits for India)
            if (/^[0-9]{6}$/.test(pincode)) {
                locationPincodeTimeout = setTimeout(async () => {
                    try {
                        locationPincodeFeedback.innerHTML = `
                            <small class="text-info">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Looking up location...
                            </small>
                        `;
                        
                        const response = await fetch(`https://api.postalpincode.in/pincode/${pincode}`);
                        const data = await response.json();
                        
                        if (data && data[0] && data[0].Status === 'Success' && data[0].PostOffice && data[0].PostOffice.length > 0) {
                            const postOffice = data[0].PostOffice[0];
                            const location = {
                                city: postOffice.District,
                                state: postOffice.State,
                                area: postOffice.Name,
                                pincode: pincode
                            };
                            
                            // Fill form fields with location data including pincode
                            fillAddressForm(location, true);
                            
                            locationPincodeFeedback.innerHTML = `
                                <small class="text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Location found: ${location.city || 'N/A'}, ${location.state || 'N/A'}
                                </small>
                            `;
                        } else {
                            throw new Error('Pincode not found in database');
                        }
                    } catch (error) {
                        console.error('Location picker - Pincode lookup failed:', error);
                        locationPincodeFeedback.innerHTML = `
                            <small class="text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Could not find location for this pincode
                            </small>
                        `;
                    }
                }, 800);
            }
        });
    }
}

// Reverse geocode coordinates to get address
async function reverseGeocode(lat, lng) {
    try {
        const response = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=en`);
        const data = await response.json();
        
        return {
            city: data.city || data.locality || '',
            state: data.principalSubdivision || '',
            area: data.localityInfo?.administrative?.[0]?.name || '',
            formatted_address: data.localityLanguageRequested || ''
        };
    } catch (error) {
        console.error('Reverse geocoding failed:', error);
        throw error;
    }
}

// Fill address form with location data
function fillAddressForm(location, includePincode = true) {
    console.log('Filling address form with location:', location, 'includePincode:', includePincode);
    
    // Map location data to form fields - using the actual field names from the form
    const fieldMapping = {
        'city': location.city || '',
        'state': location.state || '',
        'address_line_1': location.area || location.road || location.formatted_address || ''
    };
    
    // Include pincode if requested
    if (includePincode && location.pincode) {
        fieldMapping['postal_code'] = location.pincode;
    }
    
    // Fill form fields
    Object.keys(fieldMapping).forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field && fieldMapping[fieldName]) {
            field.value = fieldMapping[fieldName];
            
            // Remove any validation classes and add success
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            
            // Trigger change event
            field.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
    
    console.log('Address form filled with location data');
}
</script>
@endpush