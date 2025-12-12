@extends('admin.layout')

@section('title', 'Out of Stock Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-times-circle text-danger me-2"></i>Out of Stock Report
            </h1>
            <p class="text-muted mb-0">Products with zero stock quantity</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.stock.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
            <button class="btn btn-warning" onclick="bulkRestock()">
                <i class="fas fa-plus me-1"></i>Bulk Restock
            </button>
            <a href="{{ route('admin.stock.export') }}" class="btn btn-success">
                <i class="fas fa-download me-1"></i>Export
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Out of Stock Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $outOfStockProducts->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Potential Lost Sales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $totalValue = $outOfStockProducts->sum(function($variation) {
                                        return $variation->price;
                                    });
                                @endphp
                                ₹{{ number_format($totalValue, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg. Days Out of Stock
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $avgDays = $outOfStockProducts->avg(function($variation) {
                                        return $variation->stock ? $variation->stock->updated_at->diffInDays(now()) : 0;
                                    });
                                @endphp
                                {{ round($avgDays, 1) }} days
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Out of Stock Products Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-danger">
                <i class="fas fa-list me-2"></i>Out of Stock Products
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-primary" onclick="selectAll()">
                    <i class="fas fa-check-double"></i> Select All
                </button>
                <button class="btn btn-sm btn-warning" onclick="bulkRestock()">
                    <i class="fas fa-plus"></i> Bulk Restock
                </button>
                <button class="btn btn-sm btn-info" onclick="exportSelected()">
                    <i class="fas fa-file-export"></i> Export Selected
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($outOfStockProducts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="outOfStockTable">
                        <thead class="table-light">
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                                </th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Variation</th>
                                <th>Price</th>
                                <th>Days Out</th>
                                <th>Priority</th>
                                <th>Last Sale</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outOfStockProducts as $variation)
                            <tr>
                                <td>
                                    <input type="checkbox" class="product-checkbox" value="{{ $variation->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($variation->product->images->count() > 0)
                                            <img src="{{ $variation->product->getThumbnailImage() ? $variation->product->getThumbnailImage()->getThumbnailUrl(150) : asset('images/product-placeholder.jpg') }}" 
                                                 alt="{{ $variation->product->name }}" 
                                                 class="rounded me-3" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $variation->product->name }}</div>
                                            <small class="text-muted">{{ Str::limit($variation->product->description, 50) }}</small>
                                            <div class="mt-1">
                                                <span class="badge bg-secondary">{{ $variation->product->category->name ?? 'No Category' }}</span>
                                            </div>
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
                                        @foreach($attributeValues as $attr)
                                            <div class="mb-1">
                                                <span class="badge bg-info">
                                                    {{ $attr->attribute->name }}: {{ $attr->value }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Default variant</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-success">₹{{ number_format($variation->price, 2) }}</div>
                                    @if($variation->product->price != $variation->price)
                                        <small class="text-muted">Base: ₹{{ number_format($variation->product->price, 2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $daysOut = $variation->stock ? $variation->stock->updated_at->diffInDays(now()) : 0;
                                    @endphp
                                    <div class="text-center">
                                        <span class="badge bg-{{ $daysOut > 30 ? 'danger' : ($daysOut > 7 ? 'warning' : 'info') }} fs-6">
                                            {{ $daysOut }} days
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $priority = 'low';
                                        $priorityClass = 'secondary';
                                        if ($daysOut <= 3) {
                                            $priority = 'urgent';
                                            $priorityClass = 'danger';
                                        } elseif ($daysOut <= 7) {
                                            $priority = 'high';
                                            $priorityClass = 'warning';
                                        } elseif ($daysOut <= 14) {
                                            $priority = 'medium';
                                            $priorityClass = 'info';
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $priorityClass }} text-uppercase">{{ $priority }}</span>
                                </td>
                                <td>
                                    <div class="text-muted">
                                        <small>No sales data</small>
                                    </div>
                                    {{-- You can implement this based on your order system --}}
                                </td>
                                <td>
                                    <div class="btn-group-vertical d-grid gap-1" role="group">
                                        <button class="btn btn-sm btn-success" 
                                                onclick="quickRestock({{ $variation->id }})"
                                                title="Quick Restock">
                                            <i class="fas fa-plus"></i> Restock
                                        </button>
                                        <button class="btn btn-sm btn-info" 
                                                onclick="viewDetails({{ $variation->id }})"
                                                title="View Details">
                                            <i class="fas fa-eye"></i> Details
                                        </button>
                                        <button class="btn btn-sm btn-warning" 
                                                onclick="notifySupplier({{ $variation->id }})"
                                                title="Notify Supplier">
                                            <i class="fas fa-bell"></i> Notify
                                        </button>
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
                            Showing {{ $outOfStockProducts->firstItem() ?? 0 }} to {{ $outOfStockProducts->lastItem() ?? 0 }} 
                            of {{ $outOfStockProducts->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $outOfStockProducts->onEachSide(1)->links('custom.compact-pagination') }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                    <h4>Excellent Stock Management!</h4>
                    <p class="text-muted">No products are currently out of stock. Keep up the great work!</p>
                    <a href="{{ route('admin.stock.dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Restock Modal -->
<div class="modal fade" id="quickRestockModal" tabindex="-1" aria-labelledby="quickRestockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickRestockModalLabel">
                    <i class="fas fa-plus me-2"></i>Quick Restock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickRestockForm">
                    <input type="hidden" id="restockVariationId">
                    <div class="mb-3">
                        <label for="restockQuantity" class="form-label">Restock Quantity</label>
                        <input type="number" class="form-control form-control-lg" id="restockQuantity" min="1" value="10" required>
                        <div class="form-text">Enter the quantity to add to stock.</div>
                    </div>
                    <div class="mb-3">
                        <label for="restockReason" class="form-label">Reason</label>
                        <select class="form-select" id="restockReason">
                            <option value="new_stock">New Stock Received</option>
                            <option value="inventory_correction">Inventory Correction</option>
                            <option value="return_to_stock">Return to Stock</option>
                            <option value="emergency_restock">Emergency Restock</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="supplierNote" class="form-label">Supplier Note (Optional)</label>
                        <textarea class="form-control" id="supplierNote" rows="2" placeholder="Any notes about this restock..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveQuickRestock()">
                    <i class="fas fa-plus me-1"></i>Add to Stock
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Restock Modal -->
<div class="modal fade" id="bulkRestockModal" tabindex="-1" aria-labelledby="bulkRestockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkRestockModalLabel">
                    <i class="fas fa-plus me-2"></i>Bulk Restock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="selectedProductsForRestock"></div>
                <form id="bulkRestockForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bulkRestockQuantity" class="form-label">Quantity for All Selected</label>
                                <input type="number" class="form-control" id="bulkRestockQuantity" min="1" value="10" placeholder="Enter quantity">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bulkRestockReason" class="form-label">Reason</label>
                                <select class="form-select" id="bulkRestockReason">
                                    <option value="bulk_restock">Bulk Restock</option>
                                    <option value="new_stock">New Stock Received</option>
                                    <option value="inventory_correction">Inventory Correction</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="bulkSupplierNote" class="form-label">Note</label>
                        <textarea class="form-control" id="bulkSupplierNote" rows="2" placeholder="Bulk restock note..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveBulkRestock()">
                    <i class="fas fa-plus me-1"></i>Restock All Selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal fade" id="productDetailsModal" tabindex="-1" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productDetailsModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Product Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="productDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Quick restock functions
function quickRestock(variationId) {
    document.getElementById('restockVariationId').value = variationId;
    document.getElementById('restockQuantity').focus();
    new bootstrap.Modal(document.getElementById('quickRestockModal')).show();
}

function saveQuickRestock() {
    const variationId = document.getElementById('restockVariationId').value;
    const quantity = document.getElementById('restockQuantity').value;
    
    if (!quantity || quantity < 1) {
        alert('Please enter a valid quantity.');
        return;
    }
    
    fetch(`/admin/stock/api/update-stock/${variationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            quantity: parseInt(quantity),
            reason: document.getElementById('restockReason').value,
            note: document.getElementById('supplierNote').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('quickRestockModal')).hide();
            showAlert('success', `Stock updated successfully! Added ${quantity} items.`);
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

// Selection functions
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    
    productCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function selectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    selectAllCheckbox.checked = true;
    toggleSelectAll();
}

function bulkRestock() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one product to restock.');
        return;
    }
    
    document.getElementById('selectedProductsForRestock').innerHTML = `
        <div class="alert alert-info">
            <strong>${selectedCheckboxes.length}</strong> products selected for bulk restock.
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('bulkRestockModal')).show();
}

function saveBulkRestock() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    const quantity = document.getElementById('bulkRestockQuantity').value;
    
    if (!quantity || quantity < 1) {
        alert('Please enter a valid quantity.');
        return;
    }
    
    const updates = Array.from(selectedCheckboxes).map(cb => ({
        variation_id: cb.value,
        quantity: parseInt(quantity)
    }));
    
    fetch('{{ route("admin.stock.bulk_update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ updates: updates })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('bulkRestockModal')).hide();
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while updating stock.');
    });
}

// Other functions
function viewDetails(variationId) {
    // Implement product details view
    showAlert('info', 'Product details feature coming soon!');
}

function notifySupplier(variationId) {
    // Implement supplier notification
    showAlert('info', 'Supplier notification feature coming soon!');
}

function exportSelected() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one product to export.');
        return;
    }
    showAlert('info', 'Export selected feature coming soon!');
}

// Utility function
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
    // Focus on search or filter elements if needed
});
</script>
@endsection

@section('styles')
<style>
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
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
}

.table td {
    vertical-align: middle;
}

.btn-group-vertical .btn {
    border-radius: 0.25rem !important;
    margin-bottom: 2px;
}

.badge {
    font-size: 0.75rem;
}

.form-control-lg {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
}

.product-checkbox, #selectAllCheckbox {
    transform: scale(1.2);
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group-vertical {
        width: 100%;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
}

.card:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease-in-out;
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