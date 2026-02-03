<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1" aria-labelledby="bulkActionsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionsModalLabel">
                    <i class="fas fa-tasks me-2"></i>Bulk Actions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkActionForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You have selected <span id="selectedOrdersCount">0</span> orders for bulk action.
                    </div>

                    <div class="mb-3">
                        <label for="bulk_action" class="form-label">Select Action <span class="text-danger">*</span></label>
                        <select class="form-select" id="bulk_action" name="action" required>
                            <option value="">Choose Action</option>
                            <option value="update_status">Update Status</option>
                            <option value="mark_paid">Mark as Paid</option>
                            <option value="send_email">Send Email</option>
                            <option value="export_selected">Export Selected</option>
                            <option value="delete" class="text-danger">Delete Orders</option>
                        </select>
                    </div>

                    <!-- Status Update Options -->
                    <div class="mb-3 d-none" id="statusUpdateOptions">
                        <label for="bulk_status" class="form-label">New Status</label>
                        <select class="form-select" id="bulk_status" name="status">
                            <option value="">Select Status</option>
                            @foreach(App\Models\Order::getStatuses() as $statusKey => $statusLabel)
                                <option value="{{ $statusKey }}">{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Email Options -->
                    <div class="mb-3 d-none" id="emailOptions">
                        <label for="email_type" class="form-label">Email Type</label>
                        <select class="form-select" id="email_type" name="email_type">
                            <option value="order_confirmation">Order Confirmation</option>
                            <option value="status_update">Status Update</option>
                            <option value="invoice">Invoice</option>
                            <option value="custom">Custom Message</option>
                        </select>
                        <div class="mt-2" id="customEmailMessage" style="display: none;">
                            <textarea class="form-control" name="custom_message" rows="3" placeholder="Enter custom message..."></textarea>
                        </div>
                    </div>

                    <!-- Export Options -->
                    <div class="mb-3 d-none" id="exportOptions">
                        <label for="export_format" class="form-label">Export Format</label>
                        <select class="form-select" id="export_format" name="export_format">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="bulk_notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="bulk_notes" name="notes" rows="3" 
                                  placeholder="Add any notes for this bulk action..."></textarea>
                    </div>

                    <!-- Confirmation -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmBulkAction" required>
                        <label class="form-check-label" for="confirmBulkAction">
                            I understand that this action will be applied to all selected orders
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-2"></i>Apply Action
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bulkActionSelect = document.getElementById('bulk_action');
    const statusOptions = document.getElementById('statusUpdateOptions');
    const emailOptions = document.getElementById('emailOptions');
    const exportOptions = document.getElementById('exportOptions');
    const emailTypeSelect = document.getElementById('email_type');
    const customEmailMessage = document.getElementById('customEmailMessage');

    // Show/hide options based on selected action
    bulkActionSelect.addEventListener('change', function() {
        // Hide all options first
        statusOptions.classList.add('d-none');
        emailOptions.classList.add('d-none');
        exportOptions.classList.add('d-none');

        switch(this.value) {
            case 'update_status':
                statusOptions.classList.remove('d-none');
                document.getElementById('bulk_status').required = true;
                break;
            case 'send_email':
                emailOptions.classList.remove('d-none');
                break;
            case 'export_selected':
                exportOptions.classList.remove('d-none');
                break;
            default:
                document.getElementById('bulk_status').required = false;
                break;
        }
    });

    // Show custom message field for custom email
    emailTypeSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customEmailMessage.style.display = 'block';
        } else {
            customEmailMessage.style.display = 'none';
        }
    });

    // Update selected orders count when modal is shown
    const bulkActionsModal = document.getElementById('bulkActionsModal');
    bulkActionsModal.addEventListener('show.bs.modal', function() {
        const selectedCount = document.querySelectorAll('.order-checkbox:checked').length;
        document.getElementById('selectedOrdersCount').textContent = selectedCount;
    });

    // Handle form submission
    const bulkActionForm = document.getElementById('bulkActionForm');
    bulkActionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selectedOrders = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
        const formData = new FormData(this);
        formData.append('order_ids', JSON.stringify(selectedOrders));

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        submitBtn.disabled = true;

        fetch('{{ route("admin.orders.bulk_status_update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Bulk action completed successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the bulk action.');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});
</script>