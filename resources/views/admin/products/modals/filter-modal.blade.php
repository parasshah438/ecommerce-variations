<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">
                    <i class="fas fa-filter me-2"></i>Filter Products
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="filterForm" action="{{ route('admin.products.index') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <!-- Search Filter -->
                        <div class="col-md-12 mb-3">
                            <label for="filter_search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="filter_search" name="search" 
                                   placeholder="Search by product name, slug, HSN code, or description..." 
                                   value="{{ request('search') }}">
                            <div class="form-text">Search in product name, slug, HSN code, and description</div>
                        </div>

                        <!-- Category Filter -->
                        <div class="col-md-6 mb-3">
                            <label for="filter_category" class="form-label">Category</label>
                            <select class="form-select" id="filter_category" name="category_id">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Brand Filter -->
                        <div class="col-md-6 mb-3">
                            <label for="filter_brand" class="form-label">Brand</label>
                            <select class="form-select" id="filter_brand" name="brand_id">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-6 mb-3">
                            <label for="filter_status" class="form-label">Status</label>
                            <select class="form-select" id="filter_status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Has Variations Filter -->
                        <div class="col-md-6 mb-3">
                            <label for="filter_has_variations" class="form-label">Product Type</label>
                            <select class="form-select" id="filter_has_variations" name="has_variations">
                                <option value="">All Products</option>
                                <option value="yes" {{ request('has_variations') === 'yes' ? 'selected' : '' }}>With Variations</option>
                                <option value="no" {{ request('has_variations') === 'no' ? 'selected' : '' }}>Simple Products</option>
                            </select>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="col-md-6 mb-3">
                            <label for="filter_price_min" class="form-label">Min Price (₹)</label>
                            <input type="number" class="form-control" id="filter_price_min" name="price_min" 
                                   min="0" step="0.01" placeholder="0.00" value="{{ request('price_min') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="filter_price_max" class="form-label">Max Price (₹)</label>
                            <input type="number" class="form-control" id="filter_price_max" name="price_max" 
                                   min="0" step="0.01" placeholder="0.00" value="{{ request('price_max') }}">
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
                    </div>

                    <!-- Quick Date Filters -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Quick Date Filters</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="quick_filter" id="today" value="today"
                                    {{ request('quick_filter') === 'today' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm" for="today">Today</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="yesterday" value="yesterday"
                                    {{ request('quick_filter') === 'yesterday' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm" for="yesterday">Yesterday</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="this_week" value="this_week"
                                    {{ request('quick_filter') === 'this_week' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm" for="this_week">This Week</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="last_week" value="last_week"
                                    {{ request('quick_filter') === 'last_week' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm" for="last_week">Last Week</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="this_month" value="this_month"
                                    {{ request('quick_filter') === 'this_month' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm" for="this_month">This Month</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="last_month" value="last_month"
                                    {{ request('quick_filter') === 'last_month' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm" for="last_month">Last Month</label>
                            </div>
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <option value="created_at" {{ request('sort_by', 'created_at') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                                <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Product Name</option>
                                <option value="price" {{ request('sort_by') === 'price' ? 'selected' : '' }}>Price</option>
                                <option value="category" {{ request('sort_by') === 'category' ? 'selected' : '' }}>Category</option>
                                <option value="brand" {{ request('sort_by') === 'brand' ? 'selected' : '' }}>Brand</option>
                                <option value="active" {{ request('sort_by') === 'active' ? 'selected' : '' }}>Status</option>
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
