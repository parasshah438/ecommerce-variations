@extends('admin.layout')

@section('title', 'Blog Posts')
@section('breadcrumb-section', 'Blog')
@section('breadcrumb-page', 'Posts')

@section('page-title', 'Blog Posts')
@section('page-description', 'Plan, publish, and optimize ecommerce content that supports product discovery')

@section('page-actions')
    <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Post
    </a>
@endsection

@push('styles')
<style>
    .post-thumb {
        width: 94px;
        height: 64px;
        object-fit: cover;
    }
    .blog-icon-btn {
        width: 34px;
        height: 34px;
        padding: 0;
    }
    .blog-slug-chip {
        max-width: 260px;
    }
    @media (max-width: 767.98px) {
        .post-thumb {
            width: 76px;
            height: 56px;
        }
    }
</style>
@endpush

@section('content')
@php
    $blogCategories = $categories ?? \App\Models\BlogCategory::orderBy('name')->get();
    $hasFilters = request()->filled('status') || request()->filled('category') || request()->filled('search');
@endphp

<div class="row g-3 mb-4">
    <div class="col-xl col-md-4 col-6">
        <div class="stats-card h-100">
            <div class="stats-icon primary"><i class="fas fa-newspaper"></i></div>
            <div class="stats-value">{{ number_format($stats['total']) }}</div>
            <div class="stats-label">Total Articles</div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-6">
        <div class="stats-card h-100">
            <div class="stats-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stats-value">{{ number_format($stats['published']) }}</div>
            <div class="stats-label">Published</div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-6">
        <div class="stats-card h-100">
            <div class="stats-icon warning"><i class="fas fa-pen-nib"></i></div>
            <div class="stats-value">{{ number_format($stats['drafts']) }}</div>
            <div class="stats-label">Draft Pipeline</div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-6">
        <div class="stats-card h-100">
            <div class="stats-icon danger"><i class="fas fa-fire"></i></div>
            <div class="stats-value">{{ number_format($stats['trending']) }}</div>
            <div class="stats-label">Trending</div>
        </div>
    </div>
    <div class="col-xl col-md-8 col-12">
        <div class="stats-card h-100">
            <div class="stats-icon primary"><i class="fas fa-chart-line"></i></div>
            <div class="stats-value">{{ number_format($stats['total_views']) }}</div>
            <div class="stats-label">Organic Content Views</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card overflow-hidden">
    <div class="card-header bg-transparent">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h5 class="card-title mb-0">Content Library</h5>
                    @if($hasFilters)
                        <span class="badge bg-info">Filtered</span>
                    @endif
                </div>
                <div class="text-muted small">
                    Showing {{ $posts->firstItem() ?? 0 }} to {{ $posts->lastItem() ?? 0 }} of {{ number_format($posts->total()) }} posts
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.blog.posts.index', array_filter(['status' => 'published', 'category' => request('category'), 'search' => request('search')])) }}"
                   class="btn btn-sm {{ request('status') === 'published' ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="fas fa-check-circle"></i> Published
                </a>
                <a href="{{ route('admin.blog.posts.index', array_filter(['status' => 'draft', 'category' => request('category'), 'search' => request('search')])) }}"
                   class="btn btn-sm {{ request('status') === 'draft' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="fas fa-pen-nib"></i> Drafts
                </a>
                <a href="{{ route('admin.blog.posts.index', array_filter(['status' => 'archived', 'category' => request('category'), 'search' => request('search')])) }}"
                   class="btn btn-sm {{ request('status') === 'archived' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    <i class="fas fa-box-archive"></i> Archived
                </a>
            </div>
        </div>
    </div>

    <div class="card-body border-bottom bg-body-tertiary">
        <form action="{{ route('admin.blog.posts.index') }}" method="GET">
            <div class="row g-2 align-items-center">
                <div class="col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="search" name="search" class="form-control" placeholder="Search articles, guides, buying tips..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($blogCategories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary flex-fill" type="submit">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                        @if($hasFilters)
                            <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-outline-secondary" title="Clear filters">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <form id="bulkForm" action="{{ route('admin.blog.posts.bulk-action') }}" method="POST">
        @csrf
    </form>

    <div class="card-body border-bottom py-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <select name="action" class="form-select form-select-sm" form="bulkForm" required style="width: 190px;">
                    <option value="">Bulk Actions</option>
                    <option value="publish">Publish selected</option>
                    <option value="draft">Move to draft</option>
                    <option value="archive">Archive</option>
                    <option value="trending">Mark trending</option>
                    <option value="notrending">Remove trending</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary" form="bulkForm" onclick="return confirm('Apply bulk action to selected posts?')">
                    Apply
                </button>
                <span class="text-muted small" id="selectedCount">No posts selected</span>
            </div>
            <div class="text-muted small">
                <i class="fas fa-layer-group me-1"></i>{{ number_format($posts->count()) }} posts on this page
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 44px;">
                        <input type="checkbox" id="selectAll" class="form-check-input" title="Select all posts on this page">
                    </th>
                    <th>Article</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th class="text-center">Signals</th>
                    <th class="text-center">Views</th>
                    <th>Owner</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>
                            <input type="checkbox" name="ids[]" value="{{ $post->id }}" class="form-check-input row-checkbox" form="bulkForm">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="post-thumb rounded border flex-shrink-0" loading="lazy">
                                <div class="min-w-0">
                                    <div class="fw-bold mb-1">{{ \Illuminate\Support\Str::limit($post->title, 78) }}</div>
                                    @if($post->excerpt)
                                        <div class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit(strip_tags($post->excerpt), 118) }}</div>
                                    @endif
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis d-inline-flex align-items-center gap-1 text-truncate blog-slug-chip">
                                            <i class="fas fa-link"></i>
                                            <span class="text-truncate">{{ $post->slug }}</span>
                                        </span>
                                        <span class="text-muted small">
                                            <i class="far fa-clock me-1"></i>{{ $post->reading_time }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($post->category)
                                <span class="badge bg-primary-subtle text-primary-emphasis">{{ $post->category->name }}</span>
                            @else
                                <span class="text-muted small">Uncategorized</span>
                            @endif
                        </td>
                        <td>
                            @switch($post->status)
                                @case('published')
                                    <span class="badge rounded-pill bg-success-subtle text-success"><i class="fas fa-circle-check me-1"></i> Published</span>
                                    @break
                                @case('draft')
                                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis"><i class="fas fa-pen me-1"></i> Draft</span>
                                    @break
                                @case('archived')
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis"><i class="fas fa-box-archive me-1"></i> Archived</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                <form action="{{ route('admin.blog.posts.toggle-trending', $post) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm blog-icon-btn d-inline-flex align-items-center justify-content-center {{ $post->is_trending ? 'btn-danger' : 'btn-outline-secondary' }}" title="{{ $post->is_trending ? 'Remove trending' : 'Mark trending' }}">
                                        <i class="fas fa-fire"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.blog.posts.toggle-featured', $post) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm blog-icon-btn d-inline-flex align-items-center justify-content-center {{ $post->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}" title="{{ $post->is_featured ? 'Remove featured' : 'Mark featured' }}">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="fw-bold">{{ number_format($post->views_count) }}</div>
                            <div class="text-muted small">views</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $post->author ?: 'Admin' }}</div>
                            <div class="text-muted small">{{ $post->formatted_date }}</div>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                @if($post->status === 'published')
                                    <a href="{{ route('blog.show', $post) }}" class="btn btn-sm btn-outline-secondary blog-icon-btn d-inline-flex align-items-center justify-content-center" title="View live post" target="_blank" rel="noopener">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.blog.posts.edit', $post) }}" class="btn btn-sm btn-outline-primary blog-icon-btn d-inline-flex align-items-center justify-content-center" title="Edit post">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.blog.posts.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger blog-icon-btn d-inline-flex align-items-center justify-content-center" title="Delete post">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="mx-auto mb-3 rounded bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="fas fa-newspaper fs-4"></i>
                            </div>
                            <h5 class="mb-2">No blog posts found</h5>
                            <p class="text-muted mb-3">
                                @if($hasFilters)
                                    No posts match the selected filters.
                                @else
                                    Start building your ecommerce content library with buying guides, style edits, and SEO articles.
                                @endif
                            </p>
                            @if($hasFilters)
                                <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            @else
                                <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Write First Post
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($posts->hasPages())
        <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div class="text-muted small">
                Showing {{ $posts->firstItem() ?? 0 }} to {{ $posts->lastItem() ?? 0 }} of {{ number_format($posts->total()) }} results
            </div>
            <div>
                {{ $posts->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const bulkForm = document.getElementById('bulkForm');

    function updateSelectedCount() {
        const count = document.querySelectorAll('.row-checkbox:checked').length;
        if (selectedCount) {
            selectedCount.textContent = count === 0 ? 'No posts selected' : `${count} post${count === 1 ? '' : 's'} selected`;
        }
        if (selectAll) {
            selectAll.checked = rowCheckboxes.length > 0 && count === rowCheckboxes.length;
            selectAll.indeterminate = count > 0 && count < rowCheckboxes.length;
        }
    }

    selectAll?.addEventListener('change', function() {
        rowCheckboxes.forEach(cb => cb.checked = this.checked);
        updateSelectedCount();
    });

    rowCheckboxes.forEach(cb => cb.addEventListener('change', updateSelectedCount));

    bulkForm?.addEventListener('submit', function(event) {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) {
            event.preventDefault();
            alert('Please select at least one post.');
        }
    });

    updateSelectedCount();
</script>
@endpush
