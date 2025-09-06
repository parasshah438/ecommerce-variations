@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Orders Management</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Total</th>
                                    <th>Items</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <strong>#{{ $order->id }}</strong>
                                    </td>
                                    <td>
                                        <div>{{ $order->user->name }}</div>
                                        <small class="text-muted">{{ $order->user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $order->status === 'confirmed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $order->payment_status === 'completed' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td>₹{{ number_format($order->total, 2) }}</td>
                                    <td>{{ $order->items->count() }} items</td>
                                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            @if($order->status === \App\Models\Order::STATUS_PENDING)
                                            <button type="button" class="btn btn-sm btn-success" onclick="confirmOrder({{ $order->id }})">
                                                Confirm
                                            </button>
                                            @endif
                                            @if(in_array($order->status, [\App\Models\Order::STATUS_CONFIRMED, \App\Models\Order::STATUS_PROCESSING]))
                                            <button type="button" class="btn btn-sm btn-warning" onclick="cancelOrder({{ $order->id }})">
                                                Cancel
                                            </button>
                                            @endif
                                            @if($order->status === \App\Models\Order::STATUS_DELIVERED)
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="returnOrder({{ $order->id }})">
                                                Return
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">No orders found</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Modals -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Order</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to confirm this order? This will reserve stock for the order.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="confirmForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">Confirm Order</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Order</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cancel_reason">Cancellation Reason:</label>
                        <textarea class="form-control" name="reason" id="cancel_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Return</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="returnForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="return_reason">Return Reason:</label>
                        <textarea class="form-control" name="reason" id="return_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Process Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmOrder(orderId) {
    document.getElementById('confirmForm').action = `/admin/orders/${orderId}/confirm`;
    $('#confirmModal').modal('show');
}

function cancelOrder(orderId) {
    document.getElementById('cancelForm').action = `/admin/orders/${orderId}/cancel`;
    $('#cancelModal').modal('show');
}

function returnOrder(orderId) {
    document.getElementById('returnForm').action = `/admin/orders/${orderId}/return`;
    $('#returnModal').modal('show');
}
</script>
@endsection
