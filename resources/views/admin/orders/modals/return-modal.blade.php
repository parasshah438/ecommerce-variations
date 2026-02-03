<!-- Return Order Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning" id="returnModalLabel">
                    <i class="fas fa-undo me-2"></i>Process Order Return
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.orders.return', $order) }}" method="POST" id="returnOrderForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Return Process:</strong> Processing this return will:
                        <ul class="mb-0 mt-2">
                            <li>Mark the order as returned</li>
                            <li>Restore stock for all returned items</li>
                            <li>Initiate refund process if applicable</li>
                            <li>Send return confirmation to customer</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label for="return_reason" class="form-label">Return Reason <span class="text-danger">*</span></label>
                        <select class="form-select" id="return_reason" name="reason" required>
                            <option value="">Select Reason</option>
                            <option value="defective_product">Defective Product</option>
                            <option value="wrong_item">Wrong Item Delivered</option>
                            <option value="damaged_in_shipping">Damaged in Shipping</option>
                            <option value="not_as_described">Not as Described</option>
                            <option value="customer_changed_mind">Customer Changed Mind</option>
                            <option value="size_fit_issue">Size/Fit Issue</option>
                            <option value="arrived_too_late">Arrived Too Late</option>
                            <option value="quality_issues">Quality Issues</option>
                            <option value="missing_parts">Missing Parts/Accessories</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3" id="customReturnReasonDiv" style="display: none;">
                        <label for="custom_return_reason" class="form-label">Custom Return Reason</label>
                        <textarea class="form-control" id="custom_return_reason" name="custom_reason" rows="3" 
                                  placeholder="Please specify the reason for return..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="return_type" class="form-label">Return Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="return_type" name="return_type" required>
                            <option value="">Select Return Type</option>
                            <option value="full_return">Full Order Return</option>
                            <option value="partial_return">Partial Return</option>
                            <option value="exchange">Exchange</option>
                            <option value="refund_only">Refund Only</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="refund_method" class="form-label">Refund Method</label>
                        <select class="form-select" id="refund_method" name="refund_method">
                            <option value="">Select Refund Method</option>
                            <option value="original_payment">Original Payment Method</option>
                            <option value="store_credit">Store Credit</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="restockItems" name="restock_items" checked>
                            <label class="form-check-label" for="restockItems">
                                Restore items to inventory
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notifyCustomerReturn" name="notify_customer" checked>
                            <label class="form-check-label" for="notifyCustomerReturn">
                                Send return confirmation email to customer
                            </label>
                        </div>
                    </div>

                    <!-- Return Items Selection -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Items to Return</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input return-item" type="checkbox" 
                                                               value="{{ $item->id }}" name="return_items[]" 
                                                               data-price="{{ $item->price * $item->quantity }}"
                                                               checked>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small>{{ $item->productVariation->product->name ?? 'Product Deleted' }}</small>
                                                </td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3 p-2 bg-white rounded">
                                <div class="d-flex justify-content-between">
                                    <strong>Total Return Amount:</strong>
                                    <strong id="totalReturnAmount">₹{{ number_format($order->total, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo me-2"></i>Process Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const returnReasonSelect = document.getElementById('return_reason');
    const customReturnReasonDiv = document.getElementById('customReturnReasonDiv');
    const returnItems = document.querySelectorAll('.return-item');
    const totalReturnAmount = document.getElementById('totalReturnAmount');

    // Handle custom reason visibility
    if (returnReasonSelect) {
        returnReasonSelect.addEventListener('change', function() {
            if (this.value === 'other') {
                customReturnReasonDiv.style.display = 'block';
                document.getElementById('custom_return_reason').required = true;
            } else {
                customReturnReasonDiv.style.display = 'none';
                document.getElementById('custom_return_reason').required = false;
            }
        });
    }

    // Calculate return amount based on selected items
    function updateReturnAmount() {
        let total = 0;
        returnItems.forEach(item => {
            if (item.checked) {
                total += parseFloat(item.dataset.price);
            }
        });
        totalReturnAmount.textContent = '₹' + total.toFixed(2);
    }

    // Add event listeners to return item checkboxes
    returnItems.forEach(item => {
        item.addEventListener('change', updateReturnAmount);
    });

    // Form validation
    const returnForm = document.getElementById('returnOrderForm');
    if (returnForm) {
        returnForm.addEventListener('submit', function(e) {
            const reason = returnReasonSelect.value;
            const customReason = document.getElementById('custom_return_reason').value;
            const checkedItems = document.querySelectorAll('.return-item:checked');

            if (reason === 'other' && !customReason.trim()) {
                e.preventDefault();
                alert('Please provide a custom reason for return.');
                return false;
            }

            if (checkedItems.length === 0) {
                e.preventDefault();
                alert('Please select at least one item to return.');
                return false;
            }

            if (!confirm('Are you sure you want to process this return? This action will restore inventory and may initiate refunds.')) {
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>