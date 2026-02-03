<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">
                    <i class="fas fa-filter me-2"></i>Filter Orders
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="filterForm" action="{{ route('admin.orders.index') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <!-- Order Status Filter -->
                        <div class="col-md-6 mb-3">
                            <label for="filter_status" class="form-label">Order Status</label>
                            <select class="form-select" id="filter_status" name="status">
                                <option value="">All Statuses</option>
                                @foreach(App\Models\Order::getStatuses() as $statusKey => $statusLabel)
                                    <option value="{{ $statusKey }}" {{ request('status') === $statusKey ? 'selected' : '' }}>
                                        {{ $statusLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payment Status Filter -->
                        <div class="col-md-6 mb-3">
                            <label for="filter_payment_status" class="form-label">Payment Status</label>
                            <select class="form-select" id="filter_payment_status" name="payment_status">
                                <option value="">All Payment Statuses</option>
                                @foreach(App\Models\Order::getPaymentStatuses() as $statusKey => $statusLabel)
                                    <option value="{{ $statusKey }}" {{ request('payment_status') === $statusKey ? 'selected' : '' }}>
                                        {{ $statusLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Range Filter -->
                        <div class="col-md-6 mb-3">
                            <label for="filter_date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="filter_date_from" name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="filter_date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="filter_date_to" name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>

                        <!-- Amount Range Filter -->
                        <div class="col-md-6 mb-3">
                            <label for="filter_amount_min" class="form-label">Min Amount (₹)</label>
                            <input type="number" class="form-control" id="filter_amount_min" name="amount_min" 
                                   min="0" step="0.01" placeholder="0.00" value="{{ request('amount_min') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="filter_amount_max" class="form-label">Max Amount (₹)</label>
                            <input type="number" class="form-control" id="filter_amount_max" name="amount_max" 
                                   min="0" step="0.01" placeholder="0.00" value="{{ request('amount_max') }}">
                        </div>

                        <!-- Customer Filter -->
                        <div class="col-md-6 mb-3">
                            <label for="filter_customer" class="form-label">Customer Email</label>
                            <input type="email" class="form-control" id="filter_customer" name="customer_email" 
                                   placeholder="customer@example.com" value="{{ request('customer_email') }}">
                        </div>

                        <!-- Payment Method Filter -->
                        <div class="col-md-6 mb-3">
                            <label for="filter_payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="filter_payment_method" name="payment_method">
                                <option value="">All Methods</option>
                                <option value="razorpay" {{ request('payment_method') === 'razorpay' ? 'selected' : '' }}>Razorpay</option>
                                <option value="cod" {{ request('payment_method') === 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                                <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="wallet" {{ request('payment_method') === 'wallet' ? 'selected' : '' }}>Wallet</option>
                            </select>
                        </div>

                        <!-- Search Filter -->
                        <div class="col-md-12 mb-3">
                            <label for="filter_search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="filter_search" name="search" 
                                   placeholder="Search by order ID, customer name, email, or product name..." 
                                   value="{{ request('search') }}">
                            <div class="form-text">Search in order ID, customer details, and product names</div>
                        </div>
                    </div>

                    <!-- Quick Date Filters -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Quick Date Filters</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="quick_filter" id="today" value="today">
                                <label class="btn btn-outline-primary btn-sm" for="today">Today</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="yesterday" value="yesterday">
                                <label class="btn btn-outline-primary btn-sm" for="yesterday">Yesterday</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="this_week" value="this_week">
                                <label class="btn btn-outline-primary btn-sm" for="this_week">This Week</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="last_week" value="last_week">
                                <label class="btn btn-outline-primary btn-sm" for="last_week">Last Week</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="this_month" value="this_month">
                                <label class="btn btn-outline-primary btn-sm" for="this_month">This Month</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="last_month" value="last_month">
                                <label class="btn btn-outline-primary btn-sm" for="last_month">Last Month</label>
                            </div>
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <option value="created_at" {{ request('sort_by', 'created_at') === 'created_at' ? 'selected' : '' }}>Order Date</option>
                                <option value="total" {{ request('sort_by') === 'total' ? 'selected' : '' }}>Total Amount</option>
                                <option value="status" {{ request('sort_by') === 'status' ? 'selected' : '' }}>Status</option>
                                <option value="payment_status" {{ request('sort_by') === 'payment_status' ? 'selected' : '' }}>Payment Status</option>
                                <option value="user_name" {{ request('sort_by') === 'user_name' ? 'selected' : '' }}>Customer Name</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <select class="form-select" id="sort_order" name="sort_order">
                                <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>Descending</option>
                                <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Ascending</option>
                            </select>
                        </div>
                    </div>

                    <!-- Results per page -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="per_page" class="form-label">Results per Page</label>
                            <select class="form-select" id="per_page" name="per_page">
                                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                        <i class="fas fa-eraser me-2"></i>Clear All
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick filter handling
    const quickFilters = document.querySelectorAll('input[name="quick_filter"]');
    const dateFromInput = document.getElementById('filter_date_from');
    const dateToInput = document.getElementById('filter_date_to');

    quickFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            if (this.checked) {
                const today = new Date();
                let fromDate = new Date();
                let toDate = new Date();

                switch(this.value) {
                    case 'today':
                        fromDate = toDate = today;
                        break;
                    case 'yesterday':
                        fromDate = new Date(today);
                        fromDate.setDate(today.getDate() - 1);
                        toDate = fromDate;
                        break;
                    case 'this_week':
                        fromDate = new Date(today);
                        fromDate.setDate(today.getDate() - today.getDay());
                        toDate = today;
                        break;
                    case 'last_week':
                        fromDate = new Date(today);
                        fromDate.setDate(today.getDate() - today.getDay() - 7);
                        toDate = new Date(fromDate);
                        toDate.setDate(fromDate.getDate() + 6);
                        break;
                    case 'this_month':
                        fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                        toDate = today;
                        break;
                    case 'last_month':
                        fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        toDate = new Date(today.getFullYear(), today.getMonth(), 0);
                        break;
                }

                dateFromInput.value = fromDate.toISOString().split('T')[0];
                dateToInput.value = toDate.toISOString().split('T')[0];
            }
        });
    });

    // Clear manual date inputs when quick filter is selected
    dateFromInput.addEventListener('change', function() {
        quickFilters.forEach(filter => filter.checked = false);
    });

    dateToInput.addEventListener('change', function() {
        quickFilters.forEach(filter => filter.checked = false);
    });
});

function clearFilters() {
    const filterForm = document.getElementById('filterForm');
    const inputs = filterForm.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        if (input.type === 'radio' || input.type === 'checkbox') {
            input.checked = false;
        } else {
            input.value = '';
        }
    });
    
    // Reset to default sort
    document.getElementById('sort_by').value = 'created_at';
    document.getElementById('sort_order').value = 'desc';
    document.getElementById('per_page').value = '20';
}
</script>