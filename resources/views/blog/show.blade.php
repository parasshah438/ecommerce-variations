@extends('layouts.frontend')

@section('title', $blogPost->meta_title)

@section('meta')
    <meta name="description" content="{{ $blogPost->meta_description }}">
    <meta name="keywords" content="{{ $blogPost->meta_keywords }}">
    @if($blogPost->canonical_url)
        <link rel="canonical" href="{{ $blogPost->canonical_url }}">
    @endif
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $blogPost->meta_title }}">
    <meta property="og:description" content="{{ $blogPost->meta_description }}">
    <meta property="og:image" content="{{ $blogPost->og_image_url }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">
    @if($blogPost->published_at)
        <meta property="article:published_time" content="{{ $blogPost->published_at->toIso8601String() }}">
    @endif
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $blogPost->meta_title }}">
    <meta name="twitter:description" content="{{ $blogPost->meta_description }}">
    <meta name="twitter:image" content="{{ $blogPost->og_image_url }}">
@endsection

@section('styles')
<style>
    .blog-article {
        max-width: 800px;
        margin: 0 auto;
    }
    .blog-article .featured-image {
        width: 100%;
        max-height: 500px;
        object-fit: cover;
        border-radius: 16px;
        margin-bottom: 2rem;
    }
    .blog-article h1 {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 1rem;
    }
    .blog-article .article-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        color: #6c757d;
        font-size: 0.95rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }
    .blog-article .article-content {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #212529;
    }
    .blog-article .article-content p {
        margin-bottom: 1.5rem;
    }
    .blog-article .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 1.5rem 0;
    }
    .blog-article .article-content h2 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-top: 2.5rem;
        margin-bottom: 1rem;
    }
    .blog-article .article-content h3 {
        font-size: 1.4rem;
        font-weight: 700;
        margin-top: 2rem;
        margin-bottom: 0.75rem;
    }
    .blog-article .article-content blockquote {
        border-left: 4px solid #0d6efd;
        padding-left: 1.5rem;
        margin: 1.5rem 0;
        font-style: italic;
        color: #6c757d;
    }
    .blog-article .article-content ul, .blog-article .article-content ol {
        margin-bottom: 1.5rem;
    }
    .blog-article .article-content li {
        margin-bottom: 0.5rem;
    }
    .blog-article .article-content a {
        color: #0d6efd;
        text-decoration: underline;
    }
    .blog-article .article-content a:hover { color: #0a58ca; }
    .share-buttons {
        display: flex;
        gap: 0.75rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
    }
    .share-buttons .btn {
        border-radius: 50px;
        padding: 0.5rem 1.2rem;
    }
    .related-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        background: #fff;
    }
    .related-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .related-card .related-image {
        height: 180px;
        object-fit: cover;
        width: 100%;
    }
    .related-card .card-body { padding: 1rem; }
    .related-card .card-title {
        font-size: 1rem;
        font-weight: 700;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 0.5rem;
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
    @media (max-width: 768px) {
        .blog-article h1 { font-size: 1.8rem; }
        .blog-article .article-content { font-size: 1rem; }
    }
</style>
@endsection

@section('breadcrumb')
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
                @if($blogPost->category)
                    <li class="breadcrumb-item"><a href="{{ route('blog.category', $blogPost->category) }}">{{ $blogPost->category->name }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ $blogPost->title }}</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <!-- Main Article -->
        <div class="col-lg-8">
            <article class="blog-article">
                @if($blogPost->featured_image)
                    <img src="{{ $blogPost->featured_image_url }}" 
                         alt="{{ $blogPost->title }}" 
                         class="featured-image"
                         loading="lazy"
                         onerror="this.src='https://via.placeholder.com/1200x630?text=No+Image'">
                @endif

                <h1>{{ $blogPost->title }}</h1>

                <div class="article-meta">
                    <span><i class="bi bi-person"></i> {{ $blogPost->author ?? 'Admin' }}</span>
                    <span><i class="bi bi-calendar3"></i> {{ $blogPost->formatted_date }}</span>
                    <span><i class="bi bi-clock"></i> {{ $blogPost->reading_time }}</span>
                    @if($blogPost->category)
                        <span><i class="bi bi-folder"></i> {{ $blogPost->category->name }}</span>
                    @endif
                    <span><i class="bi bi-eye"></i> {{ number_format($blogPost->views_count) }} views</span>
                </div>

                @if($blogPost->excerpt)
                    <p class="lead text-muted mb-4">{{ $blogPost->excerpt }}</p>
                @endif

                <div class="article-content">
                    {!! $blogPost->content !!}
                </div>

                <!-- Share Buttons -->
                <div class="share-buttons">
                    <span class="fw-bold me-2 align-self-center">Share:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-facebook me-1"></i>Facebook</a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($blogPost->title) }}" target="_blank" class="btn btn-outline-dark btn-sm"><i class="bi bi-twitter me-1"></i>Twitter</a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($blogPost->title) }}" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-linkedin me-1"></i>LinkedIn</a>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($blogPost->title . ' - ' . url()->current()) }}" target="_blank" class="btn btn-outline-success btn-sm"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
                </div>

                @if($blogPost->meta_keywords)
                    <div class="mt-3">
                        @foreach(explode(',', $blogPost->meta_keywords) as $keyword)
                            <span class="badge bg-light text-dark me-1">#{{ trim($keyword) }}</span>
                        @endforeach
                    </div>
                @endif
            </article>

            <!-- Related Posts -->
            @if($relatedPosts->count() > 0)
            <div class="mt-5">
                <h3 class="fw-bold mb-4">Related Articles</h3>
                <div class="row g-4">
                    @foreach($relatedPosts as $related)
                    <div class="col-md-6">
                        <a href="{{ route('blog.show', $related) }}" class="text-decoration-none text-dark">
                            <div class="related-card">
                                <img src="{{ $related->featured_image_url }}" 
                                     alt="{{ $related->title }}" 
                                     class="related-image"
                                     loading="lazy"
                                     onerror="this.src='https://via.placeholder.com/600x400?text=No+Image'">
                                <div class="card-body">
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-calendar3"></i> {{ $related->formatted_date }}
                                    </div>
                                    <h4 class="card-title">{{ $related->title }}</h4>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Trending Widget -->
            <div class="sidebar-widget">
                <h5><i class="bi bi-fire text-danger me-2"></i>Trending Now</h5>
                @forelse($trendingPosts as $index => $trending)
                <a href="{{ route('blog.show', $trending) }}" class="text-decoration-none text-dark">
                    <div class="trending-item">
                        <div class="trending-number">{{ $index + 1 }}</div>
                        <div>
                            <div class="trending-title">{{ $trending->title }}</div>
                        </div>
                    </div>
                </a>
                @empty
                <p class="text-muted small mb-0">No trending posts yet.</p>
                @endforelse
            </div>

            <!-- Categories Widget -->
            <div class="sidebar-widget">
                <h5><i class="bi bi-folder me-2"></i>Categories</h5>
                <ul class="list-unstyled mb-0">
                    @foreach(\App\Models\BlogCategory::withCount('posts')->orderBy('name')->get() as $cat)
                    <li class="py-1">
                        <a href="{{ route('blog.category', $cat) }}" class="text-decoration-none d-flex justify-content-between">
                            <span>{{ $cat->name }}</span>
                            <span class="badge bg-light text-dark">{{ $cat->posts_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
