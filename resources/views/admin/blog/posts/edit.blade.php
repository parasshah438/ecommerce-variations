@extends('admin.layout')

@section('title', 'Edit Blog Post')
@section('breadcrumb-section', 'Blog')
@section('breadcrumb-page', 'Edit Post')

@section('page-title', 'Edit Blog Post')
@section('page-description', 'Update ecommerce blog content, publishing details, media, and SEO metadata')

@section('page-actions')
    <div class="d-flex gap-2">
        @if($blogPost->status === 'published')
            <a href="{{ route('blog.show', $blogPost) }}" class="btn btn-outline-primary" target="_blank" rel="noopener">
                <i class="fas fa-eye"></i> View Live
            </a>
        @endif
        <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Posts
        </a>
    </div>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    #editor-container { min-height: 460px; }
    .ql-toolbar.ql-snow,
    .ql-container.ql-snow { border-color: var(--bs-border-color); }
    .blog-sticky-sidebar {
        top: 120px !important;
        z-index: 1 !important;
    }
    .blog-icon-box { width: 34px; height: 34px; }
    .blog-current-image,
    .blog-image-preview {
        width: 100%;
        max-height: 180px;
        object-fit: cover;
    }
    .blog-image-preview { display: none; }
    .blog-google-preview { background: #fff; }
    .blog-google-title { color: #1a0dab; font-size: 1.05rem; line-height: 1.3; }
    .blog-google-url { color: #006621; font-size: 0.82rem; word-break: break-all; }
    .blog-google-desc { color: #545454; font-size: 0.9rem; line-height: 1.45; }
</style>
@endpush

@section('content')
<form id="blogPostForm" action="{{ route('admin.blog.posts.update', $blogPost) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4 align-items-start">
        <div class="col-xl-8">
            <div class="card overflow-hidden">
                <div class="card-header bg-transparent">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <h5 class="card-title mb-1">Article Details</h5>
                            <p class="text-muted small mb-0">Update the editorial copy, product context, and storefront presentation.</p>
                        </div>
                        @switch($blogPost->status)
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
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label small fw-bold text-uppercase text-muted">Article Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fw-bold @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $blogPost->title) }}" required maxlength="255">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="slug" class="form-label small fw-bold text-uppercase text-muted">SEO Slug</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ url('/blog') }}/</span>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $blogPost->slug) }}">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="blog_category_id" class="form-label small fw-bold text-uppercase text-muted">Content Category</label>
                            <select class="form-select @error('blog_category_id') is-invalid @enderror" id="blog_category_id" name="blog_category_id">
                                <option value="">Uncategorized</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('blog_category_id', $blogPost->blog_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('blog_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="author" class="form-label small fw-bold text-uppercase text-muted">Author</label>
                            <input type="text" class="form-control @error('author') is-invalid @enderror" id="author" name="author" value="{{ old('author', $blogPost->author) }}">
                            @error('author') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="excerpt" class="form-label small fw-bold text-uppercase text-muted">Short Ecommerce Summary</label>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" rows="3" maxlength="500">{{ old('excerpt', $blogPost->excerpt) }}</textarea>
                        @error('excerpt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text small" id="excerptCount">{{ strlen(old('excerpt', $blogPost->excerpt ?? '')) }}/500 characters</div>
                    </div>

                    <div class="mb-4">
                        <label for="content" class="form-label small fw-bold text-uppercase text-muted">Article Content</label>
                        <input type="hidden" name="content" id="content-input" value="{{ old('content', $blogPost->content) }}">
                        <div id="editor-container">{!! old('content', $blogPost->content) !!}</div>
                        @error('content') <div class="text-danger mt-1"><small>{{ $message }}</small></div> @enderror
                    </div>

                    <div class="card border">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-1"><i class="fas fa-search me-2"></i>SEO Settings</h5>
                            <p class="text-muted small mb-0">Control search snippets, canonical hints, and social card presentation.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label small fw-bold text-uppercase text-muted">Meta Title</label>
                                        <input type="text" class="form-control @error('meta_title') is-invalid @enderror" id="meta_title" name="meta_title" value="{{ old('meta_title', $blogPost->meta_title) }}" maxlength="255">
                                        @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <div class="form-text small" id="metaTitleCount">{{ strlen(old('meta_title', $blogPost->meta_title ?? '')) }}/60 characters recommended</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label small fw-bold text-uppercase text-muted">Meta Description</label>
                                        <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="3" maxlength="320">{{ old('meta_description', $blogPost->meta_description) }}</textarea>
                                        @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <div class="form-text small" id="metaDescCount">{{ strlen(old('meta_description', $blogPost->meta_description ?? '')) }}/160 characters recommended</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="meta_keywords" class="form-label small fw-bold text-uppercase text-muted">Meta Keywords</label>
                                        <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $blogPost->meta_keywords) }}" placeholder="style guide, buying tips, ecommerce">
                                        @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div>
                                        <label for="canonical_url" class="form-label small fw-bold text-uppercase text-muted">Canonical URL</label>
                                        <input type="url" class="form-control @error('canonical_url') is-invalid @enderror" id="canonical_url" name="canonical_url" value="{{ old('canonical_url', $blogPost->canonical_url) }}" placeholder="https://example.com/original-post">
                                        @error('canonical_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label small fw-bold text-uppercase text-muted">Google Search Preview</label>
                                    <div class="blog-google-preview border rounded p-3">
                                        <div class="blog-google-title" id="seoPreviewTitle">{{ old('meta_title', $blogPost->meta_title ?: $blogPost->title) }}</div>
                                        <div class="blog-google-url my-1">{{ url('/blog/') }}/<span id="seoPreviewSlug">{{ old('slug', $blogPost->slug) }}</span></div>
                                        <div class="blog-google-desc" id="seoPreviewDesc">{{ old('meta_description', $blogPost->meta_description ?: $blogPost->excerpt ?: 'Blog post description...') }}</div>
                                    </div>
                                    <div class="mt-3">
                                        <label for="og_image" class="form-label small fw-bold text-uppercase text-muted">Social Share Image</label>
                                        <div class="border border-secondary-subtle rounded p-3 bg-body-tertiary">
                                            @if($blogPost->og_image)
                                                <img src="{{ $blogPost->og_image_url }}" class="blog-current-image rounded border mb-3" alt="Current social image">
                                            @endif
                                            <input type="file" class="form-control @error('og_image') is-invalid @enderror" id="og_image" name="og_image" accept="image/*">
                                            @error('og_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            <div class="form-text small">Upload a new image to replace the current social card.</div>
                                            <img id="ogPreview" class="blog-image-preview rounded border mt-3" alt="Social image preview">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-transparent d-flex justify-content-between gap-3">
                    <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Post
                    </button>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="d-flex flex-column gap-3 sticky-xl-top blog-sticky-sidebar">
                <div class="card">
                    <div class="card-header bg-transparent fw-bold"><i class="fas fa-chart-simple me-2"></i>Post Snapshot</div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="border rounded p-3 bg-body-tertiary h-100">
                                    <div class="fs-5 fw-bold lh-1">{{ number_format($blogPost->views_count) }}</div>
                                    <div class="text-muted small mt-1">Views</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 bg-body-tertiary h-100">
                                    <div class="fs-6 fw-bold lh-1">{{ $blogPost->reading_time }}</div>
                                    <div class="text-muted small mt-1">Read Time</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-muted small">
                            Published date: <strong>{{ $blogPost->formatted_date }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-transparent fw-bold"><i class="fas fa-paper-plane me-2"></i>Publish Settings</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                <option value="draft" {{ old('status', $blogPost->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $blogPost->status) === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status', $blogPost->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <label class="border rounded p-3 d-flex align-items-center gap-3 mb-2" for="is_trending">
                            <input class="form-check-input m-0" type="checkbox" id="is_trending" name="is_trending" value="1" {{ old('is_trending', $blogPost->is_trending) ? 'checked' : '' }}>
                            <span class="blog-icon-box d-inline-flex align-items-center justify-content-center rounded bg-body-secondary text-danger flex-shrink-0"><i class="fas fa-fire"></i></span>
                            <span><span class="fw-bold d-block">Trending</span><span class="text-muted small">Promote in trending areas.</span></span>
                        </label>

                        <label class="border rounded p-3 d-flex align-items-center gap-3" for="is_featured">
                            <input class="form-check-input m-0" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $blogPost->is_featured) ? 'checked' : '' }}>
                            <span class="blog-icon-box d-inline-flex align-items-center justify-content-center rounded bg-body-secondary text-warning flex-shrink-0"><i class="fas fa-star"></i></span>
                            <span><span class="fw-bold d-block">Featured</span><span class="text-muted small">Highlight in priority content slots.</span></span>
                        </label>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-transparent fw-bold"><i class="fas fa-image me-2"></i>Featured Image</div>
                    <div class="card-body">
                        <div class="border border-secondary-subtle rounded p-3 bg-body-tertiary">
                            @if($blogPost->featured_image)
                                <img src="{{ $blogPost->featured_image_url }}" class="blog-current-image rounded border mb-3" alt="Current featured image">
                            @endif
                            <input type="file" class="form-control @error('featured_image') is-invalid @enderror" id="featured_image" name="featured_image" accept="image/*">
                            @error('featured_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">Upload a new image to replace the current featured image.</div>
                            <img id="featuredPreview" class="blog-image-preview rounded border mt-3" alt="Featured image preview">
                        </div>
                    </div>
                </div>

                <div class="card border-danger">
                    <div class="card-header bg-danger-subtle text-danger fw-bold"><i class="fas fa-triangle-exclamation me-2"></i>Danger Zone</div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Deleting this post permanently removes its content and uploaded images.</p>
                        <button type="submit" class="btn btn-outline-danger w-100" form="deletePostForm">
                            <i class="fas fa-trash"></i> Delete Post
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<form id="deletePostForm" action="{{ route('admin.blog.posts.destroy', $blogPost) }}" method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete this post? This cannot be undone.')">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    const quill = new Quill('#editor-container', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ indent: '-1' }, { indent: '+1' }],
                [{ color: [] }, { background: [] }],
                ['link', 'image', 'video'],
                ['blockquote', 'code-block'],
                [{ align: [] }],
                ['clean']
            ]
        }
    });

    document.getElementById('blogPostForm')?.addEventListener('submit', function() {
        document.getElementById('content-input').value = quill.root.innerHTML;
    });

    function makeSlug(value) {
        return value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }

    function setCount(elementId, value, suffix) {
        const target = document.getElementById(elementId);
        if (target) target.textContent = `${value.length}/${suffix}`;
    }

    function previewImage(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        input?.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (!file || !preview) return;
            const reader = new FileReader();
            reader.onload = function(readerEvent) {
                preview.src = readerEvent.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }

    const fallbackTitle = @json($blogPost->title);
    const fallbackSlug = @json($blogPost->slug);
    const fallbackDescription = @json($blogPost->excerpt ?: 'Blog post description...');

    document.getElementById('title')?.addEventListener('input', function() {
        const slug = document.getElementById('slug');
        if (slug && !slug.dataset.manuallyEdited) {
            slug.value = makeSlug(this.value);
        }
        document.getElementById('seoPreviewSlug').textContent = slug?.value || fallbackSlug;
        if (!document.getElementById('meta_title')?.value) {
            document.getElementById('seoPreviewTitle').textContent = this.value || fallbackTitle;
        }
    });

    document.getElementById('slug')?.addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
        document.getElementById('seoPreviewSlug').textContent = this.value || fallbackSlug;
    });

    document.getElementById('meta_title')?.addEventListener('input', function() {
        document.getElementById('seoPreviewTitle').textContent = this.value || document.getElementById('title')?.value || fallbackTitle;
        setCount('metaTitleCount', this.value, '60 characters recommended');
    });

    document.getElementById('meta_description')?.addEventListener('input', function() {
        document.getElementById('seoPreviewDesc').textContent = this.value || fallbackDescription;
        setCount('metaDescCount', this.value, '160 characters recommended');
    });

    document.getElementById('excerpt')?.addEventListener('input', function() {
        setCount('excerptCount', this.value, '500 characters');
    });

    document.addEventListener('DOMContentLoaded', function() {
        const excerpt = document.getElementById('excerpt');
        const metaTitle = document.getElementById('meta_title');
        const metaDescription = document.getElementById('meta_description');

        if (excerpt) setCount('excerptCount', excerpt.value, '500 characters');
        if (metaTitle) setCount('metaTitleCount', metaTitle.value, '60 characters recommended');
        if (metaDescription) setCount('metaDescCount', metaDescription.value, '160 characters recommended');
    });

    previewImage('featured_image', 'featuredPreview');
    previewImage('og_image', 'ogPreview');
</script>
@endpush
