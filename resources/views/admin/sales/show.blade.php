@extends('admin.layout')

@section('title', 'View Sale - ' . $sale->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Sale Details</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Sale
                    </a>
                    <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Sales
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">{{ $sale->name }}</h6>
                            <span class="badge badge-{{ $sale->isActive() ? 'success' : 'secondary' }}">
                                {{ $sale->isActive() ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="card-body">
                            @if($sale->banner_image)
                                <div class="mb-4">
                                    <img src="{{ Storage::url($sale->banner_image) }}" 
                                         alt="{{ $sale->name }}" 
                                         class="img-fluid rounded" 
                                         style="max-height: 300px; width: 100%; object-fit: cover;">
                                </div>
                            @endif

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Sale Information</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Description:</strong></td>
                                            <td>{{ $sale->description ?: 'No description' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Slug:</strong></td>
                                            <td><code>{{ $sale->slug }}</code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Type:</strong></td>
                                            <td>
                                                <span class="badge bg-info">{{ $sale->getSaleTypeLabel() }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Discount:</strong></td>
                                            <td>
                                                <span class="text-danger fw-bold">
                                                    {{ $sale->discount_value }}{{ $sale->type === 'percentage' ? '%' : '₹' }}
                                                </span>
                                                @if($sale->max_discount)
                                                    <small class="text-muted d-block">Max discount: ₹{{ $sale->max_discount }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Sale Timeline</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Start Date:</strong></td>
                                            <td>{{ $sale->start_date->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>End Date:</strong></td>
                                            <td>{{ $sale->end_date->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Duration:</strong></td>
                                            <td>{{ $sale->start_date->diffForHumans($sale->end_date, true) }}</td>
                                        </tr>
                                        @if($sale->usage_limit)
                                        <tr>
                                            <td><strong>Usage:</strong></td>
                                            <td>
                                                {{ $sale->usage_count }} / {{ $sale->usage_limit }}
                                                <div class="progress mt-1" style="height: 8px;">
                                                    <div class="progress-bar" 
                                                         style="width: {{ ($sale->usage_count / $sale->usage_limit) * 100 }}%"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            @if($sale->min_order_value)
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Minimum order value: <strong>₹{{ $sale->min_order_value }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Products in Sale -->
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Products in Sale ({{ $sale->products->count() }})</h6>
                        </div>
                        <div class="card-body">
                            @if($sale->products->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Original Price</th>
                                                <th>Sale Price</th>
                                                <th>Discount</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sale->products as $product)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($product->getThumbnailImage())
                                                            <img src="{{ Storage::url($product->getThumbnailImage()->image_path) }}" 
                                                                 alt="{{ $product->name }}" 
                                                                 class="me-3 rounded"
                                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                                        @endif
                                                        <div>
                                                            <div class="fw-bold">{{ $product->name }}</div>
                                                            <small class="text-muted">{{ $product->brand->name ?? 'No Brand' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>₹{{ number_format($product->price, 2) }}</td>
                                                <td class="text-danger fw-bold">
                                                    ₹{{ number_format($sale->calculateSalePrice($product->price), 2) }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        {{ round((($product->price - $sale->calculateSalePrice($product->price)) / $product->price) * 100) }}% OFF
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('products.show', $product->slug) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       target="_blank">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-box text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3 text-muted">No products in this sale</h5>
                                    <p class="text-muted">Add products to this sale to start offering discounts.</p>
                                    <a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-primary">
                                        <i class="bi bi-plus-lg"></i> Add Products
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Sale Statistics -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Sale Statistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="text-muted">Products</div>
                                    <div class="h4 mb-0 text-primary">{{ $sale->products->count() }}</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-muted">Usage Count</div>
                                    <div class="h4 mb-0 text-info">{{ $sale->usage_count }}</div>
                                </div>
                                <div class="col-12">
                                    @if($sale->isActive())
                                        @php
                                            $timeRemaining = $sale->getTimeRemaining();
                                        @endphp
                                        @if($timeRemaining)
                                            <div class="alert alert-success">
                                                <i class="bi bi-clock me-2"></i>
                                                <strong>Time Remaining:</strong><br>
                                                {{ $timeRemaining->format('%d days, %h hours, %i minutes') }}
                                            </div>
                                        @endif
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            Sale is not currently active
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('sales.show', $sale->slug) }}" 
                                   class="btn btn-info" target="_blank">
                                    <i class="bi bi-eye"></i> View Frontend
                                </a>
                                <a href="{{ route('admin.sales.edit', $sale) }}" 
                                   class="btn btn-warning">
                                    <i class="bi bi-pencil"></i> Edit Sale
                                </a>
                                <button class="btn btn-{{ $sale->is_active ? 'secondary' : 'success' }} toggle-status" 
                                        data-sale-id="{{ $sale->id }}">
                                    <i class="bi bi-{{ $sale->is_active ? 'pause-fill' : 'play-fill' }}"></i> 
                                    {{ $sale->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <form action="{{ route('admin.sales.destroy', $sale) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this sale?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="bi bi-trash"></i> Delete Sale
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.toggle-status').click(function() {
        const saleId = $(this).data('sale-id');
        const button = $(this);
        const originalHtml = button.html();
        
        button.prop('disabled', true).html('<i class="bi bi-hourglass"></i> Processing...');
        
        $.ajax({
            url: `/admin/sales/${saleId}/toggle-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                button.prop('disabled', false).html(originalHtml);
                alert('Error updating sale status');
            }
        });
    });
});
</script>
@endpush
@endsection