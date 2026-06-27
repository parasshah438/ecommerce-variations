@extends('layouts.admin')

@section('title', 'Return Requests')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">Return Requests</h2>
            <p class="text-muted mb-0">Manage customer return and refund requests</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-filter me-2"></i>Filter
            </button>
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Active Filters -->
    @if(request()->hasAny(['status', 'search']))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-filter me-2"></i>
                        <strong>Active Filters:</strong>
                        @if(request('status'))
                            <span class="badge bg-primary me-1">Status: {{ ucfirst(request('status')) }}</span>
                        @endif
                        @if(request('search'))
                            <span class="badge bg-light text-dark me-1">Search: "{{ request('search') }}"</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.return-requests.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear All
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2"><i class="fas fa-exchange-alt fa-2x"></i></div>
                    <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                    <small class="text-muted">Total Requests</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2"><i class="fas fa-clock fa-2x"></i></div>
                    <h4 class="mb-0">{{ number_format($stats['pending']) }}</h4>
                    <small class="text-muted">Pending Review</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2"><i class="fas fa-check-circle fa-2x"></i></div>
                    <h4 class="mb-0">{{ number_format($stats['approved']) }}</h4>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-danger mb-2"><i class="fas fa-times-circle fa-2x"></i></div>
                    <h4 class="mb-0">{{ number_format($stats['rejected']) }}</h4>
                    <small class="text-muted">Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-secondary mb-2"><i class="fas fa-credit-card fa-2x"></i></div>
                    <h4 class="mb-0">{{ number_format($stats['refunded']) }}</h4>
                    <small class="text-muted">Refunded</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Requests Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Return Requests</h5>
                    <small class="text-muted">
                        Showing {{ $returnRequests->count() }} of {{ $returnRequests->total() }}
                    </small>
                </div>
                <div>
                    <input type="text" class="form-control form-control-sm" placeholder="Search..." id="searchInput" style="width: 200px;" value="{{ request('search') }}">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Reason</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returnRequests as $rr)
                        <tr>
                            <td><strong>#{{ $rr->id }}</strong></td>
                            <td>
                                <a href="{{ route('admin.orders.show', $rr->order) }}" class="text-decoration-none">
                                    #{{ $rr->order->id }}
                                </a>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $rr->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $rr->user->email }}</small>
                                </div>
                            </td>
                            <td>
                                <span title="{{ $rr->customer_reason }}">{{ \Illuminate\Support\Str::limit($rr->customer_reason, 40) }}</span>
                            </td>
                            <td><strong>₹{{ number_format($rr->refund_amount, 2) }}</strong></td>
                            <td>
                                <span class="badge bg-{{ $rr->status_badge_class }} rounded-pill">
                                    {{ $rr->formatted_status }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    {{ $rr->created_at->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $rr->created_at->format('H:i A') }}</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.return-requests.show', $rr) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-exchange-alt fa-3x mb-3"></i>
                                    <h5>No Return Requests Found</h5>
                                    <p>There are no return requests to display at the moment.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        Showing {{ $returnRequests->firstItem() ?? 0 }} to {{ $returnRequests->lastItem() ?? 0 }} of {{ $returnRequests->total() }} results
                    </small>
                </div>
                <div>
                    {{ $returnRequests->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-filter me-2"></i>Filter Return Requests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('admin.return-requests.index') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach(App\Models\OrderReturnRequest::getStatuses() as $key => $label)
                                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Search (Order ID / Customer)</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search...">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Sort By</label>
                                <select name="sort_by" class="form-select">
                                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Date</option>
                                    <option value="refund_amount" {{ request('sort_by') === 'refund_amount' ? 'selected' : '' }}>Amount</option>
                                    <option value="status" {{ request('sort_by') === 'status' ? 'selected' : '' }}>Status</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Order</label>
                                <select name="sort_order" class="form-select">
                                    <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Newest First</option>
                                    <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Oldest First</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-2"></i>Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const url = new URL(window.location.href);
            if (this.value.length >= 2 || this.value.length === 0) {
                if (this.value) {
                    url.searchParams.set('search', this.value);
                } else {
                    url.searchParams.delete('search');
                }
                window.location.href = url.toString();
            }
        }, 500);
    });
});
</script>
@endpush
