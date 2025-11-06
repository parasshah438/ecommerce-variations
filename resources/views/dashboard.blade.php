@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Content -->

  
    <div class="content-wrapper">
        <!-- User Stats -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="bi bi-bag-check"></i>
                    </div>
                    <div class="stat-value">{{ $stats['total_orders'] }}</div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                    <div class="stat-value">₹{{ number_format($stats['total_spent'], 0) }}</div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="bi bi-cart3"></i>
                    </div>
                    <div class="stat-value">{{ $stats['cart_items'] }}</div>
                    <div class="stat-label">Cart Items</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="bi bi-heart"></i>
                    </div>
                    <div class="stat-value">{{ $stats['wishlist_items'] }}</div>
                    <div class="stat-label">Wishlist Items</div>
                </div>
            </div>
        </div>

        <!-- Additional Stats Row -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-value">{{ $stats['pending_orders'] }}</div>
                    <div class="stat-label">Pending Orders</div>
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <div class="stat-value">{{ $stats['orders_this_month'] }}</div>
                    <div class="stat-label">Orders This Month</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="demo-card">
                    <h3>Quick Actions</h3>
                    <div class="d-flex flex-wrap gap-3 mt-3">
                        <a href="{{ route('orders.index') }}" class="btn btn-primary">
                            <i class="bi bi-bag-check me-2"></i>View All Orders
                        </a>
                        <a href="{{ route('cart.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-cart3 me-2"></i>View Cart ({{ $stats['cart_items'] }})
                        </a>
                        <a href="{{ route('wishlist.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-heart me-2"></i>View Wishlist ({{ $stats['wishlist_items'] }})
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-shop me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        @if($recentOrders->count() > 0)
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="demo-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Recent Orders</h3>
                        <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td><strong>#{{ $order->id }}</strong></td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>{{ $order->items->count() }} items</td>
                                    <td>
                                        <span class="badge 
                                            @if($order->status === 'delivered') bg-success
                                            @elseif($order->status === 'cancelled') bg-danger
                                            @elseif($order->status === 'shipped') bg-info
                                            @elseif($order->status === 'processing') bg-warning
                                            @else bg-secondary
                                            @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>₹{{ number_format($order->total, 2) }}</td>
                                    <td>
                                        <a href="{{ route('order.details', $order) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('order.track', $order) }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-truck"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Wishlist & Recommended Products -->
        <div class="row g-4 mb-4">
            @if($recentWishlist->count() > 0)
            <div class="col-lg-6">
                <div class="demo-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Recent Wishlist</h3>
                        <a href="{{ route('wishlist.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="row g-3">
                        @foreach($recentWishlist->take(4) as $item)
                        <div class="col-6">
                            <div class="card h-100">
                                <div style="height: 120px; overflow: hidden;">
                                    @if($item->product->images->count() > 0)
                                        <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                             class="card-img-top" 
                                             style="height: 100%; object-fit: cover;"
                                             alt="{{ $item->product->name }}">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                            <i class="bi bi-image text-muted fs-4"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body p-2">
                                    <h6 class="card-title small mb-1">{{ Str::limit($item->product->name, 30) }}</h6>
                                    <p class="card-text small text-muted mb-0">₹{{ number_format($item->product->price, 2) }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @if($recommendedProducts->count() > 0)
            <div class="col-lg-{{ $recentWishlist->count() > 0 ? '6' : '12' }}">
                <div class="demo-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Recommended for You</h3>
                        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="row g-3">
                        @foreach($recommendedProducts->take(4) as $product)
                        <div class="col-6">
                            <div class="card h-100">
                                <div style="height: 120px; overflow: hidden;">
                                    @if($product->images->count() > 0)
                                        <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                             class="card-img-top" 
                                             style="height: 100%; object-fit: cover;"
                                             alt="{{ $product->name }}">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                            <i class="bi bi-image text-muted fs-4"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body p-2">
                                    <h6 class="card-title small mb-1">{{ Str::limit($product->name, 30) }}</h6>
                                    <p class="card-text small text-muted mb-0">₹{{ number_format($product->price, 2) }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Welcome Message for First Time Users -->
        @if($stats['total_orders'] == 0)
        <div class="demo-card">
            <h3>Welcome to Your Dashboard, {{ Auth::user()->name }}!</h3>
            <p>This is your personal shopping dashboard where you can track orders, manage your wishlist, and discover new products.</p>
            <p>Get started by browsing our products and adding items to your cart!</p>
            <div class="mt-3">
                <a href="{{ route('products.index') }}" class="btn btn-primary me-2">
                    <i class="bi bi-shop me-2"></i>Start Shopping
                </a>
                <a href="{{ route('wishlist.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-heart me-2"></i>Create Wishlist
                </a>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .stat-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }
    
    .demo-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
    }
    
    .table {
        --bs-table-bg: var(--card-bg);
        --bs-table-color: var(--text-primary);
        color: var(--text-primary);
    }
    
    .table th {
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    
    .table td {
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    
    .card {
        background: var(--card-bg);
        border-color: var(--border-color);
    }
    
    .card-body {
        color: var(--text-primary);
    }
    
    .card-title {
        color: var(--text-primary);
    }
    
    .text-muted {
        color: var(--text-secondary) !important;
    }
    
    .bg-light {
        background-color: var(--sidebar-hover) !important;
    }
</style>
@endpush