<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="cancelModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Cancel Order
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" id="cancelOrderForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning!</strong> Cancelling this order will:
                        <ul class="mb-0 mt-2">
                            <li>Mark the order as cancelled</li>
                            <li>Restore stock for all ordered items</li>
                            <li>Send cancellation notification to customer</li>
                            <li>This action cannot be undone</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                        <select class="form-select" id="cancel_reason" name="reason" required>
                            <option value="">Select Reason</option>
                            <option value="out_of_stock">Out of Stock</option>
                            <option value="customer_request">Customer Request</option>
                            <option value="payment_failed">Payment Failed</option>
                            <option value="fraud_prevention">Fraud Prevention</option>
                            <option value="address_issues">Address Issues</option>
                            <option value="shipping_issues">Shipping Issues</option>
                            <option value="product_discontinued">Product Discontinued</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3" id="customReasonDiv" style="display: none;">
                        <label for="custom_reason" class="form-label">Custom Reason</label>
                        <textarea class="form-control" id="custom_reason" name="custom_reason" rows="3" 
                                  placeholder="Please specify the reason for cancellation..."></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notifyCustomer" name="notify_customer" checked>
                            <label class="form-check-label" for="notifyCustomer">
                                Send cancellation email to customer
                            </label>
                        </div>
                    </div>

                    <!-- Order Summary for Cancellation -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Order Summary</h6>
                            <div class="row text-sm">
                                <div class="col-6"><strong>Order ID:</strong></div>
                                <div class="col-6">#{{ $order->id }}</div>
                                <div class="col-6"><strong>Total Amount:</strong></div>
                                <div class="col-6">₹{{ number_format($order->total, 2) }}</div>
                                <div class="col-6"><strong>Items Count:</strong></div>
                                <div class="col-6">{{ $order->items->count() }} items</div>
                                <div class="col-6"><strong>Payment Status:</strong></div>
                                <div class="col-6">
                                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                        {{ $order->formatted_payment_status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Order</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Cancel Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cancelReasonSelect = document.getElementById('cancel_reason');
    const customReasonDiv = document.getElementById('customReasonDiv');

    if (cancelReasonSelect) {
        cancelReasonSelect.addEventListener('change', function() {
            if (this.value === 'other') {
                customReasonDiv.style.display = 'block';
                document.getElementById('custom_reason').required = true;
            } else {
                customReasonDiv.style.display = 'none';
                document.getElementById('custom_reason').required = false;
            }
        });
    }

    // Form validation
    const cancelForm = document.getElementById('cancelOrderForm');
    if (cancelForm) {
        cancelForm.addEventListener('submit', function(e) {
            const reason = cancelReasonSelect.value;
            const customReason = document.getElementById('custom_reason').value;

            if (reason === 'other' && !customReason.trim()) {
                e.preventDefault();
                alert('Please provide a custom reason for cancellation.');
                return false;
            }

            if (!confirm('Are you absolutely sure you want to cancel this order? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>