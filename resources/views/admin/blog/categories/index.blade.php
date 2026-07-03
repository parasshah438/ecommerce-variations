@extends('admin.layout')

@section('title', 'Blog Categories')
@section('breadcrumb-section', 'Blog')
@section('breadcrumb-page', 'Categories')

@section('page-title', 'Blog Categories')
@section('page-description', 'Manage blog post categories for SEO-driven content')

@section('page-actions')
    <a href="{{ route('admin.blog.categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Category
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Posts</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td><strong>{{ $category->name }}</strong></td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td><span class="badge bg-info">{{ $category->posts_count ?? $category->post_count }}</span></td>
                                <td>{{ $category->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.blog.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.blog.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category? Posts will be uncategorized.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                    No blog categories yet.
                                    <a href="{{ route('admin.blog.categories.create') }}" class="d-block mt-2">Create your first category</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
