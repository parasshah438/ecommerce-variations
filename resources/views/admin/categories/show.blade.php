@extends('admin.layout')

@section('title', 'Category Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">{{ $category->name }}</h1>
                    <p class="text-muted mb-0">Category details and information</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Category
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Categories
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Category Name</label>
                            <div class="fw-bold">{{ $category->name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Slug</label>
                            <div class="fw-bold">{{ $category->slug }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-danger' }} fs-6">
                                    <i class="bi bi-{{ $category->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Parent Category</label>
                            <div class="fw-bold">
                                @if($category->parent)
                                    <a href="{{ route('admin.categories.show', $category->parent) }}" 
                                       class="text-decoration-none">
                                        <i class="bi bi-arrow-return-right"></i> {{ $category->parent->name }}
                                    </a>
                                @else
                                    <span class="text-muted">Root Category</span>
                                @endif
                            </div>
                        </div>
                        @if($category->description)
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted">Description</label>
                            <div class="fw-bold">{{ $category->description }}</div>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Created Date</label>
                            <div class="fw-bold">{{ $category->created_at->format('F d, Y \a\t g:i A') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Last Updated</label>
                            <div class="fw-bold">{{ $category->updated_at->format('F d, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border-end">
                                <div class="text-primary" style="font-size: 2.5rem;">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <h4 class="text-primary mb-1">{{ $category->products->count() }}</h4>
                                <small class="text-muted">Products</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border-end">
                                <div class="text-info" style="font-size: 2.5rem;">
                                    <i class="bi bi-folder"></i>
                                </div>
                                <h4 class="text-info mb-1">{{ $category->children->count() }}</h4>
                                <small class="text-muted">Subcategories</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-success" style="font-size: 2.5rem;">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <h4 class="text-success mb-1">{{ $category->created_at->diffInDays(now()) }}</h4>
                            <small class="text-muted">Days Old</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subcategories -->
            @if($category->children->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-folder me-2"></i>Subcategories ({{ $category->children->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($category->children as $child)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                @if($child->image)
                                    <img src="{{ $child->image_url }}" alt="{{ $child->name }}" 
                                         class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-light rounded me-3" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-folder text-muted"></i>
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $child->name }}</h6>
                                    <small class="text-muted">{{ $child->products->count() }} products</small>
                                </div>
                                <div class="ms-2">
                                    <a href="{{ route('admin.categories.show', $child) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Products -->
            @if($category->products->count() > 0)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam me-2"></i>Products ({{ $category->products->count() }})
                    </h5>
                    <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" 
                       class="btn btn-sm btn-outline-primary">
                        View All Products
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($category->products->take(6) as $product)
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm">
                                @php $thumbnailImage = $product->getThumbnailImage(); @endphp
                                @if($thumbnailImage)
                                    <img src="{{ $thumbnailImage->getThumbnailUrl(150) }}" 
                                         class="card-img-top" style="height: 150px; object-fit: cover;"
                                         alt="{{ $product->name }}"
                                         loading="lazy"
                                         onerror="this.src='{{ asset('images/product-placeholder.jpg') }}';">>
                                @else
                                    <div class="card-img-top d-flex align-items-center justify-content-center bg-light" 
                                         style="height: 150px;">
                                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                    </div>
                                @endif
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-1">{{ Str::limit($product->name, 30) }}</h6>
                                    <p class="card-text small text-muted mb-2">
                                        ₹{{ number_format($product->price, 2) }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">{{ $product->variations->count() }} variations</small>
                                        <a href="{{ route('admin.products.show', $product) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($category->products->count() > 6)
                    <div class="text-center mt-3">
                        <p class="text-muted">Showing 6 of {{ $category->products->count() }} products</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Category Image -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-image me-2"></i>Category Image
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($category->image)
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" 
                             class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                    @else
                        <div class="border border-dashed rounded p-5 bg-light">
                            <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3 mb-0">No image uploaded</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.categories.edit', $category) }}" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> Edit Category
                        </a>
                        <a href="{{ route('admin.products.create', ['category' => $category->id]) }}" 
                           class="btn btn-outline-success">
                            <i class="bi bi-plus-circle me-1"></i> Add Product
                        </a>
                        @if($category->children->count() == 0 && $category->products->count() == 0)
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash me-1"></i> Delete Category
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Category Hierarchy -->
            @if($category->parent || $category->children->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-diagram-3 me-2"></i>Category Hierarchy
                    </h5>
                </div>
                <div class="card-body">
                    @if($category->parent)
                    <div class="mb-3">
                        <label class="form-label text-muted small">Parent Category</label>
                        <div>
                            <a href="{{ route('admin.categories.show', $category->parent) }}" 
                               class="text-decoration-none">
                                <i class="bi bi-arrow-up"></i> {{ $category->parent->name }}
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small">Current Category</label>
                        <div class="fw-bold text-primary">
                            <i class="bi bi-folder"></i> {{ $category->name }}
                        </div>
                    </div>
                    
                    @if($category->children->count() > 0)
                    <div>
                        <label class="form-label text-muted small">Subcategories</label>
                        @foreach($category->children as $child)
                        <div class="mb-1">
                            <a href="{{ route('admin.categories.show', $child) }}" 
                               class="text-decoration-none">
                                <i class="bi bi-arrow-down"></i> {{ $child->name }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.border-dashed {
    border-style: dashed !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.hover-effect:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.text-decoration-none:hover {
    text-decoration: underline !important;
}
</style>
@endsection