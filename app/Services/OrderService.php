<?php

namespace App\Services;

use App\Models\Order;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $stockService;
    protected $shiprocketProcessor;

    public function __construct(
        StockService $stockService,
        ShiprocketOrderProcessor $shiprocketProcessor
    ) {
        $this->stockService = $stockService;
        $this->shiprocketProcessor = $shiprocketProcessor;
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

            // Process for shipping with Shiprocket
            $shippingResult = $this->processOrderForShipping($order);

            DB::commit();
            
            Log::info("Order confirmed and processed for shipping - Order #{$order->id}", [
                'shipping_processed' => $shippingResult['success'],
                'shiprocket_order_id' => $shippingResult['shiprocket_order_id'] ?? null
            ]);
            
            return [
                'success' => true,
                'order_confirmed' => true,
                'shipping_result' => $shippingResult
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to confirm order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process confirmed order for shipping
     * This method handles the Shiprocket integration
     */
    protected function processOrderForShipping(Order $order): array
    {
        try {
            // Only process for shipping if payment is completed
            if ($order->payment_status !== Order::PAYMENT_PAID) {
                return [
                    'success' => false,
                    'message' => 'Payment not completed, skipping shipping process'
                ];
            }

            // Check if Shiprocket credentials are configured
            if (!app('shiprocket.enabled')) {
                Log::warning("Order #{$order->id} confirmed but Shiprocket not configured");
                return [
                    'success' => false,
                    'message' => 'Shiprocket credentials not configured. Please configure SHIPROCKET_EMAIL and SHIPROCKET_PASSWORD in .env file'
                ];
            }

            // Check if shipping is enabled in configuration
            if (!config('services.shiprocket.auto_create_shipments', true)) {
                return [
                    'success' => false,
                    'message' => 'Automatic shipment creation is disabled'
                ];
            }

            // Process with Shiprocket
            $result = $this->shiprocketProcessor->processConfirmedOrder($order);
            
            if ($result['success']) {
                // Optionally auto-assign best courier
                if (config('services.shiprocket.auto_assign_courier', false)) {
                    $courierResult = $this->shiprocketProcessor->assignBestCourier($order);
                    $result['courier_assignment'] = $courierResult;
                }
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("Shipping processing failed for Order #{$order->id}", [
                'error' => $e->getMessage()
            ]);

            // Don't fail the entire order confirmation if shipping fails
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Order confirmed but shipping setup failed. Manual processing required.'
            ];
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
