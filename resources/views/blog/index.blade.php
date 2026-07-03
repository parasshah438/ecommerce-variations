@extends('layouts.frontend')

@section('title', $activeCategory->name ?? 'Blog - ' . config('app.name'))

@push('styles')
<style>
    .blog-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        background: #fff;
    }
    .blog-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    }
    .blog-card .blog-image {
        height: 220px;
        object-fit: cover;
        width: 100%;
    }
    .blog-card .card-body {
        padding: 1.5rem;
    }
    .blog-card .card-title {
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .blog-card .card-text {
        color: #6c757d;
        font-size: 0.9rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .blog-meta {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .blog-meta i {
        margin-right: 4px;
    }
    .sidebar-widget {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .sidebar-widget h5 {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #0d6efd;
    }
    .trending-item {
        display: flex;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f1f1;
        transition: background 0.2s;
    }
    .trending-item:last-child { border-bottom: none; }
    .trending-item:hover { background: #f8f9fa; }
    .trending-number {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0d6efd;
        min-width: 30px;
        opacity: 0.4;
    }
    .trending-item .trending-title {
        font-weight: 600;
        font-size: 0.9rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .trending-item .trending-views {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .category-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .category-list li {
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f1f1;
    }
    .category-list li:last-child { border-bottom: none; }
    .category-list a {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #212529;
        text-decoration: none;
        transition: color 0.2s;
    }
    .category-list a:hover { color: #0d6efd; }
    .category-list .count {
        background: #e9ecef;
        border-radius: 20px;
        padding: 0.15rem 0.6rem;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .category-list li.active a { color: #0d6efd; font-weight: 700; }
    .category-list li.active .count { background: #0d6efd; color: #fff; }
    .search-box-blog {
        border: 2px solid #e9ecef;
        padding: 0.6rem 1.2rem;
        transition: border-color 0.2s;
    }
    .search-box-blog:focus {
        border-color: #0d6efd;
        outline: none;
        box-shadow: none;
    }
    .search-box-wrapper .input-group .btn {
        border-radius: 0 25px 25px 0 !important;
        border: 2px solid #0d6efd;
        padding: 0.6rem 1.2rem;
    }
    .search-box-wrapper .input-group .search-box-blog {
        border-radius: 25px 0 0 25px !important;
    }
    .search-clear-btn {
        right: 50px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        text-decoration: none;
        font-size: 0.8rem;
    }
    .search-clear-btn:hover {
        color: #dc3545 !important;
    }
    .hero-blog {
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
        color: white;
        padding: 3rem 0;
        border-radius: 16px;
        margin-bottom: 2rem;
    }
    .hero-blog h1 { font-weight: 800; font-size: 2.2rem; }
    .read-more-btn {
        border-radius: 25px;
        padding: 0.4rem 1.2rem;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('breadcrumb')
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Blog</li>
                @isset($activeCategory)
                    <li class="breadcrumb-item active">{{ $activeCategory->name }}</li>
                @endisset
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="container">
    <!-- Hero -->
    <div class="hero-blog text-center">
        <div class="container">
            <h1>Our Blog</h1>
            <p class="lead mb-0">Style tips, trend guides, and the latest in fashion — right from the experts.</p>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            @if(request('search'))
                <div class="mb-4">
                    <p class="text-muted">Showing results for: <strong>"{{ e(request('search')) }}"</strong></p>
                </div>
            @endif

            @if($posts->count() > 0)
                <div class="row g-4">
                    @foreach($posts as $post)
                    <div class="col-md-6">
                        <article class="blog-card">
                            <a href="{{ route('blog.show', $post) }}">
                                <img src="{{ $post->featured_image_url }}" 
                                     alt="{{ $post->title }}" 
                                     class="blog-image"
                                     loading="lazy"
                                     onerror="this.src='https://via.placeholder.com/600x400?text=No+Image'">
                            </a>
                            <div class="card-body">
                                <div class="blog-meta mb-2">
                                    <span><i class="bi bi-calendar3"></i> {{ $post->formatted_date }}</span>
                                    <span class="ms-3"><i class="bi bi-clock"></i> {{ $post->reading_time }}</span>
                                    @if($post->category)
                                        <span class="ms-3"><i class="bi bi-folder"></i> {{ $post->category->name }}</span>
                                    @endif
                                </div>
                                <h2 class="card-title">
                                    <a href="{{ route('blog.show', $post) }}" class="text-decoration-none text-dark">{{ $post->title }}</a>
                                </h2>
                                <p class="card-text">{{ $post->excerpt ?: strip_tags(substr($post->content ?? '', 0, 200)) }}</p>
                                <a href="{{ route('blog.show', $post) }}" class="btn btn-outline-primary read-more-btn">
                                    Read More <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-5">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-newspaper" style="font-size: 3rem; color: #dee2e6;"></i>
                    <h4 class="mt-3 text-muted">No posts found</h4>
                    <p class="text-muted">Check back soon for new articles.</p>
                    <a href="{{ route('blog.index') }}" class="btn btn-primary">View All Posts</a>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Search -->
            <div class="sidebar-widget">
                <h5><i class="bi bi-search me-2"></i>Search</h5>
                <form action="{{ route('blog.index') }}" method="GET" class="search-box-wrapper position-relative">
                    <div class="input-group">
                        <input type="text" name="search" class="search-box-blog form-control" placeholder="Search articles..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary" aria-label="Search">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    @if(request('search'))
                        <a href="{{ route('blog.index') }}" class="position-absolute small text-muted" style="right: 50px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Categories -->
            <div class="sidebar-widget">
                <h5><i class="bi bi-folder me-2"></i>Categories</h5>
                <ul class="category-list">
                    <li class="{{ !request('category') && !isset($activeCategory) ? 'active' : '' }}">
                        <a href="{{ route('blog.index') }}">
                            <span>All Posts</span>
                            <span class="count">{{ \App\Models\BlogPost::published()->count() }}</span>
                        </a>
                    </li>
                    @foreach($categories as $cat)
                    <li class="{{ (request('category') == $cat->slug || (isset($activeCategory) && $activeCategory->id == $cat->id)) ? 'active' : '' }}">
                        <a href="{{ route('blog.category', $cat) }}">
                            <span>{{ $cat->name }}</span>
                            <span class="count">{{ $cat->posts_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Trending Posts -->
            <div class="sidebar-widget">
                <h5><i class="bi bi-fire text-danger me-2"></i>Trending Now</h5>
                @forelse($trendingPosts as $index => $trending)
                <a href="{{ route('blog.show', $trending) }}" class="text-decoration-none text-dark">
                    <div class="trending-item">
                        <div class="trending-number">{{ $index + 1 }}</div>
                        <div>
                            <div class="trending-title">{{ $trending->title }}</div>
                            <div class="trending-views"><i class="bi bi-eye"></i> {{ number_format($trending->views_count) }} views</div>
                        </div>
                    </div>
                </a>
                @empty
                <p class="text-muted small mb-0">No trending posts yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
