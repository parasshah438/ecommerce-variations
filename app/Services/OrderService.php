<?php

namespace App\Services;

use App\Models\Order;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Confirm order and reserve stock (when payment is successful)
     */
    public function confirmOrder(Order $order)
    {
        if ($order->status !== Order::STATUS_PENDING) {
            throw new \Exception("Only pending orders can be confirmed");
        }

        DB::beginTransaction();
        try {
            // Validate stock availability
            $this->stockService->validateOrderStock($order);
            
            // Reserve stock
            $this->stockService->reserveStockForOrder($order);
            
            // Update order status
            $order->update([
                'status' => Order::STATUS_CONFIRMED,
                'payment_status' => Order::PAYMENT_PAID,
            ]);

            DB::commit();
            Log::info("Order confirmed and stock reserved - Order #{$order->id}");
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to confirm order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel order and restore stock
     */
    public function cancelOrder(Order $order, string $reason = '', bool $restoreStock = true)
    {
        if (!$order->canBeCancelled()) {
            throw new \Exception("Order cannot be cancelled at this stage");
        }

        DB::beginTransaction();
        try {
            // Restore stock if it was already reserved
            if ($restoreStock && in_array($order->status, [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING])) {
                $this->stockService->restoreStockForOrder($order, "Order cancelled: {$reason}");
            }
            
            // Update order status
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'notes' => $reason,
            ]);

            DB::commit();
            Log::info("Order cancelled - Order #{$order->id}, Reason: {$reason}");
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to cancel order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle payment failure and restore stock
     */
    public function handlePaymentFailure(Order $order, string $reason = '')
    {
        DB::beginTransaction();
        try {
            // Restore stock if it was reserved
            if ($order->status === Order::STATUS_CONFIRMED) {
                $this->stockService->restoreStockForOrder($order, "Payment failed: {$reason}");
            }
            
            // Update order and payment status
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'payment_status' => Order::PAYMENT_FAILED,
                'cancelled_at' => now(),
                'notes' => "Payment failed: {$reason}",
            ]);

            DB::commit();
            Log::info("Payment failed and stock restored - Order #{$order->id}, Reason: {$reason}");
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to handle payment failure for order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process order return and restore stock
     */
    public function returnOrder(Order $order, array $returnedItems = null, string $reason = '')
    {
        if (!$order->canBeReturned()) {
            throw new \Exception("Order cannot be returned at this stage");
        }

        DB::beginTransaction();
        try {
            // Handle stock restoration for returned items
            $this->stockService->handleReturnedOrder($order, $returnedItems);
            
            // Update order status
            $order->update([
                'status' => Order::STATUS_RETURNED,
                'returned_at' => now(),
                'notes' => $reason,
            ]);

            DB::commit();
            Log::info("Order returned and stock restored - Order #{$order->id}, Reason: {$reason}");
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to process return for order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Order $order, string $newStatus, string $notes = '')
    {
        $allowedTransitions = [
            Order::STATUS_PENDING => [Order::STATUS_CONFIRMED, Order::STATUS_CANCELLED],
            Order::STATUS_CONFIRMED => [Order::STATUS_PROCESSING, Order::STATUS_CANCELLED],
            Order::STATUS_PROCESSING => [Order::STATUS_SHIPPED, Order::STATUS_CANCELLED],
            Order::STATUS_SHIPPED => [Order::STATUS_DELIVERED],
            Order::STATUS_DELIVERED => [Order::STATUS_RETURNED],
        ];

        if (!isset($allowedTransitions[$order->status]) || 
            !in_array($newStatus, $allowedTransitions[$order->status])) {
            throw new \Exception("Invalid status transition from {$order->status} to {$newStatus}");
        }

        $order->update([
            'status' => $newStatus,
            'notes' => $notes,
        ]);

        Log::info("Order status updated - Order #{$order->id}, From: {$order->status}, To: {$newStatus}");
        return true;
    }

    /**
     * Get order stock status
     */
    public function getOrderStockStatus(Order $order)
    {
        return $this->stockService->getOrderStockStatus($order);
    }
}
