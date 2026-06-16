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
     * 
     * ShipRocket sends order status updates when:
     * - Order is confirmed in ShipRocket
     * - Pickup is scheduled
     * - Courier is assigned / AWB generated
     * - Order is picked up
     * - Order is shipped / in transit
     * - Out for delivery
     * - Delivered
     * - RTO / Cancelled
     */
    public function handleOrderStatus(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            
            Log::info('Shiprocket order webhook received', [
                'type' => 'order_status',
                'data' => $data
            ]);

            // Extract identifiers from the webhook payload
            // ShipRocket can send: order_id (ours), channel_order_id, or shipment_id
            $localOrderId     = $data['order_id'] ?? null;         // our local order ID if echoed back
            $channelOrderId   = $data['channel_order_id'] ?? null; // our local order ID from channel
            $shiprocketOrderId = $data['shiprocket_order_id'] ?? $data['order_id_sr'] ?? null;
            $shipmentId       = $data['shipment_id'] ?? null;
            $currentStatus    = $data['current_status'] ?? $data['status'] ?? '';
            $awb              = $data['awb'] ?? $data['awb_code'] ?? null;
            $courierName      = $data['courier_name'] ?? $data['courier_company_name'] ?? null;
            $eta              = $data['estimated_delivery'] ?? $data['etd'] ?? null;

            // Find the local order
            $order = null;
            $existingShipment = null;
            if ($localOrderId) {
                $order = Order::find($localOrderId);
            } elseif ($channelOrderId) {
                $order = Order::find($channelOrderId);
            } elseif ($shipmentId) {
                $existingShipment = Shipment::where('shiprocket_shipment_id', $shipmentId)->first();
                $order = $existingShipment?->order;
            } elseif ($shiprocketOrderId) {
                $existingShipment = Shipment::where('shiprocket_order_id', $shiprocketOrderId)->first();
                $order = $existingShipment?->order;
            }

            if (!$order) {
                Log::warning('Order not found for Shiprocket order webhook', [
                    'data' => $data
                ]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            // Find or create the related shipment
            $shipment = null;
            if ($shipmentId) {
                $shipment = Shipment::where('shiprocket_shipment_id', $shipmentId)->first();
            } elseif ($shiprocketOrderId) {
                $shipment = $order->shipments()
                    ->where('shiprocket_order_id', $shiprocketOrderId)
                    ->first();
            }

            if (!$shipment && ($shiprocketOrderId || $shipmentId || $awb)) {
                // Try to find by AWB
                if ($awb) {
                    $shipment = Shipment::where('awb_code', $awb)
                        ->orWhere('tracking_number', $awb)
                        ->first();
                }
            }

            // If we still don't have a shipment, create one
            if (!$shipment) {
                $shipment = Shipment::create([
                    'order_id'                => $order->id,
                    'shiprocket_order_id'     => $shiprocketOrderId,
                    'shiprocket_shipment_id'  => $shipmentId,
                    'status'                  => $this->mapShiprocketStatus($currentStatus),
                    'carrier'                 => $courierName,
                    'awb_code'                => $awb,
                    'tracking_number'         => $awb,
                    'estimated_delivery'      => $eta ? \Carbon\Carbon::parse($eta) : null,
                    'shiprocket_response'     => $data,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ]);
                Log::info('Shipment auto-created from webhook', [
                    'order_id' => $order->id,
                    'shipment_id' => $shipment->id,
                    'awb' => $awb
                ]);
            }

            // Update shipment with data from this webhook
            $shipmentUpdate = [];
            
            if ($currentStatus) {
                $shipmentUpdate['status'] = $this->mapShiprocketStatus($currentStatus);
            }
            if ($awb && !$shipment->awb_code) {
                $shipmentUpdate['awb_code'] = $awb;
                $shipmentUpdate['tracking_number'] = $awb;
            }
            if ($courierName && !$shipment->carrier) {
                $shipmentUpdate['carrier'] = $courierName;
            }
            if ($eta && !$shipment->estimated_delivery) {
                $shipmentUpdate['estimated_delivery'] = \Carbon\Carbon::parse($eta);
            }

            // Set shipped_date / delivered_date based on status
            $normalizedStatus = strtolower(trim($currentStatus));
            if (in_array($normalizedStatus, ['shipped', 'picked up', 'in transit'])) {
                $shipmentUpdate['shipped_date'] = $shipment->shipped_date ?? now();
            }
            if ($normalizedStatus === 'delivered') {
                $shipmentUpdate['delivered_date'] = now();
                $shipmentUpdate['actual_delivery'] = now();
            }

            // Append this webhook event to tracking_data for scan event history
            $trackingData = $shipment->tracking_data ?? [];
            $trackingData[] = [
                'timestamp' => now()->toISOString(),
                'webhook_data' => [
                    'current_status'    => $currentStatus,
                    'current_location'  => $data['current_location'] ?? $data['location'] ?? $data['pickup_location'] ?? '',
                    'activity'          => $data['activity'] ?? $data['remarks'] ?? $currentStatus,
                    'awb'               => $awb,
                    'courier_name'      => $courierName,
                    'estimated_delivery' => $eta,
                ]
            ];
            $shipmentUpdate['tracking_data'] = $trackingData;

            $shipment->update($shipmentUpdate);

            // Update order status based on shipment status
            $orderUpdateData = [];
            switch ($shipment->status) {
                case Shipment::STATUS_PICKED_UP:
                case Shipment::STATUS_IN_TRANSIT:
                case Shipment::STATUS_OUT_FOR_DELIVERY:
                    if (!in_array($order->status, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED])) {
                        $orderUpdateData['status'] = Order::STATUS_SHIPPED;
                        $orderUpdateData['shipped_at'] = $order->shipped_at ?? now();
                    }
                    break;

                case Shipment::STATUS_DELIVERED:
                    if ($order->status !== Order::STATUS_DELIVERED) {
                        $orderUpdateData['status'] = Order::STATUS_DELIVERED;
                        $orderUpdateData['delivered_at'] = now();
                    }
                    break;

                case Shipment::STATUS_CANCELLED:
                case Shipment::STATUS_RTO:
                    $orderUpdateData['notes'] = ($order->notes ? $order->notes . "\n" : '') .
                        "Shipment {$shipment->status} via webhook on " . now()->format('Y-m-d H:i:s');
                    break;
            }

            if (!empty($orderUpdateData)) {
                $order->update($orderUpdateData);
                Log::info("Order #{$order->id} status updated from order-status webhook", [
                    'new_order_status' => $orderUpdateData['status'] ?? 'unchanged'
                ]);
            }

            Log::info('Shiprocket order webhook processed successfully', [
                'order_id' => $order->id,
                'shipment_id' => $shipment->id,
                'status' => $currentStatus
            ]);

            return response()->json(['success' => true, 'message' => 'Webhook processed']);

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
                    $orderUpdateData['shipped_at'] = $order->shipped_at ?? now();
                }
                break;

            case Shipment::STATUS_DELIVERED:
                if ($order->status !== Order::STATUS_DELIVERED) {
                    $orderUpdateData['status'] = Order::STATUS_DELIVERED;
                    $orderUpdateData['delivered_at'] = now();
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
