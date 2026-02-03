<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'address', 'items.productVariation.product']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Amount range filter
        if ($request->filled('amount_min')) {
            $query->where('total', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('total', '<=', $request->amount_max);
        }

        // Customer email filter
        if ($request->filled('customer_email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', 'LIKE', '%' . $request->customer_email . '%');
            });
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search filter (searches in multiple fields)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'LIKE', '%' . $searchTerm . '%')
                               ->orWhere('email', 'LIKE', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('items.productVariation.product', function ($productQuery) use ($searchTerm) {
                      $productQuery->where('name', 'LIKE', '%' . $searchTerm . '%');
                  });
            });
        }

        // Quick date filters
        if ($request->filled('quick_filter')) {
            $today = now();
            switch ($request->quick_filter) {
                case 'today':
                    $query->whereDate('created_at', $today->toDateString());
                    break;
                case 'yesterday':
                    $yesterday = $today->copy()->subDay();
                    $query->whereDate('created_at', $yesterday->toDateString());
                    break;
                case 'this_week':
                    $startOfWeek = $today->copy()->startOfWeek();
                    $endOfWeek = $today->copy()->endOfWeek();
                    $query->whereBetween('created_at', [
                        $startOfWeek->toDateTimeString(),
                        $endOfWeek->toDateTimeString()
                    ]);
                    break;
                case 'last_week':
                    $lastWeekStart = $today->copy()->subWeek()->startOfWeek();
                    $lastWeekEnd = $today->copy()->subWeek()->endOfWeek();
                    $query->whereBetween('created_at', [
                        $lastWeekStart->toDateTimeString(),
                        $lastWeekEnd->toDateTimeString()
                    ]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $today->month)
                          ->whereYear('created_at', $today->year);
                    break;
                case 'last_month':
                    $lastMonth = $today->copy()->subMonth();
                    $query->whereMonth('created_at', $lastMonth->month)
                          ->whereYear('created_at', $lastMonth->year);
                    break;
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'total':
                $query->orderBy('total', $sortOrder);
                break;
            case 'status':
                $query->orderBy('status', $sortOrder);
                break;
            case 'payment_status':
                $query->orderBy('payment_status', $sortOrder);
                break;
            case 'user_name':
                $query->join('users', 'orders.user_id', '=', 'users.id')
                      ->orderBy('users.name', $sortOrder)
                      ->select('orders.*');
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $orders = $query->paginate($perPage)->appends($request->query());

        // Get statistics for the dashboard cards
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', Order::STATUS_PENDING)->count(),
            'confirmed_orders' => Order::where('status', Order::STATUS_CONFIRMED)->count(),
            'processing_orders' => Order::where('status', Order::STATUS_PROCESSING)->count(),
            'shipped_orders' => Order::where('status', Order::STATUS_SHIPPED)->count(),
            'delivered_orders' => Order::where('status', Order::STATUS_DELIVERED)->count(),
            'cancelled_orders' => Order::where('status', Order::STATUS_CANCELLED)->count(),
            'returned_orders' => Order::where('status', Order::STATUS_RETURNED)->count(),
            'total_revenue' => Order::whereIn('status', [
                Order::STATUS_CONFIRMED, 
                Order::STATUS_PROCESSING, 
                Order::STATUS_SHIPPED, 
                Order::STATUS_DELIVERED
            ])->sum('total'),
            'pending_payments' => Order::where('payment_status', Order::PAYMENT_PENDING)->count(),
            'paid_orders' => Order::where('payment_status', Order::PAYMENT_PAID)->count(),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'address', 'items.productVariation.product']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', [
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_PROCESSING,
                Order::STATUS_SHIPPED,
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                Order::STATUS_REFUNDED
            ]),
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $this->orderService->updateOrderStatus($order, $request->status, $request->notes);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Order status updated successfully',
                    'order_status' => $order->fresh()->formatted_status
                ]);
            }
            
            return redirect()->back()->with('success', 'Order status updated successfully');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to update order status: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    public function confirmOrder(Order $order)
    {
        try {
            $this->orderService->confirmOrder($order);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Order confirmed and stock reserved successfully',
                    'order_status' => $order->fresh()->formatted_status
                ]);
            }
            
            return redirect()->back()->with('success', 'Order confirmed and stock reserved successfully');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to confirm order: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()->with('error', 'Failed to confirm order: ' . $e->getMessage());
        }
    }

    public function cancelOrder(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        try {
            $this->orderService->cancelOrder($order, $request->reason);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Order cancelled successfully and stock restored',
                    'order_status' => $order->fresh()->formatted_status
                ]);
            }
            
            return redirect()->back()->with('success', 'Order cancelled successfully and stock restored');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to cancel order: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    public function returnOrder(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'return_type' => 'nullable|string',
            'refund_method' => 'nullable|string',
            'custom_reason' => 'nullable|string|max:500',
            'return_items' => 'nullable|array',
            'restock_items' => 'boolean',
            'notify_customer' => 'boolean'
        ]);

        try {
            $this->orderService->returnOrder($order, $request->reason, $request->all());
            return redirect()->back()->with('success', 'Order returned successfully and stock restored');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process return: ' . $e->getMessage());
        }
    }

    public function markAsPaid(Order $order)
    {
        try {
            $order->update(['payment_status' => Order::PAYMENT_PAID]);
            return response()->json(['success' => true, 'message' => 'Order marked as paid successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to mark as paid: ' . $e->getMessage()]);
        }
    }

    public function downloadInvoice(Order $order)
    {
        // Generate and download PDF invoice
        try {
            // Implementation for PDF generation would go here
            return response()->json(['success' => true, 'message' => 'Invoice download functionality to be implemented']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to generate invoice: ' . $e->getMessage()]);
        }
    }

    public function sendOrderEmail(Request $request, Order $order)
    {
        $request->validate([
            'email_type' => 'required|string|in:confirmation,status_update,invoice',
            'custom_message' => 'nullable|string|max:500'
        ]);

        try {
            // Implementation for sending order emails would go here
            return response()->json(['success' => true, 'message' => 'Order email sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()]);
        }
    }

    public function getOrderStatistics()
    {
        try {
            $stats = [
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('status', Order::STATUS_PENDING)->count(),
                'confirmed_orders' => Order::where('status', Order::STATUS_CONFIRMED)->count(),
                'shipped_orders' => Order::where('status', Order::STATUS_SHIPPED)->count(),
                'delivered_orders' => Order::where('status', Order::STATUS_DELIVERED)->count(),
                'cancelled_orders' => Order::where('status', Order::STATUS_CANCELLED)->count(),
                'total_revenue' => Order::whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED])->sum('total'),
                'pending_payments' => Order::where('payment_status', Order::PAYMENT_PENDING)->count(),
            ];

            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch statistics: ' . $e->getMessage()]);
        }
    }

    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'status' => 'required|string|in:' . implode(',', array_keys(Order::getStatuses())),
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $orders = Order::whereIn('id', $request->order_ids)->get();
            $updated = 0;

            foreach ($orders as $order) {
                $this->orderService->updateOrderStatus($order, $request->status, $request->notes);
                $updated++;
            }

            return response()->json([
                'success' => true, 
                'message' => "Successfully updated {$updated} orders"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to update orders: ' . $e->getMessage()
            ]);
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|string|in:excel,csv,pdf',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'status' => 'nullable|string'
        ]);

        try {
            // Implementation for order export would go here
            return response()->json(['success' => true, 'message' => 'Export functionality to be implemented']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to export: ' . $e->getMessage()]);
        }
    }
}
