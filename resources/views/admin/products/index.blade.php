@extends('admin.layout')

@section('title', 'Products')
@section('page-title', 'Products')
@section('page-description', 'Manage your product catalog')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">All Products ({{ $products->total() }})</h4>
    </div>
    <div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Add New Product
        </a>
    </div>
</div>

@if($products->count() > 0)
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Variations</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        @if($product->images->first())
                                            <img src="{{ Storage::url($product->images->first()->path) }}" 
                                                 alt="{{ $product->name }}" 
                                                 style="width: 50px; height: 50px; object-fit: cover;" 
                                                 class="rounded">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">{{ Str::limit($product->name, 40) }}</h6>
                                        <small class="text-muted">{{ $product->slug }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>{{ $product->brand->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $product->variations->count() }}</span>
                            </td>
                            <td>
                                <div>
                                    <strong>₹{{ number_format($product->price, 2) }}</strong>
                                    @if($product->mrp && $product->mrp > $product->price)
                                        <br><small class="text-muted text-decoration-line-through">₹{{ number_format($product->mrp, 2) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $product->active ? 'success' : 'secondary' }}">
                                    {{ $product->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $product->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.products.show', $product) }}" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteProduct({{ $product->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
        </div>
        <div>
            {{ $products->links('custom.pagination') }}
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-box-seam fs-1 text-muted d-block mb-3"></i>
            <h5>No products found</h5>
            <p class="text-muted">Start building your product catalog by adding your first product.</p>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                Add Your First Product
            </a>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/products/${productId}`;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
