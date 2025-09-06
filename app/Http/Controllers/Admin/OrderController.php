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

    public function index()
    {
        $orders = Order::with(['user', 'address', 'items.productVariation.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.orders.index', compact('orders'));
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
            return redirect()->back()->with('success', 'Order status updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    public function confirmOrder(Order $order)
    {
        try {
            $this->orderService->confirmOrder($order);
            return redirect()->back()->with('success', 'Order confirmed and stock reserved successfully');
        } catch (\Exception $e) {
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
            return redirect()->back()->with('success', 'Order cancelled successfully and stock restored');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    public function returnOrder(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        try {
            $this->orderService->returnOrder($order, $request->reason);
            return redirect()->back()->with('success', 'Order returned successfully and stock restored');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process return: ' . $e->getMessage());
        }
    }
}
