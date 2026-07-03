@extends('admin.layout')

@section('title', 'Blog Posts')
@section('breadcrumb-section', 'Blog')
@section('breadcrumb-page', 'Posts')

@section('page-title', 'Blog Posts')
@section('page-description', 'Create and manage SEO-optimized blog content for organic traffic')

@section('page-actions')
    <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Post
    </a>
@endsection

@push('styles')
<style>
    .stats-card-small { padding: 1.25rem; }
    .stats-card-small .stats-value { font-size: 1.5rem; }
</style>
@endpush

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="stats-card">
            <div class="stats-icon primary"><i class="fas fa-file-alt"></i></div>
            <div class="stats-value">{{ $stats['total'] }}</div>
            <div class="stats-label">Total Posts</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stats-card">
            <div class="stats-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stats-value">{{ $stats['published'] }}</div>
            <div class="stats-label">Published</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stats-card">
            <div class="stats-icon warning"><i class="fas fa-pen-fancy"></i></div>
            <div class="stats-value">{{ $stats['drafts'] }}</div>
            <div class="stats-label">Drafts</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stats-card">
            <div class="stats-icon danger"><i class="fas fa-fire"></i></div>
            <div class="stats-value">{{ $stats['trending'] }}</div>
            <div class="stats-label">Trending</div>
        </div>
    </div>
    <div class="col-md-4 col-12">
        <div class="stats-card">
            <div class="stats-icon info"><i class="fas fa-eye"></i></div>
            <div class="stats-value">{{ number_format($stats['total_views']) }}</div>
            <div class="stats-label">Total Views</div>
        </div>
    </div>
</div>

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

                <!-- Bulk Actions -->
                <form id="bulkForm" action="{{ route('admin.blog.posts.bulk-action') }}" method="POST" class="mb-3">
                    @csrf
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <select name="action" class="form-select form-select-sm" required>
                                <option value="">Bulk Actions</option>
                                <option value="publish">Publish</option>
                                <option value="draft">Move to Draft</option>
                                <option value="archive">Archive</option>
                                <option value="trending">Mark Trending</option>
                                <option value="notrending">Remove Trending</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Apply bulk action?')">Apply</button>
                        </div>
                        <div class="col-auto ms-auto">
                            <form action="{{ route('admin.blog.posts.index') }}" method="GET" class="d-flex gap-2">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach(\App\Models\BlogCategory::orderBy('name')->get() as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search posts..." value="{{ request('search') }}" style="width:200px">
                                <button class="btn btn-sm btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="30"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th class="text-center"><i class="fas fa-fire"></i> Trending</th>
                                <th class="text-center"><i class="fas fa-star"></i> Featured</th>
                                <th class="text-center"><i class="fas fa-eye"></i> Views</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($posts as $post)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $post->id }}" class="form-check-input row-checkbox"></td>
                                <td>
                                    <strong>{{ $post->title }}</strong>
                                    <br><small class="text-muted"><code>{{ $post->slug }}</code></small>
                                </td>
                                <td>{{ $post->category?->name ?? '-' }}</td>
                                <td>
                                    @switch($post->status)
                                        @case('published') <span class="badge bg-success">Published</span> @break
                                        @case('draft') <span class="badge bg-warning text-dark">Draft</span> @break
                                        @case('archived') <span class="badge bg-secondary">Archived</span> @break
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.blog.posts.toggle-trending', $post) }}" class="btn btn-sm {{ $post->is_trending ? 'btn-danger' : 'btn-outline-secondary' }}" title="{{ $post->is_trending ? 'Remove Trending' : 'Mark Trending' }}">
                                        <i class="fas fa-fire"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.blog.posts.toggle-featured', $post) }}" class="btn btn-sm {{ $post->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}" title="{{ $post->is_featured ? 'Remove Featured' : 'Mark Featured' }}">
                                        <i class="fas fa-star"></i>
                                    </a>
                                </td>
                                <td class="text-center">{{ number_format($post->views_count) }}</td>
                                <td>{{ $post->author ?? '-' }}</td>
                                <td><small>{{ $post->formatted_date }}</small></td>
                                <td>
                                    <a href="{{ route('admin.blog.posts.edit', $post) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.blog.posts.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this post?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="fas fa-newspaper fa-2x mb-2 d-block"></i>
                                    No blog posts found.
                                    <a href="{{ route('admin.blog.posts.create') }}" class="d-block mt-2">Write your first post</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                </form>

                <div class="d-flex justify-content-center mt-3">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
    });
    document.getElementById('bulkForm')?.addEventListener('submit', function() {
        const checked = this.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) {
            alert('Please select at least one post.');
            return false;
        }
        // Move checked IDs into hidden inputs
        document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            this.appendChild(input);
        });
    });
</script>
@endpush
