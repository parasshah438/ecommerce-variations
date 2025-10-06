@extends('admin.layout')

@section('title', 'Attribute Values Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-gray-800">Attribute Values Management</h1>
                <a href="{{ route('admin.attribute-values.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add New Value
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Attribute Values</h6>
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('admin.attribute-values.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="attribute_id" class="form-label">Filter by Attribute</label>
                                    <select name="attribute_id" id="attribute_id" class="form-control">
                                        <option value="">All Attributes</option>
                                        @foreach($attributes as $attribute)
                                            <option value="{{ $attribute->id }}" 
                                                    {{ request('attribute_id') == $attribute->id ? 'selected' : '' }}>
                                                {{ $attribute->name }} ({{ $attribute->attributeValues->count() }} values)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="search" class="form-label">Search Values</label>
                                    <input type="text" 
                                           name="search" 
                                           id="search"
                                           class="form-control" 
                                           placeholder="Search values, colors..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex">
                                        <button class="btn btn-primary mr-2" type="submit">
                                            <i class="bi bi-search"></i> Filter
                                        </button>
                                        @if(request('search') || request('attribute_id'))
                                            <a href="{{ route('admin.attribute-values.index') }}" class="btn btn-secondary">
                                                <i class="bi bi-x-lg"></i> Clear
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Attribute</th>
                                    <th>Value</th>
                                    <th>Visual</th>
                                    <th>Default</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attributeValues as $value)
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.attributes.show', $value->attribute) }}" 
                                               class="text-decoration-none">
                                                <strong>{{ $value->attribute->name }}</strong>
                                            </a>
                                            <br>
                                            <span class="badge badge-{{ $value->attribute->type === 'color' ? 'info' : ($value->attribute->type === 'size' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($value->attribute->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $value->value }}</strong>
                                        </td>
                                        <td>
                                            @if($value->attribute->type === 'color' && $value->hex_color)
                                                <div class="color-swatch">
                                                    <div class="color-box {{ $value->hex_color === '#FFFFFF' ? 'white-color' : '' }}" 
                                                         style="background-color: {{ $value->hex_color }};"
                                                         title="{{ $value->hex_color }}"></div>
                                                    <span class="small">{{ $value->hex_color }}</span>
                                                </div>
                                            @elseif($value->attribute->type === 'size')
                                                <span class="badge badge-outline-primary">{{ $value->value }}</span>
                                            @elseif($value->attribute->type === 'number')
                                                <span class="text-primary"><i class="bi bi-123"></i> {{ $value->value }}</span>
                                            @else
                                                <span class="text-muted small">Text value</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($value->is_default)
                                                <span class="badge badge-success">Default</span>
                                            @else
                                                <span class="badge badge-secondary">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $value->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.attribute-values.show', $value) }}" 
                                                   class="btn btn-info btn-sm" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.attribute-values.edit', $value) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        onclick="confirmDelete({{ $value->id }}, '{{ $value->value }}')" 
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="mb-0 text-muted">No attribute values found.</p>
                                            @if(!request('search') && !request('attribute_id'))
                                                <a href="{{ route('admin.attribute-values.create') }}" class="btn btn-primary mt-2">
                                                    Create Your First Attribute Value
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $attributeValues->links('admin.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the attribute value "<span id="deleteName"></span>"?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Improve color display */
.color-swatch {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.color-box {
    width: 32px;
    height: 32px;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.color-box.white-color {
    border-color: #adb5bd;
}

/* Better table spacing */
.table td {
    vertical-align: middle;
}

.btn-group .btn {
    margin: 0 1px;
}

/* Badge styles for size attributes */
.badge-outline-primary {
    color: #007bff;
    border: 1px solid #007bff;
    background: transparent;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
}

/* Force small pagination on this page */
.pagination-sm .page-link {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.75rem !important;
    line-height: 1.25 !important;
}

.pagination-sm .page-item:first-child .page-link,
.pagination-sm .page-item:last-child .page-link {
    font-size: 12px !important;
    width: 32px !important;
    height: 32px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteForm').action = `/admin/attribute-values/${id}`;
    $('#deleteModal').modal('show');
}
</script>
@endpush