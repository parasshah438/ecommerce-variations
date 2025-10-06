@extends('admin.layout')

@section('title', 'Attributes Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-gray-800">Attributes Management</h1>
                <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add New Attribute
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
                    <h6 class="m-0 font-weight-bold text-primary">All Attributes</h6>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('admin.attributes.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" 
                                           name="search" 
                                           class="form-control" 
                                           placeholder="Search attributes..." 
                                           value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                @if(request('search'))
                                    <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-lg"></i> Clear
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Values Count</th>
                                    <th>Required</th>
                                    <th>Filterable</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attributes as $attribute)
                                    <tr>
                                        <td>{{ $attribute->id }}</td>
                                        <td>
                                            <strong>{{ $attribute->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $attribute->type === 'color' ? 'info' : ($attribute->type === 'size' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($attribute->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $attribute->attributeValues->count() }} values</span>
                                        </td>
                                        <td>
                                            @if($attribute->is_required)
                                                <span class="badge badge-danger">Required</span>
                                            @else
                                                <span class="badge badge-secondary">Optional</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attribute->is_filterable)
                                                <span class="badge badge-success">Yes</span>
                                            @else
                                                <span class="badge badge-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>{{ $attribute->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.attributes.show', $attribute) }}" 
                                                   class="btn btn-info btn-sm" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.attributes.edit', $attribute) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        onclick="confirmDelete({{ $attribute->id }}, '{{ $attribute->name }}')" 
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <p class="mb-0 text-muted">No attributes found.</p>
                                            @if(!request('search'))
                                                <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary mt-2">
                                                    Create Your First Attribute
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
                        {{ $attributes->links('admin.pagination.custom') }}
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
                <p>Are you sure you want to delete the attribute "<span id="deleteName"></span>"?</p>
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
/* Better table spacing */
.table td {
    vertical-align: middle;
}

.btn-group .btn {
    margin: 0 1px;
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
    document.getElementById('deleteForm').action = `/admin/attributes/${id}`;
    $('#deleteModal').modal('show');
}
</script>
@endpush