@extends('admin.layout')

@section('title', 'Categories Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">Categories Management</h1>
                    <p class="text-muted mb-0">Manage your product categories</p>
                </div>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add New Category
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.categories.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Search categories...">
                </div>
                <div class="col-md-3">
                    <label for="parent_id" class="form-label">Parent Category</label>
                    <select class="form-select" id="parent_id" name="parent_id">
                        <option value="">All Categories</option>
                        <option value="root" {{ request('parent_id') == 'root' ? 'selected' : '' }}>Root Categories</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ request('parent_id') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories List -->
    @if($categories->count() > 0)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Categories ({{ $categories->total() }})</h5>
                <div class="d-flex gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="view" id="grid-view" checked>
                        <label class="btn btn-outline-primary" for="grid-view">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </label>
                        <input type="radio" class="btn-check" name="view" id="list-view">
                        <label class="btn btn-outline-primary" for="list-view">
                            <i class="bi bi-list"></i>
                        </label>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Grid View -->
                <div id="grid-container" class="p-3">
                    <div class="row g-4">
                        @foreach($categories as $category)
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 shadow-sm border-0 hover-card">
                                    <div class="position-relative">
                                        @if($category->image)
                                            <img src="{{ $category->image_url }}" 
                                                 class="card-img-top" 
                                                 style="height: 200px; object-fit: cover;"
                                                 alt="{{ $category->name }}">
                                        @else
                                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" 
                                                 style="height: 200px;">
                                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                        @endif
                                        
                                        <!-- Status Badge -->
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                        
                                        <!-- Parent Badge -->
                                        @if($category->parent)
                                            <div class="position-absolute top-0 start-0 m-2">
                                                <span class="badge bg-info">
                                                    <i class="bi bi-arrow-return-right"></i> {{ $category->parent->name }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">{{ $category->name }}</h5>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.categories.show', $category) }}">
                                                            <i class="bi bi-eye me-1"></i> View
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.categories.edit', $category) }}">
                                                            <i class="bi bi-pencil me-1"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('admin.categories.destroy', $category) }}" 
                                                              method="POST" class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash me-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        @if($category->description)
                                            <p class="card-text text-muted small">
                                                {{ Str::limit($category->description, 100) }}
                                            </p>
                                        @endif
                                        
                                        <div class="row text-center border-top pt-3 mt-3">
                                            <div class="col-6">
                                                <div class="text-primary h5 mb-1">{{ $category->products->count() }}</div>
                                                <small class="text-muted">Products</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-info h5 mb-1">{{ $category->children->count() }}</div>
                                                <small class="text-muted">Subcategories</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- List View (Initially Hidden) -->
                <div id="list-container" class="table-responsive" style="display: none;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Parent</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>
                                        @if($category->image)
                                            <img src="{{ $category->image_url }}" 
                                                 class="rounded" 
                                                 style="width: 50px; height: 50px; object-fit: cover;"
                                                 alt="{{ $category->name }}">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center bg-light rounded" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $category->name }}</strong>
                                            @if($category->description)
                                                <br><small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($category->parent)
                                            <span class="badge bg-info">{{ $category->parent->name }}</span>
                                        @else
                                            <span class="text-muted">Root</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $category->products->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $category->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.categories.show', $category) }}" 
                                               class="btn btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.categories.edit', $category) }}" 
                                               class="btn btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.categories.destroy', $category) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            @if($categories->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} categories
                        </div>
                        <div>
                            {{ $categories->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-folder-x text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3">No Categories Found</h5>
                <p class="text-muted">Start by creating your first category.</p>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add New Category
                </a>
            </div>
        </div>
    @endif
</div>

<style>
.hover-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Category-specific pagination improvements */
.card-footer .pagination {
    margin-bottom: 0;
}

.card-footer .pagination .page-link {
    border-color: #e9ecef;
}

.card-footer .pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Responsive adjustments for category cards */
@media (max-width: 768px) {
    .card-footer .d-flex {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .card-footer .pagination {
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const gridContainer = document.getElementById('grid-container');
    const listContainer = document.getElementById('list-container');

    gridView.addEventListener('change', function() {
        if (this.checked) {
            gridContainer.style.display = 'block';
            listContainer.style.display = 'none';
        }
    });

    listView.addEventListener('change', function() {
        if (this.checked) {
            gridContainer.style.display = 'none';
            listContainer.style.display = 'block';
        }
    });
});
</script>
@endsection