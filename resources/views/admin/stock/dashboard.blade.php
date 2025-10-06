@extends('admin.layout')

@section('title', 'Stock Management Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-boxes me-2"></i>Stock Management Dashboard
            </h1>
            <p class="text-muted mb-0">Monitor and manage your inventory in real-time</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                <i class="fas fa-edit me-1"></i>Bulk Update
            </button>
            <a href="{{ route('admin.stock.export') }}" class="btn btn-success">
                <i class="fas fa-download me-1"></i>Export Report
            </a>
            <button class="btn btn-info" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Stock Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Variations
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalVariations) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cubes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                In Stock
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($inStock) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Low Stock
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($lowStock) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Out of Stock
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($outOfStock) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.stock.low_stock_report') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                View Low Stock Items
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.stock.out_of_stock_report') }}" class="btn btn-danger btn-block">
                                <i class="fas fa-times-circle me-2"></i>
                                View Out of Stock Items
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.stock.movement_report') }}" class="btn btn-info btn-block">
                                <i class="fas fa-chart-line me-2"></i>
                                Stock Movement Report
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-secondary btn-block" onclick="generateStockReport()">
                                <i class="fas fa-file-alt me-2"></i>
                                Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Alerts -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bell me-2"></i>Stock Alerts
                    </h6>
                    <button class="btn btn-sm btn-primary" onclick="loadStockAlerts()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div id="stockAlerts">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Stock Updates -->
    <div class="row">
        <!-- Low Stock Products -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Low Stock Products</h6>
                </div>
                <div class="card-body">
                    @if($lowStockProducts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockProducts as $variation)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $variation->product->name }}</div>
                                            <small class="text-muted">
                                                @php $attributeValues = $variation->attributeValues(); @endphp
                                                @foreach($attributeValues as $attr)
                                                    {{ $attr->attribute->name }}: {{ $attr->value }}
                                                    @if(!$loop->last), @endif
                                                @endforeach
                                            </small>
                                        </td>
                                        <td><code>{{ $variation->sku }}</code></td>
                                        <td>
                                            <span class="badge bg-warning">{{ $variation->stock->quantity ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="quickUpdate({{ $variation->id }}, {{ $variation->stock->quantity ?? 0 }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.stock.low_stock_report') }}" class="btn btn-warning btn-sm">
                                View All Low Stock Items
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">No low stock items found!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Out of Stock Products -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Out of Stock Products</h6>
                </div>
                <div class="card-body">
                    @if($outOfStockProducts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($outOfStockProducts as $variation)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $variation->product->name }}</div>
                                            <small class="text-muted">
                                                @php $attributeValues = $variation->attributeValues(); @endphp
                                                @foreach($attributeValues as $attr)
                                                    {{ $attr->attribute->name }}: {{ $attr->value }}
                                                    @if(!$loop->last), @endif
                                                @endforeach
                                            </small>
                                        </td>
                                        <td><code>{{ $variation->sku }}</code></td>
                                        <td>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="quickUpdate({{ $variation->id }}, 0)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.stock.out_of_stock_report') }}" class="btn btn-danger btn-sm">
                                View All Out of Stock Items
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">No out of stock items!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-labelledby="bulkUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkUpdateModalLabel">
                    <i class="fas fa-edit me-2"></i>Bulk Stock Update
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="bulkUpdateForm">
                    <p class="text-muted">Upload a CSV file or manually enter stock updates.</p>
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">Upload CSV File</label>
                        <input type="file" class="form-control" id="csvFile" accept=".csv">
                        <div class="form-text">CSV should have columns: variation_id, quantity</div>
                    </div>
                    <div class="text-center">
                        <strong>OR</strong>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Manual Updates</label>
                        <div id="manualUpdates">
                            <!-- Manual update rows will be added here -->
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addManualUpdateRow()">
                            <i class="fas fa-plus"></i> Add Row
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="processBulkUpdate()">
                    <i class="fas fa-save me-1"></i>Update Stock
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Update Modal -->
<div class="modal fade" id="quickUpdateModal" tabindex="-1" aria-labelledby="quickUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickUpdateModalLabel">Quick Stock Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickUpdateForm">
                    <input type="hidden" id="quickVariationId">
                    <div class="mb-3">
                        <label for="quickQuantity" class="form-label">New Quantity</label>
                        <input type="number" class="form-control" id="quickQuantity" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveQuickUpdate()">
                    <i class="fas fa-save me-1"></i>Update
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Load stock alerts
function loadStockAlerts() {
    fetch('{{ route("admin.stock.api.alerts") }}')
        .then(response => response.json())
        .then(data => {
            const alertsContainer = document.getElementById('stockAlerts');
            if (data.alerts.length === 0) {
                alertsContainer.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">No stock alerts at this time!</p>
                    </div>
                `;
            } else {
                let alertsHtml = '';
                data.alerts.forEach(alert => {
                    alertsHtml += `
                        <div class="alert alert-${alert.type} alert-dismissible fade show" role="alert">
                            <strong>${alert.title}:</strong> ${alert.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                });
                alertsContainer.innerHTML = alertsHtml;
            }
        })
        .catch(error => {
            console.error('Error loading alerts:', error);
            document.getElementById('stockAlerts').innerHTML = `
                <div class="alert alert-danger" role="alert">
                    Error loading stock alerts. Please try again.
                </div>
            `;
        });
}

// Quick update functions
function quickUpdate(variationId, currentQuantity) {
    document.getElementById('quickVariationId').value = variationId;
    document.getElementById('quickQuantity').value = currentQuantity;
    new bootstrap.Modal(document.getElementById('quickUpdateModal')).show();
}

function saveQuickUpdate() {
    const variationId = document.getElementById('quickVariationId').value;
    const quantity = document.getElementById('quickQuantity').value;
    
    fetch(`/admin/stock/api/update-stock/${variationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ quantity: parseInt(quantity) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('quickUpdateModal')).hide();
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while updating stock.');
    });
}

// Bulk update functions
function addManualUpdateRow() {
    const container = document.getElementById('manualUpdates');
    const rowId = Date.now();
    const row = document.createElement('div');
    row.className = 'row mb-2';
    row.innerHTML = `
        <div class="col-md-6">
            <input type="text" class="form-control" placeholder="Variation ID" name="variation_id[]">
        </div>
        <div class="col-md-4">
            <input type="number" class="form-control" placeholder="Quantity" name="quantity[]" min="0">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
}

function processBulkUpdate() {
    // Implementation for bulk update processing
    showAlert('info', 'Bulk update functionality coming soon!');
}

// Utility functions
function refreshData() {
    location.reload();
}

function generateStockReport() {
    window.open('{{ route("admin.stock.export") }}', '_blank');
}

function showAlert(type, message) {
    const alertsContainer = document.getElementById('stockAlerts');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    alertsContainer.prepend(alert);
}

// Load alerts on page load
document.addEventListener('DOMContentLoaded', function() {
    loadStockAlerts();
});

// Auto-refresh alerts every 5 minutes
setInterval(loadStockAlerts, 300000);
</script>
@endsection

@section('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.card {
    transition: all 0.3s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.12) !important;
}

.btn-block {
    width: 100%;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.spinner-border {
    width: 2rem;
    height: 2rem;
}

@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .btn-block {
        margin-bottom: 0.5rem;
    }
}
</style>
@endsection