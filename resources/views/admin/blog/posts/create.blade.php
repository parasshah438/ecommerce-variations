@extends('admin.layout')

@section('title', 'Create Blog Post')
@section('breadcrumb-section', 'Blog')
@section('breadcrumb-page', 'Create Post')

@section('page-title', 'Create Blog Post')
@section('page-description', 'Write SEO-optimized content to drive organic traffic')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    #editor-container { height: 400px; }
    .seo-preview-card { background: #f8f9fa; border-radius: 8px; padding: 1rem; }
    .seo-preview-title { color: #1a0dab; font-size: 1.2rem; }
    .seo-preview-url { color: #006621; font-size: 0.85rem; }
    .seo-preview-desc { color: #545454; font-size: 0.9rem; }
    .character-count { font-size: 0.8rem; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.blog.posts.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required maxlength="255">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Auto-generated from title">
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="blog_category_id" class="form-label">Category</label>
                            <select class="form-select @error('blog_category_id') is-invalid @enderror" id="blog_category_id" name="blog_category_id">
                                <option value="">Uncategorized</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('blog_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('blog_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="author" class="form-label">Author</label>
                            <input type="text" class="form-control @error('author') is-invalid @enderror" id="author" name="author" value="{{ old('author') }}" placeholder="Author name">
                            @error('author') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt</label>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" rows="2" maxlength="500">{{ old('excerpt') }}</textarea>
                        @error('excerpt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text character-count">0/500 characters</div>
                    </div>

                    <!-- Hidden textarea for Quill content -->
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <input type="hidden" name="content" id="content-input" value="{{ old('content') }}">
                        <div id="editor-container">{!! old('content') !!}</div>
                        @error('content') <div class="text-danger mt-1"><small>{{ $message }}</small></div> @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="featured_image" class="form-label">Featured Image</label>
                            <input type="file" class="form-control @error('featured_image') is-invalid @enderror" id="featured_image" name="featured_image" accept="image/*">
                            @error('featured_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Recommended: 1200x630px. Max 5MB.</div>
                            <img id="featuredPreview" class="img-fluid rounded mt-2" style="max-height:150px;display:none">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2 pb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_trending" name="is_trending" value="1" {{ old('is_trending') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_trending"><i class="fas fa-fire text-danger"></i> Trending</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured"><i class="fas fa-star text-warning"></i> Featured</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-search me-2"></i>SEO Settings & Google Preview</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="meta_title" class="form-label">Meta Title</label>
                                <input type="text" class="form-control @error('meta_title') is-invalid @enderror" id="meta_title" name="meta_title" value="{{ old('meta_title') }}" maxlength="255">
                                @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text character-count" id="metaTitleCount">0/60 characters recommended</div>
                            </div>
                            <div class="mb-3">
                                <label for="meta_description" class="form-label">Meta Description</label>
                                <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="2" maxlength="320">{{ old('meta_description') }}</textarea>
                                @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text character-count" id="metaDescCount">0/160 characters recommended</div>
                            </div>
                            <div class="mb-3">
                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="keyword1, keyword2, keyword3">
                                @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="canonical_url" class="form-label">Canonical URL</label>
                                <input type="url" class="form-control @error('canonical_url') is-invalid @enderror" id="canonical_url" name="canonical_url" value="{{ old('canonical_url') }}" placeholder="https://example.com/original-post">
                                @error('canonical_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="og_image" class="form-label">OG Image (Social Share)</label>
                                <input type="file" class="form-control @error('og_image') is-invalid @enderror" id="og_image" name="og_image" accept="image/*">
                                @error('og_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">Recommended: 1200x630px for Facebook/Twitter cards.</div>
                                <img id="ogPreview" class="img-fluid rounded mt-2" style="max-height:120px;display:none">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Google Search Preview</label>
                            <div class="seo-preview-card">
                                <div class="seo-preview-title" id="seoPreviewTitle">{{ old('meta_title', 'Page Title') }}</div>
                                <div class="seo-preview-url" id="seoPreviewUrl">{{ url('/blog/') }}/<span id="seoPreviewSlug">your-post-slug</span></div>
                                <div class="seo-preview-desc" id="seoPreviewDesc">{{ old('meta_description', 'Page description that appears in search results...') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Post</button>
                        <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1,2,3,4,5,6,false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image', 'video'],
                ['blockquote', 'code-block'],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });

    // Sync Quill content to hidden textarea before form submit
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('content-input').value = quill.root.innerHTML;
    });

    // Auto-slug
    document.getElementById('title')?.addEventListener('input', function() {
        const slug = document.getElementById('slug');
        if (!slug.dataset.manuallyEdited) {
            slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
        // Update SEO preview
        document.getElementById('seoPreviewSlug').textContent = slug.value || 'your-post-slug';
    });
    document.getElementById('slug')?.addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
        document.getElementById('seoPreviewSlug').textContent = this.value || 'your-post-slug';
    });

    // Live SEO preview
    document.getElementById('meta_title')?.addEventListener('input', function() {
        document.getElementById('seoPreviewTitle').textContent = this.value || 'Page Title';
        document.getElementById('metaTitleCount').textContent = this.value.length + '/60 characters recommended';
    });
    document.getElementById('meta_description')?.addEventListener('input', function() {
        document.getElementById('seoPreviewDesc').textContent = this.value || 'Page description that appears in search results...';
        document.getElementById('metaDescCount').textContent = this.value.length + '/160 characters recommended';
    });

    // Image previews
    document.getElementById('featured_image')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('featuredPreview').src = ev.target.result;
                document.getElementById('featuredPreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    document.getElementById('og_image')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('ogPreview').src = ev.target.result;
                document.getElementById('ogPreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Excerpt char count
    document.getElementById('excerpt')?.addEventListener('input', function() {
        this.nextElementSibling.textContent = this.value.length + '/500 characters';
    });

    // Pre-populate excerpt char count
    document.addEventListener('DOMContentLoaded', function() {
        const excerpt = document.getElementById('excerpt');
        if (excerpt) excerpt.nextElementSibling.textContent = excerpt.value.length + '/500 characters';
    });
</script>
@endpush
