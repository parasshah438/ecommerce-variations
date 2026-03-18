<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
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
            
            // Update order status only — payment_status is managed by the payment flow
            $order->update([
                'status' => Order::STATUS_CONFIRMED,
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
            // Allow COD orders (payment on delivery) and already-paid orders through to Shiprocket
            $isCod = $order->payment_method === 'cod';
            if ($order->payment_status !== Order::PAYMENT_PAID && !$isCod) {
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
            if (!config('shiprocket.auto_create_shipments', true)) {
                return [
                    'success' => false,
                    'message' => 'Automatic shipment creation is disabled'
                ];
            }

            // Process with Shiprocket
            $result = $this->shiprocketProcessor->processConfirmedOrder($order);
            
            if ($result['success']) {
                // Optionally auto-assign best courier
                if (config('shiprocket.auto_assign_courier', false)) {
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
     * Partially cancel specific items from an order.
     *
     * Flow:
     *  1. Validate items belong to this order and are still active
     *  2. Mark each item as cancelled, restore its stock
     *  3. Recalculate order total
     *  4. Issue partial Razorpay refund for the cancelled items
     *  5. Cancel existing Shiprocket shipment, recreate with remaining items
     *  6. If all items cancelled → cancel the whole order
     */
    public function cancelOrderItems(Order $order, array $itemIds, string $reason = ''): array
    {
        // Only allow partial cancel while order is pending, confirmed, or processing
        if (!in_array($order->status, [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PROCESSING,
        ])) {
            throw new \Exception("Items can only be cancelled before the order is shipped.");
        }

        // Load active items that belong to this order
        $itemsToCancel = $order->items()
            ->whereIn('id', $itemIds)
            ->where('status', OrderItem::STATUS_ACTIVE)
            ->get();

        if ($itemsToCancel->isEmpty()) {
            throw new \Exception("No valid active items found to cancel.");
        }

        DB::beginTransaction();
        try {
            $refundAmount = 0;

            foreach ($itemsToCancel as $item) {
                $itemTotal = $item->price * $item->quantity;
                $refundAmount += $itemTotal;

                // Restore stock (only if order was confirmed/processing — stock was reserved)
                if (in_array($order->status, [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING])) {
                    if ($item->productVariation && $item->productVariation->stock) {
                        $item->productVariation->stock->increment('quantity', $item->quantity);
                    }
                }

                // Mark item as cancelled
                $item->update([
                    'status'               => OrderItem::STATUS_CANCELLED,
                    'cancelled_at'         => now(),
                    'cancellation_reason'  => $reason,
                    'refund_amount'        => $itemTotal,
                ]);
            }

            // Check remaining active items
            $order->load('items');
            $activeItems = $order->items->where('status', OrderItem::STATUS_ACTIVE);

            if ($activeItems->isEmpty()) {
                // All items cancelled → cancel the full order
                DB::commit();
                $this->cancelOrder($order, $reason ?: 'All items cancelled');
                return [
                    'success'       => true,
                    'all_cancelled' => true,
                    'refund_amount' => $refundAmount,
                    'message'       => 'All items cancelled. Full order has been cancelled.',
                ];
            }

            // Recalculate order total from active items only
            $newSubtotal = $activeItems->sum(fn($i) => $i->price * $i->quantity);
            // Keep shipping cost and tax proportional — simplest safe approach: zero them if needed
            $order->update([
                'subtotal' => $newSubtotal,
                'total'    => $newSubtotal + ($order->shipping_cost ?? 0) + ($order->tax_amount ?? 0) - ($order->coupon_discount ?? 0),
                'notes'    => ($order->notes ? $order->notes . "\n" : '') .
                              "Partial cancel on " . now()->format('Y-m-d H:i') . ": {$reason}",
            ]);

            DB::commit();

            // --- Razorpay partial refund (outside DB transaction — external API call) ---
            $refundResult = $this->issuePartialRefund($order, $refundAmount, $reason);

            // --- Recreate Shiprocket shipment with remaining items (outside DB transaction) ---
            $shipmentResult = $this->recreateShiprocketShipment($order, $reason);

            Log::info("Partial item cancellation complete for Order #{$order->id}", [
                'cancelled_items'  => $itemsToCancel->pluck('id'),
                'refund_amount'    => $refundAmount,
                'refund_result'    => $refundResult,
                'shipment_result'  => $shipmentResult,
            ]);

            return [
                'success'          => true,
                'all_cancelled'    => false,
                'refund_amount'    => $refundAmount,
                'refund_result'    => $refundResult,
                'shipment_result'  => $shipmentResult,
                'message'          => count($itemIds) . ' item(s) cancelled. Refund of ₹' . number_format($refundAmount, 2) . ' initiated.',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Partial item cancellation failed for Order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Issue a Razorpay partial refund for the given amount.
     * Silent on failure — logs error, doesn't throw.
     */
    protected function issuePartialRefund(Order $order, float $refundAmount, string $reason): array
    {
        try {
            $payment = $order->latestPayment;
            if (!$payment || $payment->payment_status !== Payment::PAYMENT_STATUS_PAID) {
                return ['success' => false, 'message' => 'No paid payment found, skipping refund.'];
            }

            if ($payment->gateway !== Payment::GATEWAY_RAZORPAY) {
                return ['success' => false, 'message' => 'Partial refund only supported for Razorpay. Process manually for ' . $payment->gateway . '.'];
            }

            $razorpay = app(\App\Services\RazorpayService::class);
            $refund = $razorpay->refundPayment(
                $payment->gateway_payment_id,
                $refundAmount,
                ['reason' => $reason ?: 'Partial item cancellation']
            );

            // Record refund against the payment
            $existingMeta = $payment->metadata ?? [];
            $existingMeta['partial_refunds'][] = [
                'refund_id'  => $refund['id'] ?? null,
                'amount'     => $refundAmount,
                'reason'     => $reason,
                'created_at' => now()->toISOString(),
            ];
            $payment->update(['metadata' => $existingMeta]);

            return ['success' => true, 'refund_id' => $refund['id'] ?? null, 'amount' => $refundAmount];

        } catch (\Exception $e) {
            Log::error("Partial refund failed for Order #{$order->id}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cancel the existing Shiprocket shipment and recreate with remaining active items.
     * Silent on failure — order status is already updated.
     */
    protected function recreateShiprocketShipment(Order $order, string $reason): array
    {
        try {
            if (!app('shiprocket.enabled')) {
                return ['success' => false, 'message' => 'Shiprocket not configured.'];
            }

            $shipment = $order->activeShipment;

            // Cancel old shipment in Shiprocket if it exists
            if ($shipment && $shipment->shiprocket_order_id) {
                try {
                    $this->shiprocketProcessor
                        ->getShiprocketManager()
                        ->orders()
                        ->cancelOrders([$shipment->shiprocket_order_id]);

                    $shipment->update(['status' => 'cancelled']);
                    Log::info("Cancelled Shiprocket order #{$shipment->shiprocket_order_id} for partial item cancel on Order #{$order->id}");
                } catch (\Exception $e) {
                    Log::warning("Could not cancel Shiprocket order for Order #{$order->id}: " . $e->getMessage());
                }
            }

            // Reload order with fresh active items
            $order->load('items.productVariation.product');

            // Only recreate if order is confirmed/processing (has stock reserved, ready to ship)
            if (!in_array($order->status, [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING])) {
                return ['success' => false, 'message' => 'Order not in shippable state, skipping Shiprocket recreate.'];
            }

            $result = $this->shiprocketProcessor->processConfirmedOrder($order);
            return $result;

        } catch (\Exception $e) {
            Log::error("Shiprocket recreate failed for Order #{$order->id}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
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
