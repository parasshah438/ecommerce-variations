@extends('admin.layout')

@section('title', $product->name)
@section('page-title', $product->name)
@section('page-description', 'Product details and variations')

@section('content')
<div class="row">
    <!-- Product Information -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Product Information
                </h5>
                <div>
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i>
                        Edit
                    </a>
                    <a href="/products/{{ $product->slug }}" target="_blank" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-eye me-1"></i>
                        View Frontend
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Name:</td>
                                <td>{{ $product->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Slug:</td>
                                <td><code>{{ $product->slug }}</code></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Category:</td>
                                <td>{{ $product->category->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Brand:</td>
                                <td>{{ $product->brand->name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Base Price:</td>
                                <td>₹{{ number_format($product->price, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">MRP:</td>
                                <td>₹{{ number_format($product->mrp, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td>
                                    <span class="badge bg-{{ $product->active ? 'success' : 'secondary' }}">
                                        {{ $product->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Created:</td>
                                <td>{{ $product->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6>Description:</h6>
                    <p class="text-muted">{{ $product->description }}</p>
                </div>
            </div>
        </div>
        
        <!-- Product Variations -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-grid me-2"></i>
                    Product Variations ({{ $product->variations->count() }})
                </h5>
            </div>
            <div class="card-body">
                @if($product->variations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Attributes</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Min Qty</th>
                                    <th>Images</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->variations as $variation)
                                <tr>
                                    <td>
                                        @foreach($variation->attributeValues as $attributeValue)
                                            <span class="badge bg-light text-dark me-1">
                                                {{ $attributeValue->attribute->name ?? 'N/A' }}: {{ $attributeValue->value }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td><code>{{ $variation->sku }}</code></td>
                                    <td>₹{{ number_format($variation->price, 2) }}</td>
                                    <td>
                                        @if($variation->stock)
                                            <span class="badge bg-{{ $variation->stock->in_stock ? 'success' : 'danger' }}">
                                                {{ $variation->stock->quantity }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $variation->min_qty }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $variation->images->count() }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-sm" onclick="editVariation({{ $variation->id }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-grid fs-1 d-block mb-3"></i>
                        <h6>No variations found</h6>
                        <p>This product doesn't have any variations yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Product Images -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-images me-2"></i>
                    Product Images
                </h5>
            </div>
            <div class="card-body">
                @php
                    $allImages = collect();
                    
                    // Add regular product images
                    foreach($product->images as $image) {
                        $allImages->push([
                            'image' => $image,
                            'type' => 'product',
                            'label' => 'Product Image'
                        ]);
                    }
                    
                    // Add variation images
                    foreach($product->variationImages as $varImage) {
                        $allImages->push([
                            'image' => $varImage,
                            'type' => 'variation',
                            'label' => 'Variation Image'
                        ]);
                    }
                @endphp
                
                @if($allImages->count() > 0)
                    <div class="row g-2">
                        @foreach($allImages as $item)
                        <div class="col-6">
                            <div class="position-relative">
                                <img src="{{ $item['image']->getThumbnailUrl(300) }}" 
                                     alt="{{ $item['image']->alt }}" 
                                     class="img-fluid rounded"
                                     style="width: 100%; height: 120px; object-fit: cover;"
                                     loading="lazy"
                                     onerror="this.src='{{ asset('images/product-placeholder.jpg') }}'">
                                <div class="position-absolute top-0 end-0 p-1">
                                    <button class="btn btn-danger btn-sm" onclick="deleteImage({{ $item['image']->id }}, '{{ $item['type'] }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                @if($item['type'] === 'variation')
                                    <div class="position-absolute bottom-0 start-0 p-1">
                                        <span class="badge bg-info">V</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-image fs-1 d-block mb-3"></i>
                        <h6>No images</h6>
                        <p>Add some product images or variation images</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart me-2"></i>
                    Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h4 class="mb-1">{{ $product->variations->count() }}</h4>
                            <small class="text-muted">Variations</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h4 class="mb-1">{{ $product->variations->sum(function($v) { return $v->stock->quantity ?? 0; }) }}</h4>
                            <small class="text-muted">Total Stock</small>
                        </div>
                    </div>
                </div>
                <div class="row text-center mt-3">
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h4 class="mb-1">{{ $product->images->count() + $product->variationImages->count() }}</h4>
                            <small class="text-muted">Total Images</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h4 class="mb-1">₹{{ number_format($product->variations->min('price') ?? $product->price, 0) }}</h4>
                            <small class="text-muted">Min Price</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>
                        Edit Product
                    </a>
                    <a href="/products/{{ $product->slug }}" target="_blank" class="btn btn-outline-success">
                        <i class="bi bi-eye me-1"></i>
                        View on Frontend
                    </a>
                    <button class="btn btn-outline-warning" onclick="duplicateProduct()">
                        <i class="bi bi-files me-1"></i>
                        Duplicate Product
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteProduct()">
                        <i class="bi bi-trash me-1"></i>
                        Delete Product
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editVariation(variationId) {
    alert('Edit variation functionality would open a modal or redirect to edit page');
}

function deleteImage(imageId, imageType) {
    const imageTypeLabel = imageType === 'variation' ? 'variation image' : 'product image';
    if (confirm(`Are you sure you want to delete this ${imageTypeLabel}?`)) {
        // Create form for image deletion
        const form = document.createElement('form');
        form.method = 'POST';
        
        if (imageType === 'variation') {
            form.action = `/admin/products/variation-images/${imageId}`;
        } else {
            form.action = `/admin/products/images/${imageId}`;
        }
        
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

function duplicateProduct() {
    if (confirm('Are you sure you want to duplicate this product?')) {
        // Create a form and submit to a new route for duplication
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.products.duplicate", $product) }}';

        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';

        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteProduct() {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.products.destroy", $product) }}';
        
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
