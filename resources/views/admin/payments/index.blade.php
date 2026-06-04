@extends('layouts.admin')

@section('title', 'Payment Management')

@section('content')
<div class="container-fluid px-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">Payment Management</h2>
            <p class="text-muted mb-0">View and manage all payment transactions</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Payments</p>
                            <h4 class="mb-0">{{ $summary['total_payments'] }}</h4>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-credit-card text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Successful</p>
                            <h4 class="mb-0 text-success">{{ $summary['successful_payments'] }}</h4>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Failed</p>
                            <h4 class="mb-0 text-danger">{{ $summary['failed_payments'] }}</h4>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded p-3">
                            <i class="fas fa-times-circle text-danger fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Revenue</p>
                            <h4 class="mb-0">₹{{ number_format($summary['total_amount'], 2) }}</h4>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded p-3">
                            <i class="fas fa-rupee-sign text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by payment ID or order ID" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending"   @selected(request('status') === 'pending')>Pending</option>
                        <option value="paid"      @selected(request('status') === 'paid')>Paid</option>
                        <option value="failed"    @selected(request('status') === 'failed')>Failed</option>
                        <option value="refunded"  @selected(request('status') === 'refunded')>Refunded</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="gateway" class="form-select">
                        <option value="">All Gateways</option>
                        <option value="razorpay" @selected(request('gateway') === 'razorpay')>Razorpay</option>
                        <option value="cod"      @selected(request('gateway') === 'cod')>COD</option>
                        <option value="stripe"   @selected(request('gateway') === 'stripe')>Stripe</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'gateway']))
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Payment ID</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Gateway</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td class="text-muted small">{{ $payment->id }}</td>
                            <td>
                                <span class="font-monospace small">{{ $payment->payment_id ?? $payment->gateway_payment_id ?? '—' }}</span>
                            </td>
                            <td>
                                @if($payment->order)
                                    <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-decoration-none">
                                        #{{ $payment->order->id }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($payment->user)
                                    <div>{{ $payment->user->name }}</div>
                                    <div class="text-muted small">{{ $payment->user->email }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">{{ $payment->gateway ?? '—' }}</span>
                            </td>
                            <td class="fw-semibold">₹{{ number_format($payment->amount, 2) }}</td>
                            <td>
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
                                <span class="badge bg-{{ $statusClass }}">{{ ucfirst($payment->payment_status ?? '—') }}</span>
                            </td>
                            <td class="small text-muted">{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No payments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($payments->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }} payments</small>
            {{ $payments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
