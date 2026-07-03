<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturnRequest;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Sale;
use App\Models\Slider;
use App\Models\User;
use App\Models\VariationStock;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $todayStart = $now->copy()->startOfDay();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $revenueData = Order::where('payment_status', Order::PAYMENT_PAID)
            ->selectRaw('COALESCE(SUM(total), 0) as total_revenue')
            ->selectRaw('COALESCE(SUM(CASE WHEN created_at >= ? THEN total ELSE 0 END), 0) as month_revenue', [$monthStart])
            ->selectRaw('COALESCE(SUM(CASE WHEN created_at >= ? THEN total ELSE 0 END), 0) as today_revenue', [$todayStart])
            ->selectRaw('COUNT(*) as paid_orders')
            ->first();

        $lastMonthRevenue = Order::where('payment_status', Order::PAYMENT_PAID)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total');

        $revenueGrowth = $this->percentageChange((float) $revenueData->month_revenue, (float) $lastMonthRevenue);

        $totalOrders = Order::count();
        $monthOrders = Order::where('created_at', '>=', $monthStart)->count();
        $todayOrders = Order::where('created_at', '>=', $todayStart)->count();
        $lastMonthOrders = Order::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $orderGrowth = $this->percentageChange($monthOrders, $lastMonthOrders);

        $avgOrderValue = $revenueData->paid_orders > 0
            ? round($revenueData->total_revenue / $revenueData->paid_orders, 2)
            : 0;

        $orderStatuses = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalCustomers = User::count();
        $newCustomersMonth = User::where('created_at', '>=', $monthStart)->count();
        $lastMonthCustomers = User::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $customerGrowth = $this->percentageChange($newCustomersMonth, $lastMonthCustomers);

        $activeProducts = Product::where('active', true)->count();
        $inactiveProducts = Product::where('active', false)->count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $activeCategories = Category::where('is_active', true)->count();
        $totalAttributes = Attribute::count();
        $totalVariations = Product::query()
            ->join('product_variations', 'products.id', '=', 'product_variations.product_id')
            ->count('product_variations.id');

        $lowStockItems = VariationStock::with('variation.product')
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', 10)
            ->orderBy('quantity')
            ->limit(10)
            ->get();

        $outOfStockItems = VariationStock::with('variation.product')
            ->where(function ($query) {
                $query->where('quantity', '<=', 0)->orWhere('in_stock', false);
            })
            ->count();

        $pendingReviewsCount = Review::pending()->count();
        $reportedReviewsCount = Review::where('status', Review::STATUS_REPORTED)->count();

        $pendingReturnsCount = OrderReturnRequest::pending()->count();
        $openReturnsCount = OrderReturnRequest::whereNotIn('status', [
            OrderReturnRequest::STATUS_REJECTED,
            OrderReturnRequest::STATUS_REFUNDED,
        ])->count();

        $pendingPaymentsCount = Payment::pending()->count();
        $failedPaymentsCount = Payment::failed()->where('created_at', '>=', $monthStart)->count();

        $activeCouponsCount = Coupon::where(function ($query) use ($now) {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now->toDateString());
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', $now->toDateString());
            })
            ->count();

        $activeSalesCount = Sale::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->count();

        $activeSlidersCount = Slider::active()->count();
        $activeCartsCount = Cart::has('items')->count();
        $abandonedCartsCount = Cart::has('items')
            ->where('updated_at', '<=', $now->copy()->subDay())
            ->count();

        $fulfillmentBacklog = Order::whereIn('status', [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PROCESSING,
        ])->count();

        $shippingQueue = Order::whereIn('status', [
            Order::STATUS_CONFIRMED,
            Order::STATUS_PROCESSING,
        ])->count();

        $recentOrders = Order::with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $topProducts = Product::select('products.id', 'products.name', 'products.slug', 'products.price', 'products.active')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->selectRaw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as total_revenue')
            ->join('product_variations', 'products.id', '=', 'product_variations.product_id')
            ->join('order_items', 'product_variations.id', '=', 'order_items.product_variation_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->where(function ($query) {
                $query->whereNull('order_items.status')->orWhere('order_items.status', '!=', OrderItem::STATUS_CANCELLED);
            })
            ->groupBy('products.id', 'products.name', 'products.slug', 'products.price', 'products.active')
            ->orderByDesc('total_sold')
            ->limit(8)
            ->get();

        $topCategories = Category::select('categories.id', 'categories.name')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as units_sold')
            ->selectRaw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as revenue')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('product_variations', 'products.id', '=', 'product_variations.product_id')
            ->join('order_items', 'product_variations.id', '=', 'order_items.product_variation_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->where(function ($query) {
                $query->whereNull('order_items.status')->orWhere('order_items.status', '!=', OrderItem::STATUS_CANCELLED);
            })
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $monthlyRevenue = Order::where('payment_status', Order::PAYMENT_PAID)
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
            ->selectRaw('COALESCE(SUM(total), 0) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        $chartLabels = [];
        $chartRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i)->format('Y-m');
            $chartLabels[] = $now->copy()->subMonths($i)->format('M Y');
            $chartRevenue[] = (float) ($monthlyRevenue[$month] ?? 0);
        }

        $statusLabels = [];
        $statusCounts = [];
        $statusColors = [];
        $statusMap = [
            'pending' => ['label' => 'Pending', 'color' => '#f59e0b'],
            'confirmed' => ['label' => 'Confirmed', 'color' => '#2563eb'],
            'processing' => ['label' => 'Processing', 'color' => '#7c3aed'],
            'shipped' => ['label' => 'Shipped', 'color' => '#0891b2'],
            'delivered' => ['label' => 'Delivered', 'color' => '#059669'],
            'cancelled' => ['label' => 'Cancelled', 'color' => '#dc2626'],
            'returned' => ['label' => 'Returned', 'color' => '#ea580c'],
            'refunded' => ['label' => 'Refunded', 'color' => '#64748b'],
        ];

        foreach ($statusMap as $key => $info) {
            $count = $orderStatuses[$key] ?? 0;
            if ($count > 0) {
                $statusLabels[] = $info['label'];
                $statusCounts[] = $count;
                $statusColors[] = $info['color'];
            }
        }

        $dailyStats = Order::where('created_at', '>=', $now->copy()->subDays(6)->startOfDay())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day")
            ->selectRaw('COALESCE(SUM(CASE WHEN payment_status = ? THEN total ELSE 0 END), 0) as revenue', [Order::PAYMENT_PAID])
            ->selectRaw('COUNT(*) as orders')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day')
            ->toArray();

        $weekLabels = [];
        $weekRevenue = [];
        $weekOrders = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->format('Y-m-d');
            $weekLabels[] = $now->copy()->subDays($i)->format('D');
            $weekRevenue[] = (float) ($dailyStats[$day]['revenue'] ?? 0);
            $weekOrders[] = (int) ($dailyStats[$day]['orders'] ?? 0);
        }

        $paymentBreakdown = Payment::selectRaw('payment_status, COUNT(*) as count')
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status')
            ->toArray();

        $todayStats = [
            'revenue' => (float) $revenueData->today_revenue,
            'orders' => $todayOrders,
            'signups' => User::whereDate('created_at', $todayStart->toDateString())->count(),
            'reviews' => Review::whereDate('created_at', $todayStart->toDateString())->count(),
            'pending_orders' => $orderStatuses[Order::STATUS_PENDING] ?? 0,
            'processing_orders' => $orderStatuses[Order::STATUS_PROCESSING] ?? 0,
        ];

        $managementSections = $this->managementSections([
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'totalCategories' => $totalCategories,
            'totalAttributes' => $totalAttributes,
            'totalVariations' => $totalVariations,
            'totalOrders' => $totalOrders,
            'fulfillmentBacklog' => $fulfillmentBacklog,
            'pendingPaymentsCount' => $pendingPaymentsCount,
            'openReturnsCount' => $openReturnsCount,
            'activeSalesCount' => $activeSalesCount,
            'activeCouponsCount' => $activeCouponsCount,
            'pendingReviewsCount' => $pendingReviewsCount,
            'activeSlidersCount' => $activeSlidersCount,
            'totalCustomers' => $totalCustomers,
            'newCustomersMonth' => $newCustomersMonth,
            'activeCartsCount' => $activeCartsCount,
            'lowStockCount' => $lowStockItems->count(),
            'outOfStockItems' => $outOfStockItems,
            'reportedReviewsCount' => $reportedReviewsCount,
        ]);

        $riskAlerts = [
            ['label' => 'Low stock SKUs', 'value' => $lowStockItems->count(), 'severity' => $lowStockItems->count() > 0 ? 'warning' : 'success', 'route' => 'admin.stock.dashboard'],
            ['label' => 'Out of stock SKUs', 'value' => $outOfStockItems, 'severity' => $outOfStockItems > 0 ? 'danger' : 'success', 'route' => 'admin.stock.dashboard'],
            ['label' => 'Pending returns', 'value' => $pendingReturnsCount, 'severity' => $pendingReturnsCount > 0 ? 'warning' : 'success', 'route' => 'admin.return-requests.index'],
            ['label' => 'Failed payments this month', 'value' => $failedPaymentsCount, 'severity' => $failedPaymentsCount > 0 ? 'danger' : 'success', 'route' => 'admin.payments.index'],
        ];

        return view('admin.dashboard', compact(
            'revenueData',
            'revenueGrowth',
            'totalOrders',
            'monthOrders',
            'todayOrders',
            'orderGrowth',
            'avgOrderValue',
            'totalCustomers',
            'newCustomersMonth',
            'customerGrowth',
            'activeProducts',
            'inactiveProducts',
            'totalProducts',
            'totalCategories',
            'activeCategories',
            'totalAttributes',
            'totalVariations',
            'lowStockItems',
            'outOfStockItems',
            'pendingReviewsCount',
            'reportedReviewsCount',
            'pendingReturnsCount',
            'openReturnsCount',
            'pendingPaymentsCount',
            'failedPaymentsCount',
            'activeCouponsCount',
            'activeSalesCount',
            'activeSlidersCount',
            'activeCartsCount',
            'abandonedCartsCount',
            'fulfillmentBacklog',
            'shippingQueue',
            'recentOrders',
            'topProducts',
            'topCategories',
            'chartLabels',
            'chartRevenue',
            'statusLabels',
            'statusCounts',
            'statusColors',
            'weekLabels',
            'weekRevenue',
            'weekOrders',
            'paymentBreakdown',
            'todayStats',
            'orderStatuses',
            'managementSections',
            'riskAlerts'
        ));
    }

    private function percentageChange($current, $previous): float
    {
        if ((float) $previous === 0.0) {
            return (float) $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function managementSections(array $metrics): array
    {
        return [
            [
                'title' => 'Catalog',
                'icon' => 'fa-boxes-stacked',
                'accent' => 'blue',
                'count' => $metrics['totalProducts'],
                'subtitle' => $metrics['activeProducts'] . ' active products',
                'items' => [
                    ['label' => 'Products', 'count' => $metrics['totalProducts'], 'route' => 'admin.products.index', 'icon' => 'fa-box'],
                    ['label' => 'Categories', 'count' => $metrics['totalCategories'], 'route' => 'admin.categories.index', 'icon' => 'fa-folder-open'],
                    ['label' => 'Attributes', 'count' => $metrics['totalAttributes'], 'route' => 'admin.attributes.index', 'icon' => 'fa-sliders'],
                    ['label' => 'Variations', 'count' => $metrics['totalVariations'], 'route' => 'admin.products.index', 'icon' => 'fa-layer-group'],
                ],
            ],
            [
                'title' => 'Orders',
                'icon' => 'fa-cart-shopping',
                'accent' => 'green',
                'count' => $metrics['totalOrders'],
                'subtitle' => $metrics['fulfillmentBacklog'] . ' need fulfillment',
                'items' => [
                    ['label' => 'All Orders', 'count' => $metrics['totalOrders'], 'route' => 'admin.orders.index', 'icon' => 'fa-receipt'],
                    ['label' => 'Backlog', 'count' => $metrics['fulfillmentBacklog'], 'route' => 'admin.orders.index', 'icon' => 'fa-list-check'],
                    ['label' => 'Payments', 'count' => $metrics['pendingPaymentsCount'], 'route' => 'admin.payments.index', 'icon' => 'fa-credit-card'],
                    ['label' => 'Returns', 'count' => $metrics['openReturnsCount'], 'route' => 'admin.return-requests.index', 'icon' => 'fa-rotate-left'],
                ],
            ],
            [
                'title' => 'Marketing',
                'icon' => 'fa-bullhorn',
                'accent' => 'orange',
                'count' => $metrics['activeSalesCount'] + $metrics['activeCouponsCount'],
                'subtitle' => 'Sales, coupons and homepage content',
                'items' => [
                    ['label' => 'Sales', 'count' => $metrics['activeSalesCount'], 'route' => 'admin.sales.index', 'icon' => 'fa-tags'],
                    ['label' => 'Coupons', 'count' => $metrics['activeCouponsCount'], 'route' => 'admin.coupons.index', 'icon' => 'fa-ticket'],
                    ['label' => 'Reviews', 'count' => $metrics['pendingReviewsCount'], 'route' => 'admin.reviews.index', 'icon' => 'fa-star-half-stroke'],
                    ['label' => 'Sliders', 'count' => $metrics['activeSlidersCount'], 'route' => 'admin.sliders.index', 'icon' => 'fa-images'],
                ],
            ],
            [
                'title' => 'Customers',
                'icon' => 'fa-users-gear',
                'accent' => 'purple',
                'count' => $metrics['totalCustomers'],
                'subtitle' => $metrics['newCustomersMonth'] . ' new this month',
                'items' => [
                    ['label' => 'Users', 'count' => $metrics['totalCustomers'], 'route' => 'admin.users.index', 'icon' => 'fa-users'],
                    ['label' => 'New Users', 'count' => $metrics['newCustomersMonth'], 'route' => 'admin.users.index', 'icon' => 'fa-user-plus'],
                    ['label' => 'Active Carts', 'count' => $metrics['activeCartsCount'], 'route' => 'admin.users.index', 'icon' => 'fa-basket-shopping'],
                    ['label' => 'Activity Logs', 'count' => null, 'route' => 'admin.user-activities.index', 'icon' => 'fa-clock-rotate-left'],
                ],
            ],
            [
                'title' => 'Operations',
                'icon' => 'fa-warehouse',
                'accent' => 'red',
                'count' => $metrics['lowStockCount'] + $metrics['outOfStockItems'],
                'subtitle' => 'Inventory and quality checks',
                'items' => [
                    ['label' => 'Stock', 'count' => $metrics['lowStockCount'], 'route' => 'admin.stock.dashboard', 'icon' => 'fa-warehouse'],
                    ['label' => 'Out of Stock', 'count' => $metrics['outOfStockItems'], 'route' => 'admin.stock.dashboard', 'icon' => 'fa-triangle-exclamation'],
                    ['label' => 'Pending Reviews', 'count' => $metrics['pendingReviewsCount'], 'route' => 'admin.reviews.index', 'icon' => 'fa-comments'],
                    ['label' => 'Reported Reviews', 'count' => $metrics['reportedReviewsCount'], 'route' => 'admin.reviews.index', 'icon' => 'fa-flag'],
                ],
            ],
            [
                'title' => 'System',
                'icon' => 'fa-screwdriver-wrench',
                'accent' => 'teal',
                'count' => null,
                'subtitle' => 'Configuration and maintenance',
                'items' => [
                    ['label' => 'Settings', 'count' => null, 'route' => 'admin.settings.index', 'icon' => 'fa-gear'],
                    ['label' => 'Cache', 'count' => null, 'route' => 'admin.cache.index', 'icon' => 'fa-bolt'],
                    ['label' => 'Email Logs', 'count' => null, 'route' => 'admin.email-logs.index', 'icon' => 'fa-envelope-open-text'],
                    ['label' => 'Tax Settings', 'count' => null, 'route' => 'admin.tax-settings.index', 'icon' => 'fa-file-invoice-dollar'],
                ],
            ],
        ];
    }
}
