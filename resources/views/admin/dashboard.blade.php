@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Commerce Dashboard')
@section('page-description', 'Revenue, fulfillment, catalog, customers, marketing and system controls in one place')

@php
    $statusNames = \App\Models\Order::getStatuses();
    $statusIcons = [
        'pending' => 'fa-clock',
        'confirmed' => 'fa-check',
        'processing' => 'fa-arrows-rotate',
        'shipped' => 'fa-truck-fast',
        'delivered' => 'fa-circle-check',
        'cancelled' => 'fa-circle-xmark',
        'returned' => 'fa-rotate-left',
        'refunded' => 'fa-money-bill-transfer',
    ];
    $topCategoryMax = max((float) ($topCategories->max('revenue') ?? 0), 1);
@endphp

@section('page-actions')
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-receipt"></i> Orders
    </a>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Product
    </a>
@endsection

@push('styles')
<style>
    .commerce-dashboard {
        --dash-blue: #2563eb;
        --dash-green: #059669;
        --dash-orange: #ea580c;
        --dash-purple: #7c3aed;
        --dash-red: #dc2626;
        --dash-teal: #0891b2;
        --dash-ink: #0f172a;
        --dash-muted: #64748b;
        --dash-soft: #f8fafc;
    }

    [data-bs-theme="dark"] .commerce-dashboard {
        --dash-ink: #e2e8f0;
        --dash-muted: #94a3b8;
        --dash-soft: #172033;
    }

    .commerce-hero {
        background: linear-gradient(135deg, #0f172a 0%, #164e63 52%, #166534 100%);
        border-radius: 8px;
        color: #fff;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
        position: relative;
        overflow: hidden;
    }

    .commerce-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px);
        background-size: 32px 32px;
        opacity: .35;
    }

    .commerce-hero > * {
        position: relative;
        z-index: 1;
    }

    .hero-eyebrow {
        color: rgba(255,255,255,.72);
        font-size: .78rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        font-weight: 700;
    }

    .hero-title {
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1.2;
        margin: .35rem 0 .25rem;
    }

    .hero-subtitle {
        color: rgba(255,255,255,.78);
        margin: 0;
        max-width: 700px;
    }

    .hero-metric {
        border: 1px solid rgba(255,255,255,.18);
        background: rgba(255,255,255,.1);
        border-radius: 8px;
        padding: .85rem;
        min-height: 92px;
    }

    .hero-metric span {
        display: block;
        color: rgba(255,255,255,.68);
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .hero-metric strong {
        display: block;
        font-size: 1.25rem;
        line-height: 1.25;
        margin-top: .35rem;
    }

    .dash-card {
        background: var(--card-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
        height: 100%;
    }

    .dash-card-header {
        padding: 1rem 1.1rem;
        border-bottom: 1px solid var(--bs-border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .dash-card-title {
        margin: 0;
        color: var(--dash-ink);
        font-size: .98rem;
        font-weight: 800;
    }

    .dash-card-body {
        padding: 1rem 1.1rem;
    }

    .kpi-card {
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid var(--bs-border-color);
        background: var(--card-bg);
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--accent, var(--dash-blue));
    }

    .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }

    .kpi-icon,
    .section-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: var(--accent, var(--dash-blue));
        background: color-mix(in srgb, var(--accent, var(--dash-blue)) 12%, transparent);
        flex-shrink: 0;
    }

    .kpi-label {
        color: var(--dash-muted);
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .kpi-value {
        color: var(--dash-ink);
        font-size: 1.55rem;
        font-weight: 850;
        line-height: 1.2;
        margin-top: .55rem;
    }

    .kpi-foot {
        color: var(--dash-muted);
        display: flex;
        align-items: center;
        gap: .4rem;
        font-size: .82rem;
        font-weight: 600;
        margin-top: .55rem;
    }

    .trend-up { color: var(--dash-green); }
    .trend-down { color: var(--dash-red); }
    .trend-neutral { color: var(--dash-muted); }

    .accent-blue { --accent: var(--dash-blue); }
    .accent-green { --accent: var(--dash-green); }
    .accent-orange { --accent: var(--dash-orange); }
    .accent-purple { --accent: var(--dash-purple); }
    .accent-red { --accent: var(--dash-red); }
    .accent-teal { --accent: var(--dash-teal); }

    .manage-card {
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        background: var(--card-bg);
        height: 100%;
        overflow: hidden;
    }

    .manage-card-head {
        padding: .95rem;
        display: flex;
        align-items: center;
        gap: .75rem;
        border-bottom: 1px solid var(--bs-border-color);
    }

    .manage-title {
        color: var(--dash-ink);
        font-size: .95rem;
        font-weight: 800;
        margin: 0;
    }

    .manage-subtitle {
        color: var(--dash-muted);
        font-size: .78rem;
        margin: .15rem 0 0;
    }

    .manage-count {
        margin-left: auto;
        color: var(--dash-ink);
        font-weight: 850;
        font-size: 1.1rem;
    }

    .manage-links {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1px;
        background: var(--bs-border-color);
    }

    .manage-link {
        min-height: 56px;
        padding: .7rem .8rem;
        color: var(--bs-body-color);
        background: var(--card-bg);
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        font-size: .84rem;
        font-weight: 700;
    }

    .manage-link:hover {
        color: var(--accent);
        background: var(--dash-soft);
    }

    .manage-link-label {
        display: flex;
        align-items: center;
        gap: .5rem;
        min-width: 0;
    }

    .manage-link-label span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .metric-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .metric-list li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .75rem 0;
        border-bottom: 1px solid var(--bs-border-color);
    }

    .metric-list li:last-child {
        border-bottom: 0;
    }

    .metric-label {
        display: flex;
        align-items: center;
        gap: .65rem;
        min-width: 0;
        color: var(--bs-body-color);
        font-weight: 700;
        font-size: .86rem;
    }

    .metric-label span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .metric-value {
        color: var(--dash-ink);
        font-weight: 850;
        white-space: nowrap;
    }

    .chart-container {
        height: 310px;
        position: relative;
    }

    .chart-container-sm {
        height: 220px;
        position: relative;
    }

    .order-status-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .28rem .62rem;
        font-size: .74rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .order-status-badge.pending { background: #fef3c7; color: #92400e; }
    .order-status-badge.confirmed { background: #dbeafe; color: #1e40af; }
    .order-status-badge.processing { background: #ede9fe; color: #5b21b6; }
    .order-status-badge.shipped { background: #cffafe; color: #155e75; }
    .order-status-badge.delivered { background: #d1fae5; color: #065f46; }
    .order-status-badge.cancelled { background: #fee2e2; color: #991b1b; }
    .order-status-badge.returned { background: #ffedd5; color: #9a3412; }
    .order-status-badge.refunded { background: #f1f5f9; color: #334155; }

    .table-modern {
        margin: 0;
    }

    .table-modern th {
        color: var(--dash-muted);
        font-size: .72rem;
        font-weight: 850;
        letter-spacing: .06em;
        text-transform: uppercase;
        border-bottom-color: var(--bs-border-color);
        white-space: nowrap;
    }

    .table-modern td {
        vertical-align: middle;
        border-bottom-color: var(--bs-border-color);
        font-size: .86rem;
    }

    .rank-badge {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: var(--dash-soft);
        color: var(--dash-muted);
        font-size: .76rem;
        font-weight: 850;
    }

    .rank-badge.gold { background: #fef3c7; color: #92400e; }
    .rank-badge.silver { background: #e2e8f0; color: #334155; }
    .rank-badge.bronze { background: #ffedd5; color: #9a3412; }

    .stock-row,
    .product-row,
    .category-row {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .75rem 0;
        border-bottom: 1px solid var(--bs-border-color);
    }

    .stock-row:last-child,
    .product-row:last-child,
    .category-row:last-child {
        border-bottom: 0;
    }

    .row-main {
        flex: 1;
        min-width: 0;
    }

    .row-title {
        color: var(--dash-ink);
        font-size: .88rem;
        font-weight: 800;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .row-subtitle {
        color: var(--dash-muted);
        font-size: .76rem;
        margin-top: .1rem;
    }

    .stock-bar,
    .category-bar {
        height: 7px;
        border-radius: 999px;
        background: var(--bs-secondary-bg);
        overflow: hidden;
    }

    .stock-bar-fill,
    .category-bar-fill {
        height: 100%;
        border-radius: 999px;
        background: var(--accent, var(--dash-green));
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .quick-action {
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        padding: .8rem;
        text-decoration: none;
        color: var(--bs-body-color);
        background: var(--card-bg);
        min-height: 78px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .quick-action:hover {
        border-color: var(--accent, var(--dash-blue));
        color: var(--accent, var(--dash-blue));
        background: var(--dash-soft);
    }

    .empty-state {
        color: var(--dash-muted);
        padding: 2rem 1rem;
        text-align: center;
    }

    @media (max-width: 768px) {
        .commerce-hero {
            padding: 1rem;
        }

        .hero-title {
            font-size: 1.35rem;
        }

        .manage-links,
        .quick-actions {
            grid-template-columns: 1fr;
        }

        .chart-container,
        .chart-container-sm {
            height: 240px;
        }
    }
</style>
@endpush

@section('content')
<div class="commerce-dashboard">
    <section class="commerce-hero">
        <div class="row g-4 align-items-end">
            <div class="col-xl-6">
                <div class="hero-eyebrow">Ecommerce control center</div>
                <h2 class="hero-title">Good {{ now()->format('A') === 'AM' ? 'Morning' : 'Afternoon' }}, {{ auth()->user()->name ?? 'Admin' }}</h2>
                <p class="hero-subtitle">{{ now()->format('l, F j, Y') }}. Manage store performance, inventory, orders, customers, campaigns and system health from this dashboard.</p>
            </div>
            <div class="col-xl-6">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="hero-metric">
                            <span>Today Revenue</span>
                            <strong>&#8377;{{ number_format($todayStats['revenue'], 0) }}</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="hero-metric">
                            <span>Today Orders</span>
                            <strong>{{ number_format($todayStats['orders']) }}</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="hero-metric">
                            <span>Backlog</span>
                            <strong>{{ number_format($fulfillmentBacklog) }}</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="hero-metric">
                            <span>Low Stock</span>
                            <strong>{{ number_format($lowStockItems->count() + $outOfStockItems) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card accent-blue">
                <div class="kpi-top">
                    <div>
                        <div class="kpi-label">Total Revenue</div>
                        <div class="kpi-value">&#8377;{{ number_format($revenueData->total_revenue, 0) }}</div>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
                </div>
                <div class="kpi-foot {{ $revenueGrowth > 0 ? 'trend-up' : ($revenueGrowth < 0 ? 'trend-down' : 'trend-neutral') }}">
                    <i class="fas fa-{{ $revenueGrowth >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ abs($revenueGrowth) }}% vs last month
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card accent-green">
                <div class="kpi-top">
                    <div>
                        <div class="kpi-label">Orders</div>
                        <div class="kpi-value">{{ number_format($totalOrders) }}</div>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-cart-shopping"></i></div>
                </div>
                <div class="kpi-foot {{ $orderGrowth > 0 ? 'trend-up' : ($orderGrowth < 0 ? 'trend-down' : 'trend-neutral') }}">
                    <i class="fas fa-{{ $orderGrowth >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ abs($orderGrowth) }}% vs last month, {{ $monthOrders }} this month
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card accent-purple">
                <div class="kpi-top">
                    <div>
                        <div class="kpi-label">Customers</div>
                        <div class="kpi-value">{{ number_format($totalCustomers) }}</div>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-users"></i></div>
                </div>
                <div class="kpi-foot {{ $customerGrowth > 0 ? 'trend-up' : ($customerGrowth < 0 ? 'trend-down' : 'trend-neutral') }}">
                    <i class="fas fa-{{ $customerGrowth >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ abs($customerGrowth) }}% vs last month, {{ $newCustomersMonth }} new
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card accent-orange">
                <div class="kpi-top">
                    <div>
                        <div class="kpi-label">Average Order Value</div>
                        <div class="kpi-value">&#8377;{{ number_format($avgOrderValue, 0) }}</div>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-calculator"></i></div>
                </div>
                <div class="kpi-foot trend-neutral">
                    <i class="fas fa-box"></i>
                    {{ $activeProducts }} active products, {{ $activeCategories }} active categories
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach($managementSections as $section)
            <div class="col-xxl-4 col-lg-6">
                <div class="manage-card accent-{{ $section['accent'] }}">
                    <div class="manage-card-head">
                        <div class="section-icon"><i class="fas {{ $section['icon'] }}"></i></div>
                        <div>
                            <h3 class="manage-title">{{ $section['title'] }}</h3>
                            <p class="manage-subtitle">{{ $section['subtitle'] }}</p>
                        </div>
                        @if(!is_null($section['count']))
                            <div class="manage-count">{{ number_format($section['count']) }}</div>
                        @endif
                    </div>
                    <div class="manage-links">
                        @foreach($section['items'] as $item)
                            <a href="{{ route($item['route']) }}" class="manage-link">
                                <span class="manage-link-label">
                                    <i class="fas {{ $item['icon'] }}"></i>
                                    <span>{{ $item['label'] }}</span>
                                </span>
                                @if(!is_null($item['count']))
                                    <span>{{ number_format($item['count']) }}</span>
                                @else
                                    <i class="fas fa-arrow-right"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-chart-area me-2 text-primary"></i>Revenue Trend</h3>
                    <span class="badge text-bg-light">Last 12 months</span>
                </div>
                <div class="dash-card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-chart-pie me-2 text-success"></i>Order Pipeline</h3>
                    <a href="{{ route('admin.orders.index') }}" class="small fw-bold text-decoration-none">View orders</a>
                </div>
                <div class="dash-card-body">
                    <div class="chart-container-sm">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                    @if(count($statusLabels))
                        <ul class="metric-list mt-2">
                            @foreach($statusLabels as $i => $label)
                                <li>
                                    <span class="metric-label">
                                        <span style="width:10px;height:10px;border-radius:50%;background:{{ $statusColors[$i] ?? '#94a3b8' }};display:inline-block;"></span>
                                        <span>{{ $label }}</span>
                                    </span>
                                    <span class="metric-value">{{ number_format($statusCounts[$i] ?? 0) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="empty-state">No order status data yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-calendar-week me-2 text-info"></i>7 Day Sales Pulse</h3>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success-subtle text-success">Revenue</span>
                        <span class="badge bg-primary-subtle text-primary">Orders</span>
                    </div>
                </div>
                <div class="dash-card-body">
                    <div class="chart-container-sm">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-gauge-high me-2 text-warning"></i>Today Snapshot</h3>
                </div>
                <div class="dash-card-body">
                    <ul class="metric-list">
                        <li>
                            <span class="metric-label"><i class="fas fa-wallet text-primary"></i><span>Revenue</span></span>
                            <span class="metric-value">&#8377;{{ number_format($todayStats['revenue'], 0) }}</span>
                        </li>
                        <li>
                            <span class="metric-label"><i class="fas fa-cart-shopping text-success"></i><span>Orders</span></span>
                            <span class="metric-value">{{ number_format($todayStats['orders']) }}</span>
                        </li>
                        <li>
                            <span class="metric-label"><i class="fas fa-user-plus text-info"></i><span>New Signups</span></span>
                            <span class="metric-value">{{ number_format($todayStats['signups']) }}</span>
                        </li>
                        <li>
                            <span class="metric-label"><i class="fas fa-star text-warning"></i><span>Reviews</span></span>
                            <span class="metric-value">{{ number_format($todayStats['reviews']) }}</span>
                        </li>
                        <li>
                            <span class="metric-label"><i class="fas fa-truck-fast text-danger"></i><span>Shipping Queue</span></span>
                            <span class="metric-value">{{ number_format($shippingQueue) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-clock me-2 text-primary"></i>Recent Orders</h3>
                    <a href="{{ route('admin.orders.index') }}" class="small fw-bold text-decoration-none">Manage all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th class="ps-3">Order</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th class="pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-decoration-none">#{{ $order->id }}</a>
                                        <div class="small text-muted">{{ $order->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ optional($order->user)->name ?? 'Guest' }}</div>
                                        <div class="small text-muted">{{ optional($order->user)->email ?? 'No email' }}</div>
                                    </td>
                                    <td>{{ number_format($order->items->sum('quantity')) }}</td>
                                    <td class="fw-bold">&#8377;{{ number_format($order->total, 2) }}</td>
                                    <td class="pe-3">
                                        <span class="order-status-badge {{ $order->status }}">
                                            <i class="fas {{ $statusIcons[$order->status] ?? 'fa-circle' }}"></i>
                                            {{ $statusNames[$order->status] ?? ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox fs-3 d-block mb-2"></i>
                                            No orders yet.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-trophy me-2 text-warning"></i>Top Products</h3>
                    <a href="{{ route('admin.products.index') }}" class="small fw-bold text-decoration-none">Catalog</a>
                </div>
                <div class="dash-card-body">
                    @forelse($topProducts as $index => $product)
                        <div class="product-row">
                            <span class="rank-badge {{ $index === 0 ? 'gold' : ($index === 1 ? 'silver' : ($index === 2 ? 'bronze' : '')) }}">{{ $index + 1 }}</span>
                            <div class="row-main">
                                <div class="row-title">{{ $product->name }}</div>
                                <div class="row-subtitle">&#8377;{{ number_format($product->price, 2) }} selling price, {{ number_format($product->total_sold) }} sold</div>
                            </div>
                            <div class="fw-bold text-success">&#8377;{{ number_format($product->total_revenue, 0) }}</div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-box-open fs-3 d-block mb-2"></i>
                            No sales data yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-4">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-triangle-exclamation me-2 text-danger"></i>Risk Alerts</h3>
                </div>
                <div class="dash-card-body">
                    <ul class="metric-list">
                        @foreach($riskAlerts as $alert)
                            <li>
                                <a href="{{ route($alert['route']) }}" class="metric-label text-decoration-none">
                                    <i class="fas fa-circle text-{{ $alert['severity'] }}"></i>
                                    <span>{{ $alert['label'] }}</span>
                                </a>
                                <span class="metric-value">{{ number_format($alert['value']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-credit-card me-2 text-primary"></i>Payments</h3>
                    <a href="{{ route('admin.payments.index') }}" class="small fw-bold text-decoration-none">Open</a>
                </div>
                <div class="dash-card-body">
                    <ul class="metric-list">
                        <li>
                            <span class="metric-label"><i class="fas fa-circle-check text-success"></i><span>Paid</span></span>
                            <span class="metric-value">{{ number_format($paymentBreakdown[\App\Models\Payment::PAYMENT_STATUS_PAID] ?? 0) }}</span>
                        </li>
                        <li>
                            <span class="metric-label"><i class="fas fa-clock text-warning"></i><span>Pending</span></span>
                            <span class="metric-value">{{ number_format($paymentBreakdown[\App\Models\Payment::PAYMENT_STATUS_PENDING] ?? 0) }}</span>
                        </li>
                        <li>
                            <span class="metric-label"><i class="fas fa-circle-xmark text-danger"></i><span>Failed</span></span>
                            <span class="metric-value">{{ number_format($paymentBreakdown[\App\Models\Payment::PAYMENT_STATUS_FAILED] ?? 0) }}</span>
                        </li>
                        <li>
                            <span class="metric-label"><i class="fas fa-money-bill-transfer text-secondary"></i><span>Refunded</span></span>
                            <span class="metric-value">{{ number_format($paymentBreakdown[\App\Models\Payment::PAYMENT_STATUS_REFUNDED] ?? 0) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-basket-shopping me-2 text-success"></i>Customer Funnel</h3>
                </div>
                <div class="dash-card-body">
                    <ul class="metric-list">
                        <li>
                            <span class="metric-label"><i class="fas fa-users text-primary"></i><span>Total Customers</span></span>
                            <span class="metric-value">{{ number_format($totalCustomers) }}</span>
                        </li>
                        <li>
                            <span class="metric-label"><i class="fas fa-user-plus text-info"></i><span>New This Month</span></span>
                            <span class="metric-value">{{ number_format($newCustomersMonth) }}</span>
                        </li>
                        <li>
                            <span class="metric-label"><i class="fas fa-cart-shopping text-success"></i><span>Active Carts</span></span>
                            <span class="metric-value">{{ number_format($activeCartsCount) }}</span>
                        </li>
                        <li>
                            <span class="metric-label"><i class="fas fa-hourglass-half text-warning"></i><span>Abandoned Carts</span></span>
                            <span class="metric-value">{{ number_format($abandonedCartsCount) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-5">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-warehouse me-2 text-danger"></i>Low Stock Watchlist</h3>
                    <a href="{{ route('admin.stock.dashboard') }}" class="small fw-bold text-decoration-none">Manage stock</a>
                </div>
                <div class="dash-card-body">
                    @forelse($lowStockItems as $stock)
                        @php
                            $product = $stock->variation->product ?? null;
                            $qty = (int) $stock->quantity;
                            $stockAccent = $qty <= 3 ? 'var(--dash-red)' : ($qty <= 5 ? 'var(--dash-orange)' : 'var(--dash-green)');
                        @endphp
                        <div class="stock-row" style="--accent: {{ $stockAccent }};">
                            <div class="row-main">
                                <div class="row-title">{{ $product->name ?? 'Unknown Product' }}</div>
                                <div class="row-subtitle">SKU: {{ $stock->variation->sku ?? 'N/A' }}</div>
                                <div class="stock-bar mt-2">
                                    <div class="stock-bar-fill" style="width: {{ min(($qty / 10) * 100, 100) }}%;"></div>
                                </div>
                            </div>
                            <span class="fw-bold" style="color: {{ $stockAccent }};">{{ $qty }} left</span>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-circle-check text-success fs-3 d-block mb-2"></i>
                            Inventory looks healthy.
                        </div>
                    @endforelse
                    @if($outOfStockItems > 0)
                        <a href="{{ route('admin.stock.dashboard') }}" class="btn btn-outline-danger btn-sm w-100 mt-3">
                            <i class="fas fa-triangle-exclamation"></i> {{ $outOfStockItems }} out of stock SKUs
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-chart-simple me-2 text-success"></i>Category Performance</h3>
                    <a href="{{ route('admin.categories.index') }}" class="small fw-bold text-decoration-none">Categories</a>
                </div>
                <div class="dash-card-body">
                    @forelse($topCategories as $category)
                        <div class="category-row">
                            <div class="row-main">
                                <div class="d-flex justify-content-between gap-2">
                                    <div class="row-title">{{ $category->name }}</div>
                                    <div class="fw-bold">&#8377;{{ number_format($category->revenue, 0) }}</div>
                                </div>
                                <div class="row-subtitle">{{ number_format($category->units_sold) }} units sold</div>
                                <div class="category-bar mt-2 accent-green">
                                    <div class="category-bar-fill" style="width: {{ min(((float) $category->revenue / $topCategoryMax) * 100, 100) }}%;"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-folder-open fs-3 d-block mb-2"></i>
                            No category sales yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h3>
                </div>
                <div class="dash-card-body">
                    <div class="quick-actions">
                        <a href="{{ route('admin.products.create') }}" class="quick-action accent-blue">
                            <i class="fas fa-plus"></i>
                            <strong>Add Product</strong>
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="quick-action accent-green">
                            <i class="fas fa-receipt"></i>
                            <strong>Orders</strong>
                        </a>
                        <a href="{{ route('admin.sales.create') }}" class="quick-action accent-orange">
                            <i class="fas fa-tags"></i>
                            <strong>Create Sale</strong>
                        </a>
                        <a href="{{ route('admin.coupons.index') }}" class="quick-action accent-purple">
                            <i class="fas fa-ticket"></i>
                            <strong>Coupons</strong>
                        </a>
                        <a href="{{ route('admin.reviews.index') }}" class="quick-action accent-teal">
                            <i class="fas fa-comments"></i>
                            <strong>Reviews</strong>
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="quick-action accent-red">
                            <i class="fas fa-gear"></i>
                            <strong>Settings</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartTextColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-body-color') || '#334155';
    const gridColor = 'rgba(148, 163, 184, 0.18)';
    const money = value => '\u20B9' + Number(value || 0).toLocaleString('en-IN');

    const revenueChart = document.getElementById('revenueChart');
    if (revenueChart) {
        new Chart(revenueChart, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Revenue',
                    data: @json($chartRevenue),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.12)',
                    fill: true,
                    tension: 0.38,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#2563eb',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#fff',
                        bodyColor: '#e2e8f0',
                        callbacks: { label: context => 'Revenue: ' + money(context.raw) }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: chartTextColor, callback: value => money(value) }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: chartTextColor, maxRotation: 0, autoSkip: true }
                    }
                }
            }
        });
    }

    const statusChart = document.getElementById('orderStatusChart');
    if (statusChart) {
        const statusData = @json($statusCounts);
        new Chart(statusChart, {
            type: 'doughnut',
            data: {
                labels: @json($statusLabels),
                datasets: [{
                    data: statusData.length ? statusData : [1],
                    backgroundColor: statusData.length ? @json($statusColors) : ['#e2e8f0'],
                    borderWidth: 0,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: statusData.length > 0,
                        backgroundColor: '#0f172a',
                        titleColor: '#fff',
                        bodyColor: '#e2e8f0',
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((sum, item) => sum + item, 0);
                                const percent = total > 0 ? Math.round((context.raw / total) * 100) : 0;
                                return context.label + ': ' + context.raw + ' (' + percent + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    const weeklyChart = document.getElementById('weeklyChart');
    if (weeklyChart) {
        new Chart(weeklyChart, {
            type: 'bar',
            data: {
                labels: @json($weekLabels),
                datasets: [
                    {
                        label: 'Revenue',
                        data: @json($weekRevenue),
                        backgroundColor: 'rgba(5, 150, 105, 0.76)',
                        borderRadius: 5,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Orders',
                        data: @json($weekOrders),
                        backgroundColor: 'rgba(37, 99, 235, 0.72)',
                        borderRadius: 5,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#fff',
                        bodyColor: '#e2e8f0',
                        callbacks: {
                            label: context => context.dataset.label === 'Revenue'
                                ? 'Revenue: ' + money(context.raw)
                                : 'Orders: ' + context.raw
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        grid: { color: gridColor },
                        ticks: { color: chartTextColor, callback: value => money(value) }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { display: false },
                        ticks: { color: chartTextColor, precision: 0 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: chartTextColor }
                    }
                }
            }
        });
    }
});
</script>
@endpush
