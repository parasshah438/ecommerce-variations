<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Shiprocket Manager
 * Main facade class for all Shiprocket operations
 */
class ShiprocketManager
{
    protected $orderService;
    protected $courierService;
    protected $shipmentService;
    protected $returnService;

    public function __construct()
    {
        $this->orderService = new ShiprocketService();
        $this->courierService = new ShiprocketCourierService();
        $this->shipmentService = new ShiprocketShipmentService();
        $this->returnService = new ShiprocketReturnService();
    }

    /**
     * Get order service instance
     */
    public function orders(): ShiprocketService
    {
        return $this->orderService;
    }

    /**
     * Get courier service instance
     */
    public function couriers(): ShiprocketCourierService
    {
        return $this->courierService;
    }

    /**
     * Get shipment service instance
     */
    public function shipments(): ShiprocketShipmentService
    {
        return $this->shipmentService;
    }

    /**
     * Get return service instance
     */
    public function returns(): ShiprocketReturnService
    {
        return $this->returnService;
    }

    /**
     * Complete order fulfillment workflow
     */
    public function createOrder(array $orderData, bool $autoAssignCourier = true): array
    {
        try {
            Log::info('Starting order fulfillment', ['order_id' => $orderData['order_id'] ?? 'unknown']);

            // Step 1: Create order
            $order = $this->orderService->createOrder($orderData);
            if (!isset($order['shipment_id'])) {
                throw new Exception('Order creation failed - no shipment ID returned');
            }

            $shipmentId = $order['shipment_id'];
            $result = [
                'order_created' => true,
                'order_id' => $order['order_id'],
                'shipment_id' => $shipmentId,
            ];

            // Step 2: Auto-assign courier if enabled
            if ($autoAssignCourier && config('shiprocket.auto_assign_courier', false)) {
                $courierAssignment = $this->autoAssignCourier($orderData);
                if ($courierAssignment['success']) {
                    $result['courier_assigned'] = true;
                    $result['courier'] = $courierAssignment['courier'];
                    $result['awb'] = $courierAssignment['awb'];
                } else {
                    $result['courier_assigned'] = false;
                    $result['courier_error'] = $courierAssignment['error'];
                }
            }

            Log::info('Order fulfillment completed', $result);
            return array_merge($result, ['success' => true]);

        } catch (Exception $e) {
            Log::error('Order fulfillment failed', [
                'order_id' => $orderData['order_id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Auto-assign best courier for order
     */
    public function autoAssignCourier(array $orderData): array
    {
        try {
            $pickupPostcode = $this->extractPostcode($orderData, 'pickup');
            $deliveryPostcode = $this->extractPostcode($orderData, 'delivery');
            $weight = $orderData['weight'] ?? config('shiprocket.default_weight', 0.5);
            $cod = $this->isCodOrder($orderData) ? 1 : 0;

            // Get best courier based on preferences
            if (config('shiprocket.prefer_cheapest', true)) {
                $courier = $this->courierService->getCheapestCourier($pickupPostcode, $deliveryPostcode, $weight, $cod);
            } else {
                $courier = $this->courierService->getFastestCourier($pickupPostcode, $deliveryPostcode, $weight, $cod);
            }

            if (!$courier) {
                throw new Exception('No suitable courier found');
            }

            // Generate AWB
            $shipmentId = $orderData['shipment_id'] ?? null;
            if (!$shipmentId) {
                throw new Exception('Shipment ID required for AWB generation');
            }

            $awbResponse = $this->courierService->generateAwb($shipmentId, $courier['courier_company_id']);
            
            return [
                'success' => true,
                'courier' => $courier,
                'awb' => $awbResponse
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process return with complete workflow
     */
    public function processReturnWorkflow(array $returnRequest): array
    {
        try {
            Log::info('Starting return workflow', ['return_request' => $returnRequest]);

            // Step 1: Check eligibility
            $eligibility = $this->returnService->checkReturnEligibility($returnRequest);
            if (!$eligibility['eligible']) {
                return [
                    'success' => false,
                    'error' => $eligibility['reason']
                ];
            }

            // Step 2: Create return order
            $returnOrder = $this->returnService->createReturn($returnRequest);

            // Step 3: Auto-assign return courier if enabled
            $result = [
                'success' => true,
                'return_order' => $returnOrder,
                'eligibility' => $eligibility
            ];

            if (config('shiprocket.auto_assign_courier', false) && isset($returnOrder['shipment_id'])) {
                $courierAssignment = $this->autoAssignReturnCourier($returnRequest, $returnOrder['shipment_id']);
                $result['courier_assignment'] = $courierAssignment;
            }

            Log::info('Return workflow completed', $result);
            return $result;

        } catch (Exception $e) {
            Log::error('Return workflow failed', [
                'return_request' => $returnRequest,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Auto-assign courier for return
     */
    protected function autoAssignReturnCourier(array $returnRequest, int $shipmentId): array
    {
        try {
            $pickupPostcode = $returnRequest['pickup_pincode'] ?? null;
            $deliveryPostcode = $returnRequest['shipping_pincode'] ?? null;
            $weight = $returnRequest['weight'] ?? config('shiprocket.default_weight', 0.5);

            if (!$pickupPostcode || !$deliveryPostcode) {
                throw new Exception('Pickup and delivery postcodes required');
            }

            $courier = $this->courierService->getCheapestCourier($pickupPostcode, $deliveryPostcode, $weight, 0);
            if (!$courier) {
                throw new Exception('No suitable return courier found');
            }

            $awbResponse = $this->returnService->generateReturnAwb($shipmentId, $courier['courier_company_id']);
            
            return [
                'success' => true,
                'courier' => $courier,
                'awb' => $awbResponse
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get comprehensive dashboard data
     */
    public function getDashboardData(): array
    {
        try {
            return [
                'orders' => [
                    'recent' => $this->orderService->getOrders(1, 10),
                    'total_count' => $this->getTotalOrderCount(),
                ],
                'shipments' => [
                    'recent' => $this->shipmentService->getShipments(1, 10),
                    'delivered' => $this->shipmentService->getDeliveredShipments(1, 5),
                    'pending' => $this->shipmentService->getPendingShipments(1, 5),
                    'delayed' => $this->shipmentService->getDelayedShipments(),
                ],
                'returns' => [
                    'recent' => $this->returnService->getReturns(1, 10),
                ],
                'couriers' => [
                    'available' => $this->courierService->getCouriers(),
                ],
            ];

        } catch (Exception $e) {
            Log::error('Dashboard data fetch failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Health check for all services
     */
    public function healthCheck(): array
    {
        return [
            'order_service' => $this->orderService->healthCheck(),
            'courier_service' => $this->courierService->healthCheck(),
            'shipment_service' => $this->shipmentService->healthCheck(),
            'return_service' => $this->returnService->healthCheck(),
            'overall' => $this->orderService->healthCheck(), // Use base service for overall check
        ];
    }

    /**
     * Extract postcode from order data
     */
    protected function extractPostcode(array $orderData, string $type): string
    {
        if ($type === 'pickup') {
            return $orderData['pickup_pincode'] ?? $orderData['billing_pincode'] ?? '';
        } else {
            return $orderData['shipping_pincode'] ?? $orderData['billing_pincode'] ?? '';
        }
    }

    /**
     * Check if order is COD
     */
    protected function isCodOrder(array $orderData): bool
    {
        $paymentMethod = strtolower($orderData['payment_method'] ?? '');
        return in_array($paymentMethod, ['cod', 'cash on delivery']);
    }

    /**
     * Get total order count (mock implementation)
     */
    protected function getTotalOrderCount(): int
    {
        // This would typically be implemented based on your database
        try {
            $orders = $this->orderService->getOrders(1, 1);
            return $orders['meta']['pagination']['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}