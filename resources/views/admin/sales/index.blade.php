@extends('admin.layout')

@section('title', 'Sales Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Sales Management</h1>
                <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Create New Sale
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Sales</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Sale Name</th>
                                    <th>Type</th>
                                    <th>Discount</th>
                                    <th>Products Count</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                <tr>
                                    <td>{{ $sale->id }}</td>
                                    <td>
                                        <strong>{{ $sale->name }}</strong>
                                        @if($sale->banner_image)
                                            <br><small class="text-muted">Has Banner</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($sale->type) }}</span>
                                    </td>
                                    <td>
                                        {{ $sale->discount_value }}{{ $sale->type === 'percentage' ? '%' : '₹' }}
                                        @if($sale->max_discount)
                                            <br><small class="text-muted">Max: ₹{{ $sale->max_discount }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $sale->products_count }}</td>
                                    <td>{{ $sale->start_date->format('M d, Y H:i') }}</td>
                                    <td>{{ $sale->end_date->format('M d, Y H:i') }}</td>
                                    <td>
                                        @if($sale->isActive())
                                            <span class="badge badge-success">Active</span>
                                        @elseif($sale->end_date < now())
                                            <span class="badge badge-danger">Expired</span>
                                        @elseif($sale->start_date > now())
                                            <span class="badge badge-warning">Scheduled</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.sales.show', $sale) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.sales.edit', $sale) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-sm btn-{{ $sale->is_active ? 'secondary' : 'success' }} toggle-status" 
                                                    data-sale-id="{{ $sale->id }}" title="Toggle Status">
                                                <i class="bi bi-{{ $sale->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                                            </button>
                                            <form action="{{ route('admin.sales.destroy', $sale) }}" 
                                                  method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this sale?')" 
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No sales found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($sales->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $sales->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $('.toggle-status').click(function() {
        const saleId = $(this).data('sale-id');
        const button = $(this);
        const originalHtml = button.html();
        
        // Disable button during request
        button.prop('disabled', true).html('<i class="bi bi-hourglass"></i>');
        
        $.ajax({
            url: `/admin/sales/${saleId}/toggle-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                    // Reload page to reflect changes
                    location.reload();
                } else {
                    button.prop('disabled', false).html(originalHtml);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to update sale status');
                    } else {
                        alert('Failed to update sale status');
                    }
                }
            },
            error: function(xhr) {
                button.prop('disabled', false).html(originalHtml);
                console.error('Error:', xhr);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Error updating sale status');
                } else {
                    alert('Error updating sale status');
                }
            }
        });
    });
});
</script>
@endpush
@endsection