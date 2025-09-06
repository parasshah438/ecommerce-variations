@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Overview of your e-commerce store')

@section('content')
<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-primary mb-2">
                    <i class="bi bi-box-seam fs-1"></i>
                </div>
                <h3 class="mb-1">{{ $stats['total_products'] }}</h3>
                <p class="text-muted mb-0">Total Products</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-success mb-2">
                    <i class="bi bi-grid fs-1"></i>
                </div>
                <h3 class="mb-1">{{ $stats['total_variations'] }}</h3>
                <p class="text-muted mb-0">Product Variations</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-warning mb-2">
                    <i class="bi bi-cart3 fs-1"></i>
                </div>
                <h3 class="mb-1">{{ $stats['total_orders'] }}</h3>
                <p class="text-muted mb-0">Total Orders</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-info mb-2">
                    <i class="bi bi-people fs-1"></i>
                </div>
                <h3 class="mb-1">{{ $stats['total_customers'] }}</h3>
                <p class="text-muted mb-0">Total Customers</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add New Product
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-box-seam me-2"></i>
                            Manage Products
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-outline-success w-100">
                            <i class="bi bi-download me-2"></i>
                            Export Data
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-outline-info w-100">
                            <i class="bi bi-gear me-2"></i>
                            Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Products
                </h5>
            </div>
            <div class="card-body">
                @php
                    $recentProducts = \App\Models\Product::with(['category', 'brand'])
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @if($recentProducts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProducts as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                @if($product->images->first())
                                                    <img src="{{ Storage::url($product->images->first()->path) }}" 
                                                         alt="{{ $product->name }}" 
                                                         style="width: 40px; height: 40px; object-fit: cover;" 
                                                         class="rounded">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="bi bi-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0">{{ Str::limit($product->name, 30) }}</h6>
                                                <small class="text-muted">{{ $product->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                    <td>{{ $product->brand->name ?? 'N/A' }}</td>
                                    <td>₹{{ number_format($product->price, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $product->active ? 'success' : 'secondary' }}">
                                            {{ $product->active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-box-seam fs-1 d-block mb-3"></i>
                        <h6>No products found</h6>
                        <p>Start by adding your first product</p>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            Add Product
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart me-2"></i>
                    System Info
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Laravel Version</span>
                        <span class="text-muted">{{ app()->version() }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>PHP Version</span>
                        <span class="text-muted">{{ PHP_VERSION }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Environment</span>
                        <span class="badge bg-{{ app()->environment() === 'production' ? 'success' : 'warning' }}">
                            {{ ucfirst(app()->environment()) }}
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Debug Mode</span>
                        <span class="badge bg-{{ config('app.debug') ? 'danger' : 'success' }}">
                            {{ config('app.debug') ? 'On' : 'Off' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
