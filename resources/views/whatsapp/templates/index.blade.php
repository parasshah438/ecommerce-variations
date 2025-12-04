@extends('layouts.app')

@section('title', 'WhatsApp Templates')

@section('content')
<div class="container-fluid">
    @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Error: The templates page encountered an issue. Please try again later.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">WhatsApp Templates</h1>
                    <p class="text-muted">Manage your message templates</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                        <i class="bi bi-plus-circle me-1"></i> Create Template
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    @if($templates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Content Preview</th>
                                        <th>Status</th>
                                        <th>Usage Count</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                    <tr>
                                        <td>
                                            <strong>{{ $template->name }}</strong>
                                            @if($template->description)
                                                <br><small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($template->category) }}</span>
                                        </td>
                                        <td>
                                            <div class="template-preview" style="max-width: 300px;">
                                                {{ Str::limit($template->content, 100) }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ ucfirst($template->status) }}</span>
                                        </td>
                                        <td>{{ number_format($template->usage_count) }}</td>
                                        <td>{{ $template->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="previewTemplate({{ $template->id }})">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="useTemplate({{ $template->id }})">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $templates->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-file-text fa-3x text-gray-400 mb-3"></i>
                            <h5 class="text-gray-600">No templates found</h5>
                            <p class="text-gray-500">Create your first message template to get started!</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                                <i class="bi bi-plus-circle me-1"></i> Create Template
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createTemplateForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Template Name *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="general">General</option>
                                <option value="marketing">Marketing</option>
                                <option value="notification">Notification</option>
                                <option value="greeting">Greeting</option>
                                <option value="reminder">Reminder</option>
                                <option value="support">Support</option>
                                <option value="promotional">Promotional</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="Brief description of the template">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Template Content *</label>
                        <textarea class="form-control" name="content" rows="5" required placeholder="Enter your template content here..."></textarea>
                        <div class="form-text">
                            You can use variables like: user_name, current_date, site_name, etc.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/whatsapp-templates.js') }}?v={{ time() }}"></script>
@endpush
@endsection@extends('layouts.app')

@section('title', 'WhatsApp Templates')

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
                    <h1 class="h3 mb-0">WhatsApp Templates</h1>
                    <p class="text-muted">Manage your message templates</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                        <i class="bi bi-plus-circle me-1"></i> Create Template
                    </button>
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
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <option value="marketing" {{ request('category') == 'marketing' ? 'selected' : '' }}>Marketing</option>
                                <option value="notification" {{ request('category') == 'notification' ? 'selected' : '' }}>Notification</option>
                                <option value="greeting" {{ request('category') == 'greeting' ? 'selected' : '' }}>Greeting</option>
                                <option value="reminder" {{ request('category') == 'reminder' ? 'selected' : '' }}>Reminder</option>
                                <option value="support" {{ request('category') == 'support' ? 'selected' : '' }}>Support</option>
                                <option value="promotional" {{ request('category') == 'promotional' ? 'selected' : '' }}>Promotional</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search templates..." value="{{ request('search') }}">
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

    <!-- Templates List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    @if($templates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Content Preview</th>
                                        <th>Status</th>
                                        <th>Usage Count</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                    <tr>
                                        <td>
                                            <strong>{{ $template->name }}</strong>
                                            @if($template->description)
                                                <br><small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>{!! $template->category_badge !!}</td>
                                        <td>
                                            <div class="template-preview" style="max-width: 300px;">
                                                {{ Str::limit($template->content, 100) }}
                                            </div>
                                        </td>
                                        <td>{!! $template->status_badge !!}</td>
                                        <td>{{ number_format($template->usage_count) }}</td>
                                        <td>{{ $template->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="previewTemplate({{ $template->id }})">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="useTemplate({{ $template->id }})">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="editTemplate({{ $template->id }})">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTemplate({{ $template->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $templates->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-file-text fa-3x text-gray-400 mb-3"></i>
                            <h5 class="text-gray-600">No templates found</h5>
                            <p class="text-gray-500">Create your first message template to get started!</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                                <i class="bi bi-plus-circle me-1"></i> Create Template
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createTemplateForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Template Name *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="general">General</option>
                                <option value="marketing">Marketing</option>
                                <option value="notification">Notification</option>
                                <option value="greeting">Greeting</option>
                                <option value="reminder">Reminder</option>
                                <option value="support">Support</option>
                                <option value="promotional">Promotional</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="Brief description of the template">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Template Content *</label>
                        <textarea class="form-control" name="content" rows="5" required placeholder="Enter your template content here..."></textarea>
                        <div class="form-text">
                            You can use variables in double curly braces like <code>{<!-- -->{user_name}}</code>, <code>{<!-- -->{current_date}}</code>, <code>{<!-- -->{site_name}}</code>, etc.
                            <br><strong>Available variables:</strong> user_name, user_email, current_date, current_time, site_name, site_url, customer_name, order_id, amount, product_name
                        </div>
                    </div>
                    
                    <!-- Sample Templates -->
                    <div class="mb-3">
                        <label class="form-label">Sample Templates</label>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Welcome Message</h6>
                                <div class="border p-2 mb-2 sample-template" style="cursor: pointer;" data-template="welcome">
                                    <small>Hi [Name]! Welcome message template...</small>
                                </div>
                                
                                <h6>Order Confirmation</h6>
                                <div class="border p-2 mb-2 sample-template" style="cursor: pointer;" data-template="order">
                                    <small>Order confirmation template...</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Reminder Message</h6>
                                <div class="border p-2 mb-2 sample-template" style="cursor: pointer;" data-template="reminder">
                                    <small>Reminder message template...</small>
                                </div>
                                
                                <h6>Support Message</h6>
                                <div class="border p-2 mb-2 sample-template" style="cursor: pointer;" data-template="support">
                                    <small>Support message template...</small>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">Click on any sample to use it as a starting point</small>
                    </div>

                    <!-- Template Preview -->
                    <div class="mb-3">
                        <label class="form-label">Preview</label>
                        <div id="templatePreview" class="border p-3 bg-light rounded">
                            <em class="text-muted">Template preview will appear here...</em>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="draft">Draft</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template Preview Modal -->
<div class="modal fade" id="previewTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.sample-template {
    transition: all 0.2s ease;
    border-radius: 4px;
}

.sample-template:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    transform: translateY(-1px);
}

.accordion-button:not(.collapsed) {
    background-color: #e7f3ff;
    border-color: #b8daff;
}

.template-preview {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.4;
    white-space: pre-wrap;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/whatsapp-templates.js') }}"></script>
@endpush
@endsection