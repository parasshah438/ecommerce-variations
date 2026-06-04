@extends('layouts.admin')

@section('title', 'Payment #' . $payment->id)

@section('content')
<div class="container-fluid px-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">Payment Detail</h2>
            <p class="text-muted mb-0">Payment ID: <span class="font-monospace">{{ $payment->payment_id ?? $payment->gateway_payment_id ?? '#' . $payment->id }}</span></p>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Payments
        </a>
    </div>

    <div class="row g-4">

        <!-- Left: Payment Info -->
        <div class="col-lg-8">

            <!-- Transaction Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Transaction Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="text-muted small mb-1">Payment ID</label>
                            <p class="font-monospace mb-0">{{ $payment->payment_id ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small mb-1">Gateway Payment ID</label>
                            <p class="font-monospace mb-0">{{ $payment->gateway_payment_id ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small mb-1">Gateway Order ID</label>
                            <p class="font-monospace mb-0">{{ $payment->gateway_order_id ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small mb-1">Transaction ID</label>
                            <p class="font-monospace mb-0">{{ $payment->transaction_id ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small mb-1">Receipt Number</label>
                            <p class="mb-0">{{ $payment->receipt_number ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small mb-1">Gateway</label>
                            <p class="mb-0"><span class="badge bg-secondary text-uppercase">{{ $payment->gateway ?? '—' }}</span></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small mb-1">Payment Method</label>
                            <p class="mb-0">{{ $payment->payment_method ?? $payment->method ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small mb-1">Currency</label>
                            <p class="mb-0">{{ strtoupper($payment->currency ?? 'INR') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Info -->
            @if($payment->order)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Linked Order</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="fw-semibold">Order #{{ $payment->order->id }}</span>
                            <span class="ms-2 badge bg-{{ $payment->order->status === 'delivered' ? 'success' : 'secondary' }}">
                                {{ ucfirst($payment->order->status) }}
                            </span>
                        </div>
                        <a href="{{ route('admin.orders.show', $payment->order) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i>View Order
                        </a>
                    </div>
                    @if($payment->order->items && $payment->order->items->count())
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->order->items as $item)
                                <tr>
                                    <td>
                                        @if(optional(optional($item->variation)->product)->name)
                                            {{ $item->variation->product->name }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">Order Total</th>
                                    <th class="text-end">₹{{ number_format($payment->order->total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Gateway Response -->
            @if($payment->gateway_response)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Gateway Response</div>
                <div class="card-body">
                    <pre class="bg-light rounded p-3 small mb-0" style="max-height:300px;overflow:auto;">{{ json_encode($payment->gateway_response, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif

        </div>

        <!-- Right: Status & Customer -->
        <div class="col-lg-4">

            <!-- Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Payment Status</div>
                <div class="card-body">
                    @php
                        $statusClass = match($payment->payment_status) {
                            'paid'      => 'success',
                            'failed'    => 'danger',
                            'pending'   => 'warning',
                            'refunded'  => 'info',
                            'cancelled' => 'secondary',
                            default     => 'light',
                        };
                    @endphp
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-{{ $statusClass }} fs-6 px-3 py-2">{{ ucfirst($payment->payment_status ?? '—') }}</span>
                    </div>
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Amount</span>
                            <strong>₹{{ number_format($payment->amount, 2) }}</strong>
                        </li>
                        @if($payment->paid_at)
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Paid At</span>
                            <span>{{ $payment->paid_at->format('d M Y, h:i A') }}</span>
                        </li>
                        @endif
                        @if($payment->failed_at)
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Failed At</span>
                            <span>{{ $payment->failed_at->format('d M Y, h:i A') }}</span>
                        </li>
                        @endif
                        @if($payment->refunded_at)
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Refunded At</span>
                            <span>{{ $payment->refunded_at->format('d M Y, h:i A') }}</span>
                        </li>
                        @endif
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Created</span>
                            <span>{{ $payment->created_at->format('d M Y, h:i A') }}</span>
                        </li>
                    </ul>
                    @if($payment->failure_reason)
                    <div class="alert alert-danger mt-3 mb-0 small">
                        <strong>Failure Reason:</strong> {{ $payment->failure_reason }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Customer Card -->
            @if($payment->user)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Customer</div>
                <div class="card-body">
                    <p class="fw-semibold mb-1">{{ $payment->user->name }}</p>
                    <p class="text-muted small mb-2">{{ $payment->user->email }}</p>
                    <a href="{{ route('admin.users.show', $payment->user) }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fas fa-user me-1"></i>View Customer
                    </a>
                </div>
            </div>
            @endif

            <!-- Metadata -->
            @if($payment->ip_address || $payment->user_agent)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Session Info</div>
                <div class="card-body small">
                    @if($payment->ip_address)
                    <div class="mb-2">
                        <span class="text-muted">IP Address</span>
                        <div class="font-monospace">{{ $payment->ip_address }}</div>
                    </div>
                    @endif
                    @if($payment->user_agent)
                    <div>
                        <span class="text-muted">User Agent</span>
                        <div class="text-break" style="font-size:0.75rem;">{{ $payment->user_agent }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection
