@extends('admin.layout')

@section('title', 'Stock Movement Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line text-info me-2"></i>Stock Movement Report
            </h1>
            <p class="text-muted mb-0">Track inventory changes and stock history</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.stock.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-filter me-1"></i>Filter
            </button>
            <a href="{{ route('admin.stock.export') }}" class="btn btn-success">
                <i class="fas fa-download me-1"></i>Export
            </a>
        </div>
    </div>

    <!-- Filter Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-info shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="text-info mb-2">Current View: All Stock Movements</h6>
                            <p class="text-muted mb-0">
                                Showing stock data for {{ $variations->total() }} product variations. 
                                <span class="badge bg-info">{{ $variations->count() }} items on this page</span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <button class="btn btn-sm btn-outline-info" onclick="refreshData()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="exportCurrentView()">
                                    <i class="fas fa-file-export"></i> Export View
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movement Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Variations
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $variations->total() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cubes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Stock Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $totalValue = $variations->sum(function($variation) {
                                        $quantity = $variation->stock ? $variation->stock->quantity : 0;
                                        return $quantity * $variation->price;
                                    });
                                @endphp
                                ₹{{ number_format($totalValue, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Need Attention
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $needAttention = $variations->filter(function($variation) {
                                        $quantity = $variation->stock ? $variation->stock->quantity : 0;
                                        return $quantity <= 10;
                                    })->count();
                                @endphp
                                {{ $needAttention }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg. Stock Level
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $avgStock = $variations->avg(function($variation) {
                                        return $variation->stock ? $variation->stock->quantity : 0;
                                    });
                                @endphp
                                {{ round($avgStock, 1) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movement Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-info">
                <i class="fas fa-table me-2"></i>Stock Movement Details
            </h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-sort"></i> Sort By
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="sortTable('product')">Product Name</a></li>
                    <li><a class="dropdown-item" href="#" onclick="sortTable('sku')">SKU</a></li>
                    <li><a class="dropdown-item" href="#" onclick="sortTable('stock')">Stock Quantity</a></li>
                    <li><a class="dropdown-item" href="#" onclick="sortTable('value')">Stock Value</a></li>
                    <li><a class="dropdown-item" href="#" onclick="sortTable('updated')">Last Updated</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            @if($variations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="movementTable">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Variation</th>
                                <th>Current Stock</th>
                                <th>Stock Value</th>
                                <th>Status</th>
                                <th>Last Movement</th>
                                <th>Stock Trend</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($variations as $variation)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($variation->product->images->count() > 0)
                                            <img src="{{ asset('storage/' . $variation->product->images->first()->image_path) }}" 
                                                 alt="{{ $variation->product->name }}" 
                                                 class="rounded me-3" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $variation->product->name }}</div>
                                            <small class="text-muted">{{ $variation->product->category->name ?? 'No Category' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="bg-light px-2 py-1 rounded">{{ $variation->sku }}</code>
                                    <div class="mt-1">
                                        <small class="text-muted">ID: {{ $variation->id }}</small>
                                    </div>
                                </td>
                                <td>
                                    @php $attributeValues = $variation->attributeValues(); @endphp
                                    @if($attributeValues->count() > 0)
                                        @foreach($attributeValues->take(2) as $attr)
                                            <div class="mb-1">
                                                <span class="badge bg-secondary">
                                                    {{ $attr->attribute->name }}: {{ $attr->value }}
                                                </span>
                                            </div>
                                        @endforeach
                                        @if($attributeValues->count() > 2)
                                            <small class="text-muted">+{{ $attributeValues->count() - 2 }} more</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Default</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $quantity = $variation->stock ? $variation->stock->quantity : 0;
                                    @endphp
                                    <div class="text-center">
                                        @if($quantity == 0)
                                            <span class="badge bg-danger fs-6">{{ $quantity }}</span>
                                        @elseif($quantity <= 5)
                                            <span class="badge bg-warning fs-6">{{ $quantity }}</span>
                                        @elseif($quantity <= 10)
                                            <span class="badge bg-info fs-6">{{ $quantity }}</span>
                                        @else
                                            <span class="badge bg-success fs-6">{{ $quantity }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="adjustStock({{ $variation->id }}, {{ $quantity }})"
                                                title="Adjust Stock">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $stockValue = $quantity * $variation->price;
                                    @endphp
                                    <div class="fw-bold">₹{{ number_format($stockValue, 2) }}</div>
                                    <small class="text-muted">@ ₹{{ number_format($variation->price, 2) }} each</small>
                                </td>
                                <td>
                                    @if($quantity == 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($quantity <= 5)
                                        <span class="badge bg-warning">Critical</span>
                                    @elseif($quantity <= 10)
                                        <span class="badge bg-info">Low</span>
                                    @else
                                        <span class="badge bg-success">Good</span>
                                    @endif
                                </td>
                                <td>
                                    @if($variation->stock)
                                        <div>{{ $variation->stock->updated_at->format('M j, Y') }}</div>
                                        <small class="text-muted">{{ $variation->stock->updated_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">No movement</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        @php
                                            // Simulate trend (you can implement actual trend calculation)
                                            $trend = rand(0, 2);
                                        @endphp
                                        @if($trend == 0)
                                            <i class="fas fa-arrow-down text-danger" title="Decreasing"></i>
                                            <span class="ms-1 text-danger">↓</span>
                                        @elseif($trend == 1)
                                            <i class="fas fa-arrow-up text-success" title="Increasing"></i>
                                            <span class="ms-1 text-success">↑</span>
                                        @else
                                            <i class="fas fa-minus text-info" title="Stable"></i>
                                            <span class="ms-1 text-info">→</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="viewHistory({{ $variation->id }})"
                                                title="View History">
                                            <i class="fas fa-history"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="adjustStock({{ $variation->id }}, {{ $quantity }})"
                                                title="Adjust Stock">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="{{ route('admin.products.show', $variation->product->id) }}" 
                                           class="btn btn-sm btn-outline-secondary"
                                           title="View Product">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap">
                    <div class="mb-2 mb-md-0">
                        <small class="text-muted">
                            Showing {{ $variations->firstItem() ?? 0 }} to {{ $variations->lastItem() ?? 0 }} 
                            of {{ $variations->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $variations->onEachSide(1)->links('custom.compact-pagination') }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-4x text-muted mb-4"></i>
                    <h4>No Stock Movement Data</h4>
                    <p class="text-muted">No stock movement records found. Start by adding products and managing inventory.</p>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Products
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">
                    <i class="fas fa-filter me-2"></i>Filter Stock Movement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dateFrom" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="dateFrom">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dateTo" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="dateTo">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="stockStatus" class="form-label">Stock Status</label>
                                <select class="form-select" id="stockStatus">
                                    <option value="">All Status</option>
                                    <option value="in_stock">In Stock</option>
                                    <option value="low_stock">Low Stock</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category">
                                    <option value="">All Categories</option>
                                    <!-- Add categories dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="minQuantity" class="form-label">Min Quantity</label>
                                <input type="number" class="form-control" id="minQuantity" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="maxQuantity" class="form-label">Max Quantity</label>
                                <input type="number" class="form-control" id="maxQuantity" min="0">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="clearFilters()">Clear All</button>
                <button type="button" class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter me-1"></i>Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1" aria-labelledby="stockAdjustmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockAdjustmentModalLabel">
                    <i class="fas fa-edit me-2"></i>Adjust Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="stockAdjustmentForm">
                    <input type="hidden" id="adjustVariationId">
                    <div class="mb-3">
                        <label for="currentStock" class="form-label">Current Stock</label>
                        <input type="number" class="form-control" id="currentStock" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="newStock" class="form-label">New Stock Quantity</label>
                        <input type="number" class="form-control form-control-lg" id="newStock" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="adjustmentReason" class="form-label">Reason for Adjustment</label>
                        <select class="form-select" id="adjustmentReason">
                            <option value="stock_received">Stock Received</option>
                            <option value="stock_sold">Stock Sold</option>
                            <option value="damaged_goods">Damaged Goods</option>
                            <option value="inventory_correction">Inventory Correction</option>
                            <option value="theft_loss">Theft/Loss</option>
                            <option value="return">Return to Stock</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="adjustmentNote" class="form-label">Note (Optional)</label>
                        <textarea class="form-control" id="adjustmentNote" rows="2" placeholder="Additional notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveStockAdjustment()">
                    <i class="fas fa-save me-1"></i>Save Adjustment
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Stock adjustment functions
function adjustStock(variationId, currentQuantity) {
    document.getElementById('adjustVariationId').value = variationId;
    document.getElementById('currentStock').value = currentQuantity;
    document.getElementById('newStock').value = currentQuantity;
    document.getElementById('newStock').focus();
    new bootstrap.Modal(document.getElementById('stockAdjustmentModal')).show();
}

function saveStockAdjustment() {
    const variationId = document.getElementById('adjustVariationId').value;
    const newStock = document.getElementById('newStock').value;
    
    if (newStock === '' || newStock < 0) {
        alert('Please enter a valid stock quantity.');
        return;
    }
    
    fetch(`/admin/stock/api/update-stock/${variationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            quantity: parseInt(newStock),
            reason: document.getElementById('adjustmentReason').value,
            note: document.getElementById('adjustmentNote').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('stockAdjustmentModal')).hide();
            showAlert('success', 'Stock adjusted successfully!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while adjusting stock.');
    });
}

// Filter functions
function applyFilters() {
    // Implementation for applying filters
    showAlert('info', 'Filter functionality coming soon!');
    bootstrap.Modal.getInstance(document.getElementById('filterModal')).hide();
}

function clearFilters() {
    document.getElementById('filterForm').reset();
}

// Utility functions
function viewHistory(variationId) {
    showAlert('info', 'Stock history view coming soon!');
}

function sortTable(column) {
    showAlert('info', `Sorting by ${column} coming soon!`);
}

function refreshData() {
    location.reload();
}

function exportCurrentView() {
    window.open('{{ route("admin.stock.export") }}', '_blank');
}

function showAlert(type, message) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 5000);
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    document.getElementById('dateTo').value = today.toISOString().split('T')[0];
    document.getElementById('dateFrom').value = thirtyDaysAgo.toISOString().split('T')[0];
});
</script>
@endsection

@section('styles')
<style>
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background-color: #f8f9fc !important;
}

.table td {
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: rgba(54, 185, 204, 0.1);
}

.btn-group .btn {
    border-radius: 0.25rem !important;
    margin-right: 2px;
}

.badge {
    font-size: 0.75rem;
}

.form-control-lg {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
}

.card {
    transition: all 0.3s ease-in-out;
}

.card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.12) !important;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        margin-bottom: 2px;
        margin-right: 0;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
}

.text-success {
    color: #1cc88a !important;
}

.text-danger {
    color: #e74a3b !important;
}

.text-info {
    color: #36b9cc !important;
}

/* Compact Pagination Styles */
.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    padding: 0.375rem 0.75rem;
    margin: 0 2px;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
    color: #6c757d;
    font-size: 0.875rem;
    min-width: 40px;
    text-align: center;
}

.pagination .page-link:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
    color: #495057;
    text-decoration: none;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
    cursor: not-allowed;
}

@media (max-width: 576px) {
    .pagination .page-link {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
        margin: 0 1px;
        min-width: 32px;
    }
    
    .pagination .page-item:not(.active):not(:first-child):not(:last-child) {
        display: none;
    }
    
    /* Show only prev/next on mobile */
    .pagination .page-item:first-child,
    .pagination .page-item:last-child,
    .pagination .page-item.active {
        display: inline-block !important;
    }
}
</style>
@endsection