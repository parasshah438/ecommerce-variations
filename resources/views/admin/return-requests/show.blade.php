@extends('layouts.admin')

@section('title', 'Return Request #' . $returnRequest->id)

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">Return Request #{{ $returnRequest->id }}</h2>
            <p class="text-muted mb-0">
                For Order #{{ $returnRequest->order->id }} &middot; 
                Submitted {{ $returnRequest->created_at->format('M d, Y \a\t H:i A') }}
            </p>
        </div>
        <div>
            <a href="{{ route('admin.return-requests.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Status & Workflow Card -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-info-circle text-primary me-2"></i>Return Status
                    </h5>
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-{{ $returnRequest->status_badge_class }} fs-6 me-3">
                            {{ $returnRequest->formatted_status }}
                        </span>
                    </div>

                    <!-- Workflow Steps -->
                    <div class="status-timeline">
                        <div class="timeline-item {{ $returnRequest->status === 'pending' ? 'active' : 'completed' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Return Requested</h6>
                                <small class="text-muted">{{ $returnRequest->created_at->format('M d, Y H:i') }}</small>
                                @if($returnRequest->customer_reason)
                                    <br><small class="text-muted">Reason: {{ $returnRequest->customer_reason }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="timeline-item {{ in_array($returnRequest->status, ['approved','pickup_scheduled','picked_up','refunded']) ? 'completed' : ($returnRequest->status === 'pending' ? '' : 'active') }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">{{ $returnRequest->isApproved() || $returnRequest->status === 'rejected' ? ($returnRequest->isRejected() ? 'Rejected' : 'Approved') : 'Awaiting Review' }}</h6>
                                <small class="text-muted">
                                    @if($returnRequest->reviewed_at)
                                        {{ $returnRequest->reviewed_at->format('M d, Y H:i') }}
                                    @else
                                        Pending admin review
                                    @endif
                                </small>
                                @if($returnRequest->admin_note)
                                    <br><small class="text-muted">Note: {{ $returnRequest->admin_note }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="timeline-item {{ in_array($returnRequest->status, ['pickup_scheduled','picked_up','refunded']) ? 'completed' : ($returnRequest->status === 'approved' ? 'active' : '') }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Pickup Scheduled</h6>
                                <small class="text-muted">
                                    @if($returnRequest->pickup_scheduled_date)
                                        {{ $returnRequest->pickup_scheduled_date->format('M d, Y H:i') }}
                                    @else
                                        Not scheduled
                                    @endif
                                </small>
                                @if($returnRequest->pickup_awb)
                                    <br><small class="text-muted">AWB: {{ $returnRequest->pickup_awb }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="timeline-item {{ in_array($returnRequest->status, ['picked_up','refunded']) ? 'completed' : ($returnRequest->status === 'pickup_scheduled' ? 'active' : '') }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Picked Up</h6>
                                <small class="text-muted">
                                    @if($returnRequest->picked_up_at)
                                        {{ $returnRequest->picked_up_at->format('M d, Y H:i') }}
                                    @else
                                        Awaiting pickup
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="timeline-item {{ $returnRequest->status === 'refunded' ? 'completed' : ($returnRequest->status === 'picked_up' ? 'active' : '') }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Refunded</h6>
                                <small class="text-muted">
                                    @if($returnRequest->refunded_at)
                                        ₹{{ number_format($returnRequest->refund_amount ?? 0, 2) }} on {{ $returnRequest->refunded_at->format('M d, Y H:i') }}
                                        @if($returnRequest->refund_id)
                                            <br><small>Refund ID: {{ $returnRequest->refund_id }}</small>
                                        @endif
                                    @else
                                        Awaiting refund
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-user text-info me-2"></i>Customer Information
                    </h5>
                    <p class="mb-2"><strong>{{ $returnRequest->user->name }}</strong></p>
                    <p class="text-muted mb-2"><i class="fas fa-envelope me-2"></i>{{ $returnRequest->user->email }}</p>
                    @if($returnRequest->user->phone)
                        <p class="text-muted mb-2"><i class="fas fa-phone me-2"></i>{{ $returnRequest->user->phone }}</p>
                    @endif

                    <hr class="my-3">

                    <h6 class="mb-2">Order Details</h6>
                    <p class="mb-1"><strong>Order #{{ $returnRequest->order->id }}</strong></p>
                    <p class="text-muted mb-1">Total: ₹{{ number_format($returnRequest->order->total, 2) }}</p>
                    <p class="text-muted mb-1">Payment: {{ ucfirst($returnRequest->order->payment_method) }}</p>
                    <p class="text-muted mb-0">Status: {{ $returnRequest->order->formatted_status }}</p>

                    <hr class="my-3">

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.orders.show', $returnRequest->order) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="fas fa-eye me-1"></i>View Order
                        </a>
                        <a href="{{ route('admin.users.show', $returnRequest->user) }}" class="btn btn-sm btn-outline-info flex-fill">
                            <i class="fas fa-user me-1"></i>View Customer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Items -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="fas fa-box text-primary me-2"></i>Items Being Returned
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $returnItemIds = $returnRequest->return_items ?? []; @endphp
                        @foreach($returnRequest->order->items as $item)
                            @if(in_array($item->id, $returnItemIds))
                            <tr>
                                <td>
                                    <strong>{{ $item->productVariation->product->name ?? 'Product Deleted' }}</strong>
                                </td>
                                <td><code>{{ $item->productVariation->sku ?? 'N/A' }}</code></td>
                                <td>₹{{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td><strong>₹{{ number_format($item->price * $item->quantity, 2) }}</strong></td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-active">
                            <td colspan="4" class="text-end"><strong>Total Refund Amount:</strong></td>
                            <td><strong>₹{{ number_format($returnRequest->refund_amount, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($returnRequest->canBeApproved())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="fas fa-gavel text-warning me-2"></i>Review Return Request
            </h5>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <button class="btn btn-success w-100" onclick="approveReturn({{ $returnRequest->id }})">
                        <i class="fas fa-check me-2"></i>Approve Return
                    </button>
                    <small class="text-muted d-block mt-1">Approves return, restores stock</small>
                </div>
                <div class="col-md-6 mb-2">
                    <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times me-2"></i>Reject Return
                    </button>
                    <small class="text-muted d-block mt-1">Rejects the return request</small>
                </div>
            </div>

            <!-- Approve with options -->
            <div class="mt-3 p-3 bg-light rounded">
                <form id="approveForm" method="POST" action="{{ route('admin.return-requests.approve', $returnRequest) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Admin Note (optional)</label>
                            <textarea class="form-control" name="admin_note" rows="2" placeholder="Note to customer..."></textarea>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="schedulePickup" name="schedule_pickup" value="1" checked>
                                <label class="form-check-label" for="schedulePickup">
                                    Schedule Shiprocket pickup automatically
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="approveAndRefund" name="approve_and_refund" value="1">
                                <label class="form-check-label" for="approveAndRefund">
                                    Auto-process refund immediately (skip pickup)
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Post-approval actions -->
    @if($returnRequest->isApproved() || $returnRequest->status === 'pickup_scheduled')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="fas fa-truck text-primary me-2"></i>Pickup & Refund Workflow
            </h5>
            <div class="row">
                @if($returnRequest->isApproved())
                <div class="col-md-4 mb-2">
                    <form method="POST" action="{{ route('admin.return-requests.schedule-pickup', $returnRequest) }}">
                        @csrf
                        <button type="submit" class="btn btn-info w-100">
                            <i class="fas fa-calendar-check me-2"></i>Schedule Pickup
                        </button>
                    </form>
                </div>
                @endif
                @if($returnRequest->isApproved() || $returnRequest->status === 'pickup_scheduled')
                <div class="col-md-4 mb-2">
                    <form method="POST" action="{{ route('admin.return-requests.mark-picked-up', $returnRequest) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Mark items as picked up?')">
                            <i class="fas fa-check-double me-2"></i>Mark Picked Up
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($returnRequest->status === 'picked_up')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="fas fa-credit-card text-success me-2"></i>Process Refund
            </h5>
            <p class="text-muted">
                Items have been picked up. Process the refund to complete the return.
                @if($returnRequest->order->latestPayment && $returnRequest->order->latestPayment->gateway === 'razorpay')
                    Refund will be processed automatically via Razorpay.
                @else
                    Manual refund may be required for non-Razorpay payments.
                @endif
            </p>
            <form method="POST" action="{{ route('admin.return-requests.process-refund', $returnRequest) }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Refund Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" name="refund_amount" class="form-control" value="{{ $returnRequest->refund_amount }}" max="{{ $returnRequest->refund_amount }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Process refund of this amount?')">
                        <i class="fas fa-check-circle me-2"></i>Process Refund
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($returnRequest->isRejected())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Return Rejected</strong>
        @if($returnRequest->admin_note)
            <br>{{ $returnRequest->admin_note }}
        @endif
        @if($returnRequest->reviewed_at)
            <br><small>Reviewed on {{ $returnRequest->reviewed_at->format('M d, Y H:i') }}</small>
        @endif
    </div>
    @endif

    @if($returnRequest->status === 'refunded')
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Return Completed — Refunded</strong>
        <br>Amount: ₹{{ number_format($returnRequest->refund_amount ?? 0, 2) }}
        @if($returnRequest->refund_id)
            <br>Refund ID: <code>{{ $returnRequest->refund_id }}</code>
        @endif
        @if($returnRequest->refunded_at)
            <br>Processed on {{ $returnRequest->refunded_at->format('M d, Y H:i') }}
        @endif
    </div>
    @endif
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger"><i class="fas fa-times-circle me-2"></i>Reject Return Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.return-requests.reject', $returnRequest) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Rejecting this return request.</strong> The customer will be notified.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="admin_note" rows="3" required placeholder="Explain why the return is rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Reject Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.status-timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 12px;
    top: 30px;
    width: 2px;
    height: calc(100% + 20px);
    background-color: #e2e8f0;
}

.timeline-item.completed::after {
    background-color: #10b981;
}

.timeline-item.active::after {
    background-color: #3b82f6;
}

.timeline-marker {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 3px solid #e2e8f0;
    background-color: white;
    margin-right: 15px;
    flex-shrink: 0;
    z-index: 1;
}

.timeline-item.completed .timeline-marker {
    border-color: #10b981;
    background-color: #10b981;
}

.timeline-item.active .timeline-marker {
    border-color: #3b82f6;
    background-color: #3b82f6;
}

.timeline-content h6 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 2px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle approve and refund checkbox
    const approveAndRefundCheckbox = document.getElementById('approveAndRefund');
    const schedulePickupCheckbox = document.getElementById('schedulePickup');

    if (approveAndRefundCheckbox && schedulePickupCheckbox) {
        approveAndRefundCheckbox.addEventListener('change', function() {
            if (this.checked) {
                schedulePickupCheckbox.checked = false;
                schedulePickupCheckbox.disabled = true;
            } else {
                schedulePickupCheckbox.disabled = false;
            }
        });
    }
});

function approveReturn(id) {
    const form = document.getElementById('approveForm');
    const formData = new FormData(form);
    const approveAndRefund = document.getElementById('approveAndRefund')?.checked;

    if (!confirm('Are you sure you want to approve this return request?')) {
        return;
    }

    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✓ ' + data.message);
            if (approveAndRefund) {
                // Auto-process pickup → picked up → refund
                const orderId = {{ $returnRequest->order_id }};
                const returnId = {{ $returnRequest->id }};
                autoProcessRefund(returnId);
            } else {
                location.reload();
            }
        } else {
            alert('Error: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-2"></i>Approve Return';
        }
    })
    .catch(() => {
        alert('An error occurred. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check me-2"></i>Approve Return';
    });
}

function autoProcessRefund(returnId) {
    // Chain: schedule pickup → mark picked up → process refund
    fetch(`/admin/return-requests/${returnId}/schedule-pickup`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data1 => {
        if (data1.success) {
            return fetch(`/admin/return-requests/${returnId}/mark-picked-up`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            });
        }
        throw new Error(data1.message || 'Pickup scheduling failed');
    })
    .then(r => r.json())
    .then(data2 => {
        if (data2.success) {
            // Process refund with full amount
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('refund_amount', {{ $returnRequest->refund_amount }});

            return fetch(`/admin/return-requests/${returnId}/process-refund`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData,
            }).then(r => r.json());
        }
        throw new Error(data2.message || 'Mark picked up failed');
    })
    .then(data3 => {
        if (data3.success) {
            alert('✓ Return approved and refunded successfully!');
            location.reload();
        } else {
            alert('Error during refund: ' + data3.message);
            location.reload();
        }
    })
    .catch(err => {
        alert('Auto-refund process encountered an issue: ' + err.message);
        location.reload();
    });
}
</script>
@endpush
