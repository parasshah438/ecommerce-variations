@extends('admin.layout')

@section('title', 'Review Details')
@section('page-title', 'Review Details')
@section('page-description', 'Review is live on the storefront; use actions below to reject or report if needed')
@section('breadcrumb-section', 'Admin')
@section('breadcrumb-page', 'Review #' . $review->id)

@section('page-actions')
    <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Reviews
    </a>
    @if($review->product)
        <a href="{{ route('products.show', $review->product->slug) }}" target="_blank" class="btn btn-outline-primary">
            <i class="fas fa-external-link-alt"></i> Open Product Page
        </a>
        <a href="{{ route('admin.products.show', $review->product) }}" class="btn btn-primary">
            <i class="fas fa-box"></i> Admin Product Details
        </a>
    @endif
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Review Content</h5>
                <span class="badge {{ $review->statusBadgeClass() }}">{{ $review->statusLabel() }}</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-warning fs-5 mb-2">
                        @for($s = 1; $s <= 5; $s++)
                            <i class="fas fa-star{{ $s <= $review->rating ? '' : '-o' }}"></i>
                        @endfor
                        <span class="text-dark fs-6 ms-2">{{ $review->rating }} out of 5</span>
                    </div>
                    @if($review->title)
                        <h4 class="mb-2">{{ $review->title }}</h4>
                    @endif
                    <p class="mb-0 text-muted">{{ $review->comment ?: 'No comment provided.' }}</p>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Reviewed by</div>
                        @if($review->user)
                            <div class="fw-semibold">{{ $review->user->name }}</div>
                            <div class="text-muted small">{{ $review->user->email }}</div>
                            <a href="{{ route('admin.users.show', $review->user) }}" class="small">View customer profile</a>
                        @else
                            <span class="text-muted">User account removed</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Submitted on</div>
                        <div class="fw-semibold">{{ $review->created_at->format('l, d F Y') }}</div>
                        <div class="text-muted">{{ $review->created_at->format('h:i A') }}</div>
                        @if($review->updated_at->ne($review->created_at))
                            <div class="small text-muted mt-2">
                                Last updated: {{ $review->updated_at->format('d M Y, h:i A') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Verified purchase</div>
                        <div>
                            @if($review->verified_purchase)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </div>
                    </div>
                    @if($review->moderated_at)
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Last moderated</div>
                            <div class="fw-semibold">{{ $review->moderated_at->format('d M Y, h:i A') }}</div>
                            @if($review->moderator)
                                <div class="text-muted small">By {{ $review->moderator->name }}</div>
                            @endif
                        </div>
                    @endif
                </div>

                @if($review->admin_notes)
                    <div class="alert alert-secondary mt-4 mb-0">
                        <strong>Admin notes:</strong><br>{{ $review->admin_notes }}
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Moderation Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @if($review->status !== 'approved')
                        <div class="col-md-4">
                            <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small">Notes (optional)</label>
                                    <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="Approval note">{{ old('admin_notes', $review->admin_notes) }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check"></i> Approve Review
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($review->status !== 'rejected')
                        <div class="col-md-4">
                            <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small">Rejection reason (optional)</label>
                                    <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="Why this review was rejected"></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-times"></i> Reject Review
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($review->status !== 'reported')
                        <div class="col-md-4">
                            <form action="{{ route('admin.reviews.report', $review) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small">Report reason <span class="text-danger">*</span></label>
                                    <textarea name="admin_notes" class="form-control form-control-sm @error('admin_notes') is-invalid @enderror" rows="2" required placeholder="Spam, abusive language, fake review, etc.">{{ old('admin_notes') }}</textarea>
                                    @error('admin_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-dark w-100">
                                    <i class="fas fa-flag"></i> Mark as Reported
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <hr>

                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Permanently delete this review?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-trash"></i> Delete Review Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if($review->product)
            @php
                $thumbnail = $review->product->getThumbnailImage();
                $imageUrl = $thumbnail ? ($thumbnail->url ?? asset('images/product-placeholder.jpg')) : asset('images/product-placeholder.jpg');
            @endphp
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Linked Product</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="{{ $imageUrl }}" alt="{{ $review->product->name }}" class="img-fluid rounded" style="max-height: 180px; object-fit: cover;">
                    </div>
                    <h6 class="fw-bold">{{ $review->product->name }}</h6>
                    @if($review->product->category)
                        <div class="text-muted small mb-2">{{ $review->product->category->name }}</div>
                    @endif
                    <div class="mb-2">
                        <strong>Price:</strong> ₹{{ number_format($review->product->price, 2) }}
                    </div>
                    <div class="mb-2">
                        <strong>Rating:</strong>
                        {{ $review->product->average_rating ? number_format($review->product->average_rating, 1) : 'N/A' }}
                        ({{ $review->product->reviews_count ?? 0 }} reviews)
                    </div>
                    <div class="mb-2">
                        <strong>SKU / Slug:</strong>
                        <code class="small">{{ $review->product->slug }}</code>
                    </div>
                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('products.show', $review->product->slug) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-external-link-alt"></i> View on Storefront
                        </a>
                        <a href="{{ route('admin.products.show', $review->product) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-box"></i> Open Admin Product
                        </a>
                        <a href="{{ route('admin.products.edit', $review->product) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-pen"></i> Edit Product
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-box-open fa-2x mb-2"></i>
                    <p class="mb-0">The linked product no longer exists.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
