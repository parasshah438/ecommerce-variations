<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">
                    <i class="fas fa-edit me-2"></i>Update Order Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" id="statusUpdateForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">New Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            @foreach(App\Models\Order::getStatuses() as $statusKey => $statusLabel)
                                @if($statusKey !== $order->status)
                                    <option value="{{ $statusKey }}" {{ old('status') === $statusKey ? 'selected' : '' }}>
                                        {{ $statusLabel }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text">Current status: <strong>{{ $order->formatted_status }}</strong></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Add any notes about this status change...">{{ old('notes') }}</textarea>
                        <div class="form-text">These notes will be recorded with the status change.</div>
                    </div>

                    <!-- Status Change Warnings -->
                    <div class="alert alert-info d-none" id="statusWarning">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="statusWarningText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const warningDiv = document.getElementById('statusWarning');
    const warningText = document.getElementById('statusWarningText');

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const selectedStatus = this.value;
            let warning = '';

            switch(selectedStatus) {
                case 'cancelled':
                    warning = 'Cancelling this order will restore the stock for all items.';
                    break;
                case 'returned':
                    warning = 'Processing return will restore the stock and may initiate refund process.';
                    break;
                case 'shipped':
                    warning = 'Mark as shipped only when the package has been dispatched.';
                    break;
                case 'delivered':
                    warning = 'Mark as delivered only when customer has received the order.';
                    break;
                case 'refunded':
                    warning = 'This status indicates the payment has been refunded to customer.';
                    break;
            }

            if (warning) {
                warningText.textContent = warning;
                warningDiv.classList.remove('d-none');
            } else {
                warningDiv.classList.add('d-none');
            }
        });
    }
});
</script>