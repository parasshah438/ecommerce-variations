<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturnRequest;
use App\Models\Payment;
use App\Services\StockService;
use App\Services\ShiprocketReturnService;
use App\Services\RazorpayService;
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
                'confirmed_at' => now(),
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
                // Pass restoreStock:false — stock was already restored per-item in the foreach above
                DB::commit();
                $this->cancelOrder($order, $reason ?: 'All items cancelled', false);
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
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to cancel order #{$order->id}: " . $e->getMessage());
            throw $e;
        }

        // --- Razorpay refund (outside DB transaction — external API call) ---
        $payment = $order->latestPayment;
        if ($payment && $payment->payment_status === Payment::PAYMENT_STATUS_PAID
            && $payment->gateway === Payment::GATEWAY_RAZORPAY) {
            try {
                $razorpay = app(\App\Services\RazorpayService::class);
                $refund = $razorpay->refundPayment(
                    $payment->gateway_payment_id,
                    $order->total,
                    ['reason' => $reason ?: 'Order cancelled']
                );
                if (isset($refund['id'])) {
                    $payment->update([
                        'payment_status' => Payment::PAYMENT_STATUS_REFUNDED,
                        'refund_id'      => $refund['id'],
                        'refund_amount'  => $refund['amount'] / 100,
                        'refunded_at'    => now(),
                    ]);
                    Log::info("Razorpay refund issued for Order #{$order->id}", ['refund_id' => $refund['id']]);
                }
            } catch (\Exception $e) {
                Log::error("Razorpay refund failed for Order #{$order->id}: " . $e->getMessage());
                // Refund failure does not roll back the cancellation
            }
        }

        // --- Cancel ShipRocket shipment (outside DB transaction) ---
        if (app('shiprocket.enabled')) {
            try {
                $shipment = $order->activeShipment ?? $order->shipments()->where('status', '!=', 'cancelled')->first();
                if ($shipment && $shipment->shiprocket_order_id) {
                    $this->shiprocketProcessor
                        ->getShiprocketManager()
                        ->orders()
                        ->cancelOrders([$shipment->shiprocket_order_id]);
                    $shipment->update(['status' => 'cancelled']);
                    Log::info("ShipRocket order #{$shipment->shiprocket_order_id} cancelled for Order #{$order->id}");
                }
            } catch (\Exception $e) {
                Log::error("ShipRocket cancellation failed for Order #{$order->id}: " . $e->getMessage());
                // ShipRocket failure does not roll back the cancellation
            }
        }

        return true;
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

        $updateData = [
            'status' => $newStatus,
            'notes' => $notes,
        ];

        // Populate timestamps when transitioning to each status
        switch ($newStatus) {
            case Order::STATUS_CONFIRMED:
                $updateData['confirmed_at'] = now();
                break;
            case Order::STATUS_PROCESSING:
                $updateData['processing_at'] = now();
                break;
            case Order::STATUS_SHIPPED:
                $updateData['shipped_at'] = now();
                break;
            case Order::STATUS_DELIVERED:
                $updateData['delivered_at'] = now();
                break;
        }

        $order->update($updateData);

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

    // ──────────────────────────────────────────────
    //  RETURN REQUEST WORKFLOW (Customer → Admin → Pickup → Refund)
    // ──────────────────────────────────────────────

    /**
     * Customer submits a return request for a delivered order.
     * Creates an OrderReturnRequest with pending status.
     */
    public function submitReturnRequest(Order $order, array $itemIds, string $reason): OrderReturnRequest
    {
        if (!$order->canBeReturned()) {
            throw new \Exception('Only delivered orders can be returned.');
        }

        // Check return window (30 days)
        $returnWindow = 30;
        if ($order->delivered_at && $order->delivered_at->diffInDays(now()) > $returnWindow) {
            throw new \Exception("Return window of {$returnWindow} days has expired.");
        }

        // Calculate refund amount from selected items
        $items = $order->items()->whereIn('id', $itemIds)->get();
        if ($items->isEmpty()) {
            throw new \Exception('No valid items selected for return.');
        }

        $refundAmount = 0;
        foreach ($items as $item) {
            $refundAmount += $item->price * $item->quantity;
        }

        // Create the return request
        $returnRequest = OrderReturnRequest::create([
            'order_id'        => $order->id,
            'user_id'         => $order->user_id,
            'status'          => OrderReturnRequest::STATUS_PENDING,
            'customer_reason' => $reason,
            'return_items'    => $itemIds,
            'refund_amount'   => $refundAmount,
        ]);

        // Update order notes
        $order->update([
            'notes' => ($order->notes ? $order->notes . "\n" : '') .
                       "Return requested on " . now()->format('Y-m-d H:i') . ": {$reason}",
        ]);

        Log::info("Return request submitted - Order #{$order->id}, Request #{$returnRequest->id}, Amount: {$refundAmount}");

        return $returnRequest;
    }

    /**
     * Admin approves a return request.
     * Restores stock for returned items and schedules Shiprocket return pickup.
     */
    public function approveReturnRequest(OrderReturnRequest $returnRequest, $adminUser, ?string $adminNote = null): void
    {
        if (!$returnRequest->canBeApproved()) {
            throw new \Exception('Return request cannot be approved in its current state.');
        }

        $order = $returnRequest->order;

        DB::beginTransaction();
        try {
            // Restore stock for returned items
            $returnItemIds = $returnRequest->return_items ?? [];
            $itemsToRestore = $order->items()->whereIn('id', $returnItemIds)->get();

            foreach ($itemsToRestore as $item) {
                if ($item->productVariation && $item->productVariation->stock) {
                    $item->productVariation->stock->increment('quantity', $item->quantity);
                    $item->productVariation->stock->update(['in_stock' => true]);
                }
            }

            // Update request status
            $returnRequest->update([
                'status'      => OrderReturnRequest::STATUS_APPROVED,
                'admin_note'  => $adminNote,
                'reviewed_by' => $adminUser->id,
                'reviewed_at' => now(),
            ]);

            // Update order
            $order->update([
                'status' => Order::STATUS_RETURNED,
                'returned_at' => now(),
                'notes' => ($order->notes ? $order->notes . "\n" : '') .
                           "Return approved on " . now()->format('Y-m-d H:i') . ": {$adminNote}",
            ]);

            DB::commit();

            Log::info("Return request #{$returnRequest->id} approved, stock restored for Order #{$order->id}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to approve return request #{$returnRequest->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Schedule Shiprocket return pickup for an approved return request.
     */
    public function scheduleReturnPickup(OrderReturnRequest $returnRequest, ?string $pickupDate = null): void
    {
        if (!$returnRequest->isApproved()) {
            throw new \Exception('Return request must be approved before scheduling pickup.');
        }

        $order = $returnRequest->order;
        $shipment = $order->activeShipment;

        if (!$shipment || !$shipment->shiprocket_order_id) {
            // If no Shiprocket shipment exists, mark pickup scheduled locally
            $returnRequest->update([
                'status' => OrderReturnRequest::STATUS_PICKUP_SCHEDULED,
                'pickup_scheduled_date' => $pickupDate ? now()->parse($pickupDate) : now()->addDay(),
            ]);
            Log::info("Return pickup marked scheduled (no Shiprocket) for request #{$returnRequest->id}");
            return;
        }

        // Shiprocket return pickup via ShiprocketReturnService
        if (app('shiprocket.enabled')) {
            try {
                $returnService = app(ShiprocketReturnService::class);

                // Build return data for Shiprocket using original order's pickup location
                $returnData = $this->buildShiprocketReturnData($order, $returnRequest, $shipment);
                $result = $returnService->processReturn($returnData);

                if ($result['success'] && isset($result['return_order'])) {
                    $returnOrder = $result['return_order'];
                    $returnRequest->update([
                        'status'                     => OrderReturnRequest::STATUS_PICKUP_SCHEDULED,
                        'shiprocket_return_order_id' => $returnOrder['order_id'] ?? null,
                        'shiprocket_shipment_id'     => $returnOrder['shipment_id'] ?? null,
                        'pickup_scheduled_date'      => $pickupDate ? now()->parse($pickupDate) : now()->addDay(),
                    ]);

                    Log::info("Shiprocket return pickup scheduled for request #{$returnRequest->id}", [
                        'shiprocket_return_order_id' => $returnOrder['order_id'] ?? null,
                    ]);
                    return;
                }

                // If Shiprocket processReturn fails, still mark as scheduled for manual handling
                $returnRequest->update([
                    'status'                => OrderReturnRequest::STATUS_PICKUP_SCHEDULED,
                    'pickup_scheduled_date' => $pickupDate ? now()->parse($pickupDate) : now()->addDay(),
                ]);
                Log::warning("Shiprocket return processing failed, marked scheduled locally: " . ($result['error'] ?? 'Unknown'));

            } catch (\Exception $e) {
                Log::error("Shiprocket return pickup failed for request #{$returnRequest->id}: " . $e->getMessage());
                // Fall back to local pickup schedule
                $returnRequest->update([
                    'status'                => OrderReturnRequest::STATUS_PICKUP_SCHEDULED,
                    'pickup_scheduled_date' => $pickupDate ? now()->parse($pickupDate) : now()->addDay(),
                ]);
            }
        } else {
            // Shiprocket not configured — mark locally
            $returnRequest->update([
                'status'                => OrderReturnRequest::STATUS_PICKUP_SCHEDULED,
                'pickup_scheduled_date' => $pickupDate ? now()->parse($pickupDate) : now()->addDay(),
            ]);
            Log::info("Return pickup scheduled locally (Shiprocket not configured) for request #{$returnRequest->id}");
        }
    }

    /**
     * Build the return data payload for Shiprocket.
     */
    protected function buildShiprocketReturnData(Order $order, OrderReturnRequest $returnRequest, $shipment): array
    {
        $address = $order->address;
        $user = $order->user;

        $returnItems = [];
        $items = $order->items()->whereIn('id', $returnRequest->return_items ?? [])->get();
        foreach ($items as $item) {
            $product = $item->productVariation->product ?? null;
            $returnItems[] = [
                'name'          => $product->name ?? 'Product',
                'sku'           => $item->productVariation->sku ?? 'SKU',
                'units'         => $item->quantity,
                'selling_price' => $item->price,
            ];
        }

        return [
            'order_id'              => (string) $order->id,
            'order_date'            => $order->created_at->format('Y-m-d H:i'),
            'pickup_customer_name'  => $address->name ?? $user->name,
            'pickup_address'        => $address->address_line ?? '',
            'pickup_city'           => $address->city ?? '',
            'pickup_state'          => $address->state ?? '',
            'pickup_country'        => $address->country ?? 'India',
            'pickup_pincode'        => $address->zip ?? '',
            'pickup_email'          => $user->email,
            'pickup_phone'          => $address->phone ?? $user->phone ?? '',
            'shipping_customer_name' => config('shiprocket.return_shipping_name', 'Warehouse'),
            'shipping_address'      => config('shiprocket.return_shipping_address', ''),
            'shipping_city'         => config('shiprocket.return_shipping_city', ''),
            'shipping_state'        => config('shiprocket.return_shipping_state', ''),
            'shipping_country'      => 'India',
            'shipping_pincode'      => config('shiprocket.return_shipping_pincode', ''),
            'shipping_email'        => config('shiprocket.return_shipping_email', ''),
            'shipping_phone'        => config('shiprocket.return_shipping_phone', ''),
            'order_items'           => $returnItems,
            'payment_method'        => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
            'sub_total'             => $returnRequest->refund_amount ?? $order->subtotal,
            'length'                => 20,
            'breadth'               => 15,
            'height'                => 10,
            'weight'                => 0.5,
        ];
    }

    /**
     * Process refund for a picked-up return request.
     * Triggers Razorpay refund for the return amount.
     */
    public function processReturnRefund(OrderReturnRequest $returnRequest, float $refundAmount): void
    {
        if ($returnRequest->status !== OrderReturnRequest::STATUS_PICKED_UP) {
            throw new \Exception('Items must be picked up before processing refund.');
        }

        $order = $returnRequest->order;

        // Try Razorpay refund
        $refundId = null;
        $payment = $order->latestPayment;
        if ($payment && $payment->payment_status === Payment::PAYMENT_STATUS_PAID
            && $payment->gateway === Payment::GATEWAY_RAZORPAY) {
            try {
                $razorpay = app(RazorpayService::class);
                $refund = $razorpay->refundPayment(
                    $payment->gateway_payment_id,
                    $refundAmount,
                    ['reason' => 'Return request #' . $returnRequest->id]
                );
                if (isset($refund['id'])) {
                    $refundId = $refund['id'];
                    // Record in payment metadata
                    $existingMeta = $payment->metadata ?? [];
                    $existingMeta['return_refunds'][] = [
                        'return_request_id' => $returnRequest->id,
                        'refund_id'         => $refund['id'],
                        'amount'            => $refundAmount,
                        'created_at'        => now()->toISOString(),
                    ];
                    $payment->update(['metadata' => $existingMeta]);
                    Log::info("Razorpay refund issued for return request #{$returnRequest->id}", [
                        'refund_id' => $refund['id'],
                        'amount'    => $refundAmount,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Razorpay refund failed for return request #{$returnRequest->id}: " . $e->getMessage());
                // Continue — we still mark it refunded locally
            }
        }

        // Update return request
        $returnRequest->update([
            'status'       => OrderReturnRequest::STATUS_REFUNDED,
            'refund_id'    => $refundId,
            'refund_amount' => $refundAmount,
            'refunded_at'  => now(),
        ]);

        // Update order status
        $order->update([
            'payment_status' => Order::PAYMENT_REFUNDED,
            'refunded_at'    => now(),
            'notes' => ($order->notes ? $order->notes . "\n" : '') .
                       "Return refunded ₹" . number_format($refundAmount, 2) . " on " . now()->format('Y-m-d H:i'),
        ]);

        Log::info("Return refund processed for request #{$returnRequest->id}, Order #{$order->id}, Amount: ₹{$refundAmount}");
    }
}
