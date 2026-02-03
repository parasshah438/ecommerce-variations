<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

/**
 * Shiprocket Webhook Controller
 * Handles webhook notifications from Shiprocket
 */
class ShiprocketWebhookController extends Controller
{
    /**
     * Handle shipment status webhook
     */
    public function handleShipmentStatus(Request $request): JsonResponse
    {
        try {
            // Verify webhook authenticity if secret is configured
            if (config('shiprocket.webhook_secret')) {
                $this->verifyWebhookSignature($request);
            }

            $data = $request->all();
            
            Log::info('Shiprocket webhook received', [
                'type' => 'shipment_status',
                'data' => $data
            ]);

            // Extract shipment information
            $shipmentId = $data['shipment_id'] ?? null;
            $orderId = $data['order_id'] ?? null;
            $status = $data['current_status'] ?? null;
            $awbCode = $data['awb'] ?? null;

            if (!$shipmentId && !$orderId) {
                Log::warning('Shiprocket webhook missing required identifiers', $data);
                return response()->json(['error' => 'Missing required identifiers'], 400);
            }

            // Find local shipment
            $shipment = $this->findShipment($shipmentId, $orderId);
            
            if (!$shipment) {
                Log::warning('Shipment not found for Shiprocket webhook', [
                    'shipment_id' => $shipmentId,
                    'order_id' => $orderId
                ]);
                return response()->json(['error' => 'Shipment not found'], 404);
            }

            // Update shipment status
            $this->updateShipmentFromWebhook($shipment, $data);

            // Update order status if needed
            $this->updateOrderFromShipmentStatus($shipment);

            return response()->json(['success' => true, 'message' => 'Webhook processed']);

        } catch (\Exception $e) {
            Log::error('Shiprocket webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Webhook processing failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle order status webhook
     */
    public function handleOrderStatus(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            
            Log::info('Shiprocket order webhook received', [
                'type' => 'order_status',
                'data' => $data
            ]);

            // Process order-specific webhook data
            // Implementation depends on specific webhook format
            
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Shiprocket order webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Verify webhook signature
     */
    protected function verifyWebhookSignature(Request $request): void
    {
        $signature = $request->header('X-Shiprocket-Signature');
        $payload = $request->getContent();
        $secret = config('shiprocket.webhook_secret');

        if (!$signature || !$secret) {
            throw new \Exception('Webhook signature verification failed');
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        if (!hash_equals($expectedSignature, $signature)) {
            throw new \Exception('Invalid webhook signature');
        }
    }

    /**
     * Find shipment by Shiprocket ID or Order ID
     */
    protected function findShipment($shipmentId, $orderId): ?Shipment
    {
        if ($shipmentId) {
            return Shipment::where('shiprocket_shipment_id', $shipmentId)->first();
        }

        if ($orderId) {
            return Shipment::where('shiprocket_order_id', $orderId)->first();
        }

        return null;
    }

    /**
     * Update shipment from webhook data
     */
    protected function updateShipmentFromWebhook(Shipment $shipment, array $data): void
    {
        $updateData = [];

        // Map Shiprocket status to our internal status
        if (isset($data['current_status'])) {
            $updateData['status'] = $this->mapShiprocketStatus($data['current_status']);
        }

        // Update tracking information
        if (isset($data['awb'])) {
            $updateData['awb_code'] = $data['awb'];
            if (!$shipment->tracking_number) {
                $updateData['tracking_number'] = $data['awb'];
            }
        }

        if (isset($data['courier_name'])) {
            $updateData['carrier'] = $data['courier_name'];
        }

        // Update dates based on status
        if (isset($data['current_status'])) {
            switch (strtolower($data['current_status'])) {
                case 'shipped':
                case 'picked up':
                    $updateData['shipped_date'] = now();
                    break;
                case 'delivered':
                    $updateData['delivered_date'] = now();
                    $updateData['actual_delivery'] = now();
                    break;
            }
        }

        // Store webhook data for reference
        $trackingData = $shipment->tracking_data ?? [];
        $trackingData[] = [
            'timestamp' => now(),
            'webhook_data' => $data
        ];
        $updateData['tracking_data'] = $trackingData;

        $shipment->update($updateData);

        Log::info("Shipment updated from webhook", [
            'shipment_id' => $shipment->id,
            'order_id' => $shipment->order_id,
            'status' => $updateData['status'] ?? 'unchanged'
        ]);
    }

    /**
     * Update order status based on shipment status
     */
    protected function updateOrderFromShipmentStatus(Shipment $shipment): void
    {
        $order = $shipment->order;
        
        if (!$order) {
            return;
        }

        $orderUpdateData = [];

        // Update order status based on shipment status
        switch ($shipment->status) {
            case Shipment::STATUS_PICKED_UP:
            case Shipment::STATUS_IN_TRANSIT:
                if ($order->status !== Order::STATUS_SHIPPED) {
                    $orderUpdateData['status'] = Order::STATUS_SHIPPED;
                }
                break;

            case Shipment::STATUS_DELIVERED:
                if ($order->status !== Order::STATUS_DELIVERED) {
                    $orderUpdateData['status'] = Order::STATUS_DELIVERED;
                }
                break;

            case Shipment::STATUS_RTO:
                // Handle return to origin
                $orderUpdateData['notes'] = ($order->notes ? $order->notes . "\n" : '') . 
                                          "Package returned to origin on " . now()->format('Y-m-d H:i:s');
                break;
        }

        if (!empty($orderUpdateData)) {
            $order->update($orderUpdateData);
            
            Log::info("Order status updated from shipment", [
                'order_id' => $order->id,
                'new_status' => $orderUpdateData['status'] ?? 'unchanged'
            ]);
        }
    }

    /**
     * Map Shiprocket status to our internal shipment status
     */
    protected function mapShiprocketStatus(string $shiprocketStatus): string
    {
        $statusMap = [
            'order confirmed' => Shipment::STATUS_CREATED,
            'pickup scheduled' => Shipment::STATUS_PICKUP_SCHEDULED,
            'picked up' => Shipment::STATUS_PICKED_UP,
            'shipped' => Shipment::STATUS_IN_TRANSIT,
            'in transit' => Shipment::STATUS_IN_TRANSIT,
            'out for delivery' => Shipment::STATUS_OUT_FOR_DELIVERY,
            'delivered' => Shipment::STATUS_DELIVERED,
            'rto' => Shipment::STATUS_RTO,
            'cancelled' => Shipment::STATUS_CANCELLED,
            'lost' => Shipment::STATUS_LOST,
            'damaged' => Shipment::STATUS_DAMAGED,
        ];

        $normalizedStatus = strtolower(trim($shiprocketStatus));
        
        return $statusMap[$normalizedStatus] ?? $normalizedStatus;
    }
}