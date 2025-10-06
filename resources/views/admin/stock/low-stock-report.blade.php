@extends('admin.layout')

@section('title', 'Low Stock Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-exclamation-triangle text-warning me-2"></i>Low Stock Report
            </h1>
            <p class="text-muted mb-0">Products with stock between 1-10 items</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.stock.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
            <a href="{{ route('admin.stock.export') }}" class="btn btn-success">
                <i class="fas fa-download me-1"></i>Export
            </a>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-warning shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Low Stock Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $lowStockProducts->total() }} Products
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Products Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-warning">
                <i class="fas fa-list me-2"></i>Low Stock Products
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-primary" onclick="selectAll()">
                    <i class="fas fa-check-double"></i> Select All
                </button>
                <button class="btn btn-sm btn-warning" onclick="bulkUpdate()">
                    <i class="fas fa-edit"></i> Bulk Update
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($lowStockProducts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="lowStockTable">
                        <thead class="table-light">
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                                </th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Variation</th>
                                <th>Current Stock</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $variation)
                            <tr>
                                <td>
                                    <input type="checkbox" class="product-checkbox" value="{{ $variation->id }}">
                                </td>
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
                                            <small class="text-muted">{{ Str::limit($variation->product->description, 60) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="bg-light px-2 py-1 rounded">{{ $variation->sku }}</code>
                                </td>
                                <td>
                                    @php $attributeValues = $variation->attributeValues(); @endphp
                                    @if($attributeValues->count() > 0)
                                        @foreach($attributeValues as $attr)
                                            <span class="badge bg-secondary me-1">
                                                {{ $attr->attribute->name }}: {{ $attr->value }}
                                            </span>
                                            @if(!$loop->last)<br>@endif
                                        @endforeach
                                    @else
                                        <span class="text-muted">No variations</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-warning fs-6 me-2">
                                            {{ $variation->stock->quantity ?? 0 }}
                                        </span>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="quickUpdate({{ $variation->id }}, {{ $variation->stock->quantity ?? 0 }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $quantity = $variation->stock->quantity ?? 0;
                                    @endphp
                                    @if($quantity == 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($quantity <= 5)
                                        <span class="badge bg-warning">Critical Low</span>
                                    @else
                                        <span class="badge bg-info">Low Stock</span>
                                    @endif
                                </td>
                                <td>
                                    @if($variation->stock)
                                        <div>{{ $variation->stock->updated_at->format('M j, Y') }}</div>
                                        <small class="text-muted">{{ $variation->stock->updated_at->format('H:i A') }}</small>
                                    @else
                                        <span class="text-muted">No record</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="quickUpdate({{ $variation->id }}, {{ $variation->stock->quantity ?? 0 }})"
                                                title="Update Stock">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="{{ route('admin.products.show', $variation->product->id) }}" 
                                           class="btn btn-sm btn-info"
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
                            Showing {{ $lowStockProducts->firstItem() ?? 0 }} to {{ $lowStockProducts->lastItem() ?? 0 }} 
                            of {{ $lowStockProducts->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $lowStockProducts->onEachSide(1)->links('custom.compact-pagination') }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                    <h4>Great News!</h4>
                    <p class="text-muted">No products with low stock found. Your inventory levels are healthy!</p>
                    <a href="{{ route('admin.stock.dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Update Modal -->
<div class="modal fade" id="quickUpdateModal" tabindex="-1" aria-labelledby="quickUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickUpdateModalLabel">
                    <i class="fas fa-edit me-2"></i>Update Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickUpdateForm">
                    <input type="hidden" id="quickVariationId">
                    <div class="mb-3">
                        <label for="quickQuantity" class="form-label">New Stock Quantity</label>
                        <input type="number" class="form-control form-control-lg" id="quickQuantity" min="0" required>
                        <div class="form-text">Enter the new stock quantity for this product variation.</div>
                    </div>
                    <div class="mb-3">
                        <label for="updateReason" class="form-label">Reason for Update (Optional)</label>
                        <select class="form-select" id="updateReason">
                            <option value="">Select reason...</option>
                            <option value="stock_received">Stock Received</option>
                            <option value="inventory_correction">Inventory Correction</option>
                            <option value="damaged_goods">Damaged Goods Removed</option>
                            <option value="theft_loss">Theft/Loss</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveQuickUpdate()">
                    <i class="fas fa-save me-1"></i>Update Stock
                </button>
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
                <div id="selectedProducts"></div>
                <form id="bulkUpdateForm">
                    <div class="mb-3">
                        <label for="bulkQuantity" class="form-label">Set Quantity for All Selected</label>
                        <input type="number" class="form-control" id="bulkQuantity" min="0" placeholder="Enter quantity">
                    </div>
                    <div class="mb-3">
                        <label for="bulkReason" class="form-label">Reason for Update</label>
                        <select class="form-select" id="bulkReason">
                            <option value="">Select reason...</option>
                            <option value="stock_received">Stock Received</option>
                            <option value="inventory_correction">Inventory Correction</option>
                            <option value="bulk_update">Bulk Update</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveBulkUpdate()">
                    <i class="fas fa-save me-1"></i>Update All Selected
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Quick update functions
function quickUpdate(variationId, currentQuantity) {
    document.getElementById('quickVariationId').value = variationId;
    document.getElementById('quickQuantity').value = currentQuantity;
    document.getElementById('quickQuantity').focus();
    new bootstrap.Modal(document.getElementById('quickUpdateModal')).show();
}

function saveQuickUpdate() {
    const variationId = document.getElementById('quickVariationId').value;
    const quantity = document.getElementById('quickQuantity').value;
    
    if (!quantity || quantity < 0) {
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
            reason: document.getElementById('updateReason').value
        })
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

function bulkUpdate() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one product to update.');
        return;
    }
    
    const selectedProducts = Array.from(selectedCheckboxes).map(cb => cb.value);
    document.getElementById('selectedProducts').innerHTML = `
        <div class="alert alert-info">
            <strong>${selectedProducts.length}</strong> products selected for bulk update.
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('bulkUpdateModal')).show();
}

function saveBulkUpdate() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    const quantity = document.getElementById('bulkQuantity').value;
    
    if (!quantity || quantity < 0) {
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
            bootstrap.Modal.getInstance(document.getElementById('bulkUpdateModal')).hide();
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
    // Add any initialization code here
});
</script>
@endsection

@section('styles')
<style>
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
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

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 2px;
        margin-right: 0;
    }
}

.product-checkbox {
    transform: scale(1.2);
}

#selectAllCheckbox {
    transform: scale(1.2);
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