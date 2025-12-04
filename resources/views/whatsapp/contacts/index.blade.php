@extends('layouts.app')

@section('title', 'WhatsApp Contacts')

@section('content')
<div class="container-fluid">
    @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">WhatsApp Contacts</h1>
                    <p class="text-muted">Manage your WhatsApp contacts</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#importContactsModal">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                        <i class="bi bi-person-plus me-1"></i> Add Contact
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3>{{ number_format($contacts->total()) }}</h3>
                            <p class="mb-0">Total Contacts</p>
                        </div>
                        <i class="bi bi-people fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3>{{ number_format($contacts->where('status', 'active')->count() ?? 0) }}</h3>
                            <p class="mb-0">Active</p>
                        </div>
                        <i class="bi bi-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3>{{ number_format($contacts->where('is_blocked', false)->count() ?? 0) }}</h3>
                            <p class="mb-0">Unblocked</p>
                        </div>
                        <i class="bi bi-shield-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3>{{ number_format($contacts->where('message_count', '>', 0)->count() ?? 0) }}</h3>
                            <p class="mb-0">Messaged</p>
                        </div>
                        <i class="bi bi-chat-dots fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Blocked Status</label>
                            <select name="blocked" class="form-select">
                                <option value="">All Contacts</option>
                                <option value="0" {{ request('blocked') === '0' ? 'selected' : '' }}>Unblocked</option>
                                <option value="1" {{ request('blocked') === '1' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search contacts..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Contacts List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    @if($contacts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Contact</th>
                                        <th>Phone</th>
                                        <th>Company</th>
                                        <th>Tags</th>
                                        <th>Messages</th>
                                        <th>Last Message</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contacts as $contact)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input contact-checkbox" value="{{ $contact->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2 bg-primary text-white">
                                                    {{ $contact->initials }}
                                                </div>
                                                <div>
                                                    <strong>{{ $contact->name }}</strong>
                                                    @if($contact->email)
                                                        <br><small class="text-muted">{{ $contact->email }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $contact->formatted_phone }}</td>
                                        <td>
                                            {{ $contact->company ?: '-' }}
                                            @if($contact->position)
                                                <br><small class="text-muted">{{ $contact->position }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($contact->tags && is_array($contact->tags))
                                                @foreach($contact->tags as $tag)
                                                    <span class="badge bg-secondary me-1">{{ $tag }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ number_format($contact->message_count) }}</td>
                                        <td>{{ $contact->last_message_time }}</td>
                                        <td>{!! $contact->status_badge !!}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="sendMessage({{ $contact->id }})">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewContact({{ $contact->id }})">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="editContact({{ $contact->id }})">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                @if($contact->is_blocked)
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="unblockContact({{ $contact->id }})">
                                                        <i class="bi bi-unlock"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="blockContact({{ $contact->id }})">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteContact({{ $contact->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Bulk Actions -->
                        <div class="mt-3 d-none" id="bulkActions">
                            <div class="d-flex align-items-center">
                                <span class="me-3"><span id="selectedCount">0</span> contacts selected</span>
                                <button class="btn btn-sm btn-primary me-2" onclick="bulkSendMessage()">
                                    <i class="bi bi-send me-1"></i> Send Message
                                </button>
                                <button class="btn btn-sm btn-secondary me-2" onclick="bulkBlock()">
                                    <i class="bi bi-lock me-1"></i> Block
                                </button>
                                <button class="btn btn-sm btn-success me-2" onclick="bulkUnblock()">
                                    <i class="bi bi-unlock me-1"></i> Unblock
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="bulkDelete()">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $contacts->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-person-x fa-3x text-gray-400 mb-3"></i>
                            <h5 class="text-gray-600">No contacts found</h5>
                            <p class="text-gray-500">Add your first contact to get started!</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                                <i class="bi bi-person-plus me-1"></i> Add Contact
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Contact Modal -->
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addContactForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" class="form-control" name="phone" placeholder="+91 9876543210" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company</label>
                            <input type="text" class="form-control" name="company">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position</label>
                            <input type="text" class="form-control" name="position">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <input type="text" class="form-control" name="tags" placeholder="customer, vip, lead (comma separated)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Contacts Modal -->
<div class="modal fade" id="importContactsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Contacts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importContactsForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">CSV File</label>
                        <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                        <div class="form-text">Upload a CSV file with columns: name, phone, email, company, position</div>
                    </div>
                    <div class="mb-3">
                        <a href="#" class="btn btn-sm btn-outline-primary">Download Sample CSV</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import Contacts</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox functionality
    $('#selectAll').on('change', function() {
        $('.contact-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });
    
    $('.contact-checkbox').on('change', function() {
        updateBulkActions();
        
        // Update select all checkbox
        const totalCheckboxes = $('.contact-checkbox').length;
        const checkedCheckboxes = $('.contact-checkbox:checked').length;
        $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
        $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes);
    });
    
    function updateBulkActions() {
        const checkedCount = $('.contact-checkbox:checked').length;
        $('#selectedCount').text(checkedCount);
        
        if (checkedCount > 0) {
            $('#bulkActions').removeClass('d-none');
        } else {
            $('#bulkActions').addClass('d-none');
        }
    }
});

function sendMessage(contactId) {
    window.location.href = '{{ route("whatsapp.send.form") }}?contact=' + contactId;
}

function viewContact(contactId) {
    showAlert('info', 'View contact functionality would be implemented here.');
}

function editContact(contactId) {
    showAlert('info', 'Edit contact functionality would be implemented here.');
}

function blockContact(contactId) {
    if (confirm('Are you sure you want to block this contact?')) {
        showAlert('info', 'Block contact functionality would be implemented here.');
    }
}

function unblockContact(contactId) {
    if (confirm('Are you sure you want to unblock this contact?')) {
        showAlert('info', 'Unblock contact functionality would be implemented here.');
    }
}

function deleteContact(contactId) {
    if (confirm('Are you sure you want to delete this contact? This action cannot be undone.')) {
        showAlert('info', 'Delete contact functionality would be implemented here.');
    }
}

function bulkSendMessage() {
    const selectedIds = $('.contact-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Please select contacts first.');
        return;
    }
    
    window.location.href = '{{ route("whatsapp.bulk.form") }}?contacts=' + selectedIds.join(',');
}

function bulkBlock() {
    const selectedIds = $('.contact-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Please select contacts first.');
        return;
    }
    
    if (confirm(`Are you sure you want to block ${selectedIds.length} contacts?`)) {
        showAlert('info', 'Bulk block functionality would be implemented here.');
    }
}

function bulkUnblock() {
    const selectedIds = $('.contact-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Please select contacts first.');
        return;
    }
    
    if (confirm(`Are you sure you want to unblock ${selectedIds.length} contacts?`)) {
        showAlert('info', 'Bulk unblock functionality would be implemented here.');
    }
}

function bulkDelete() {
    const selectedIds = $('.contact-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Please select contacts first.');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedIds.length} contacts? This action cannot be undone.`)) {
        showAlert('info', 'Bulk delete functionality would be implemented here.');
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info');
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.alert').remove();
    $('.container-fluid').prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endpush
@endsection