@extends('admin.layout')

@section('title', 'Review Moderation')
@section('page-title', 'Review Moderation')
@section('page-description', 'Reviews publish instantly on the storefront; approve, reject, or report them here when needed')
@section('breadcrumb-section', 'Admin')
@section('breadcrumb-page', 'Reviews')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md">
        <div class="stats-card">
            <div class="stats-icon primary"><i class="fas fa-star"></i></div>
            <div class="stats-value">{{ $stats['total'] ?? 0 }}</div>
            <div class="stats-label">Total Reviews</div>
        </div>
    </div>
    <div class="col-md">
        <div class="stats-card">
            <div class="stats-icon warning"><i class="fas fa-clock"></i></div>
            <div class="stats-value">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stats-label">Pending</div>
        </div>
    </div>
    <div class="col-md">
        <div class="stats-card">
            <div class="stats-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stats-value">{{ $stats['approved'] ?? 0 }}</div>
            <div class="stats-label">Approved</div>
        </div>
    </div>
    <div class="col-md">
        <div class="stats-card">
            <div class="stats-icon danger"><i class="fas fa-times-circle"></i></div>
            <div class="stats-value">{{ $stats['rejected'] ?? 0 }}</div>
            <div class="stats-label">Rejected</div>
        </div>
    </div>
    <div class="col-md">
        <div class="stats-card">
            <div class="stats-icon danger"><i class="fas fa-flag"></i></div>
            <div class="stats-value">{{ $stats['reported'] ?? 0 }}</div>
            <div class="stats-label">Reported</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}"
                       placeholder="Product, user, title, or comment">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach(\App\Models\Review::statusOptions() as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="rating" class="form-label">Rating</label>
                <select class="form-select" id="rating" name="rating">
                    <option value="">All ratings</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-grow-1">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-rotate-right"></i>
                </a>
                @if(($stats['pending'] ?? 0) > 0)
                    <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}" class="btn btn-warning">
                        <i class="fas fa-clock"></i> Pending ({{ $stats['pending'] }})
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0">Product Reviews</h5>
        <span class="text-muted small">Showing {{ $reviews->count() }} of {{ $reviews->total() }}</span>
    </div>

    @if($reviews->count() > 0)
        <form id="bulkReviewForm" action="{{ route('admin.reviews.bulk-action') }}" method="POST">
            @csrf
            <div class="card-body border-bottom bg-light py-2">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAllReviews">
                        <label class="form-check-label small" for="selectAllReviews">Select all on page</label>
                    </div>
                    <select name="action" class="form-select form-select-sm" style="width: auto;" required>
                        <option value="">Bulk action...</option>
                        <option value="approve">Approve selected</option>
                        <option value="reject">Reject selected</option>
                        <option value="delete">Delete selected</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmBulkAction()">Apply</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 36px;"></th>
                            <th>Product</th>
                            <th>Reviewer</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                            <tr>
                                <td>
                                    <input class="form-check-input review-checkbox" type="checkbox" name="review_ids[]" value="{{ $review->id }}">
                                </td>
                                <td style="min-width: 200px;">
                                    @if($review->product)
                                        <a href="{{ route('admin.reviews.show', $review) }}" class="fw-semibold text-decoration-none">
                                            {{ Str::limit($review->product->name, 40) }}
                                        </a>
                                        <div class="d-flex gap-2 mt-1">
                                            <a href="{{ route('products.show', $review->product->slug) }}" target="_blank" class="small text-primary">
                                                <i class="fas fa-external-link-alt"></i> Storefront
                                            </a>
                                            <a href="{{ route('admin.products.show', $review->product) }}" class="small text-muted">
                                                <i class="fas fa-box"></i> Admin product
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">Product removed</span>
                                    @endif
                                </td>
                                <td style="min-width: 160px;">
                                    @if($review->user)
                                        <div class="fw-semibold">{{ $review->user->name }}</div>
                                        <div class="text-muted small">{{ $review->user->email }}</div>
                                        <a href="{{ route('admin.users.show', $review->user) }}" class="small">View user</a>
                                    @else
                                        <span class="text-muted">User removed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-warning">
                                        @for($s = 1; $s <= 5; $s++)
                                            <i class="fas fa-star{{ $s <= $review->rating ? '' : '-o' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="small text-muted">{{ $review->rating }}/5</span>
                                    @if($review->verified_purchase)
                                        <div><span class="badge bg-info text-dark mt-1">Verified purchase</span></div>
                                    @endif
                                </td>
                                <td style="max-width: 280px;">
                                    @if($review->title)
                                        <div class="fw-semibold">{{ Str::limit($review->title, 50) }}</div>
                                    @endif
                                    <div class="text-muted small">{{ Str::limit($review->comment, 90) }}</div>
                                </td>
                                <td style="white-space: nowrap;">
                                    <div class="fw-semibold">{{ $review->created_at->format('d M Y') }}</div>
                                    <div class="text-muted small">{{ $review->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $review->statusBadgeClass() }}">{{ $review->statusLabel() }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-outline-info" title="View details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($review->status !== 'approved')
                                            <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="redirect_to_index" value="1">
                                                <button type="submit" class="btn btn-outline-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($review->status !== 'rejected')
                                            <form action="{{ route('admin.reviews.reject', $review) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="redirect_to_index" value="1">
                                                <button type="submit" class="btn btn-outline-danger" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    @else
        <div class="card-body text-center py-5">
            <i class="fas fa-star fa-3x text-muted mb-3"></i>
            <h5 class="mb-2">No reviews found</h5>
            <p class="text-muted mb-0">Reviews from customers will appear here for moderation.</p>
        </div>
    @endif

    @if($reviews->hasPages())
        <div class="card-footer">
            {{ $reviews->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.getElementById('selectAllReviews')?.addEventListener('change', function () {
    document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = this.checked);
});

function confirmBulkAction() {
    const form = document.getElementById('bulkReviewForm');
    const action = form.querySelector('[name="action"]').value;
    const checked = form.querySelectorAll('.review-checkbox:checked').length;

    if (!action) {
        alert('Please choose a bulk action.');
        return false;
    }

    if (checked === 0) {
        alert('Please select at least one review.');
        return false;
    }

    return confirm(`Apply "${action}" to ${checked} selected review(s)?`);
}
</script>
@endpush
