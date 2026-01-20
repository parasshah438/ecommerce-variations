<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Shiprocket Return Service
 * Handles return and exchange operations
 */
class ShiprocketReturnService extends ShiprocketService
{
    /**
     * Create a return order
     */
    public function createReturn(array $returnData): array
    {
        $this->validateReturnData($returnData);
        return $this->makeRequest('POST', '/orders/create/return', $returnData);
    }

    /**
     * Create exchange order
     */
    public function createExchange(array $exchangeData): array
    {
        $this->validateExchangeData($exchangeData);
        return $this->makeRequest('POST', '/orders/create/exchange', $exchangeData);
    }

    /**
     * Update return order
     */
    public function updateReturn(int $orderId, array $updateData): array
    {
        $data = array_merge(['order_id' => (string) $orderId], $updateData);
        return $this->makeRequest('POST', '/orders/edit', $data);
    }

    /**
     * Get all return orders
     */
    public function getReturns(int $page = 1, int $perPage = 10): array
    {
        return $this->makeRequest('GET', "/orders/processing/return?page={$page}&per_page={$perPage}");
    }

    /**
     * Generate AWB for return shipment
     */
    public function generateReturnAwb(int $shipmentId, int $courierId): array
    {
        return $this->makeRequest('POST', '/courier/assign/awb', [
            'shipment_id' => (string) $shipmentId,
            'courier_id' => (string) $courierId,
            'is_return' => true,
        ]);
    }

    /**
     * Get return reasons
     */
    public function getReturnReasons(): array
    {
        // Common return reasons - this might need to be fetched from API if available
        return [
            1 => 'Product damaged',
            2 => 'Wrong product delivered',
            3 => 'Product not as described',
            4 => 'Size/fit issues',
            5 => 'Quality issues',
            6 => 'Late delivery',
            7 => 'Customer changed mind',
            8 => 'Defective product',
            9 => 'Missing parts/accessories',
            10 => 'Other',
        ];
    }

    /**
     * Process return request
     */
    public function processReturn(array $returnRequest): array
    {
        try {
            // Validate return eligibility
            $eligibility = $this->checkReturnEligibility($returnRequest);
            if (!$eligibility['eligible']) {
                throw new Exception($eligibility['reason']);
            }

            // Create return order
            $returnOrder = $this->createReturn($returnRequest);
            
            // Log return creation
            Log::info('Return order created', [
                'return_order_id' => $returnOrder['order_id'] ?? null,
                'original_order' => $returnRequest['order_id'] ?? null
            ]);

            return [
                'success' => true,
                'return_order' => $returnOrder,
                'message' => 'Return order created successfully'
            ];

        } catch (Exception $e) {
            Log::error('Return processing failed', [
                'request' => $returnRequest,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check return eligibility
     */
    public function checkReturnEligibility(array $returnRequest): array
    {
        try {
            // Get original order details
            $originalOrderId = $returnRequest['original_order_id'] ?? null;
            if (!$originalOrderId) {
                return [
                    'eligible' => false,
                    'reason' => 'Original order ID is required'
                ];
            }

            $originalOrder = $this->getOrderDetails($originalOrderId);
            
            if (!isset($originalOrder['data'])) {
                return [
                    'eligible' => false,
                    'reason' => 'Original order not found'
                ];
            }

            $orderData = $originalOrder['data'];

            // Check if order is delivered
            if (($orderData['status'] ?? '') !== 'DELIVERED') {
                return [
                    'eligible' => false,
                    'reason' => 'Order must be delivered before return'
                ];
            }

            // Check return window (typically 7-30 days)
            $returnWindow = config('services.shiprocket.return_window_days', 7);
            $deliveredDate = $orderData['delivered_date'] ?? null;
            
            if ($deliveredDate) {
                $deliveredTimestamp = strtotime($deliveredDate);
                $returnDeadline = $deliveredTimestamp + ($returnWindow * 24 * 60 * 60);
                
                if (time() > $returnDeadline) {
                    return [
                        'eligible' => false,
                        'reason' => "Return window of {$returnWindow} days has expired"
                    ];
                }
            }

            return [
                'eligible' => true,
                'remaining_days' => $this->getRemainingReturnDays($deliveredDate, $returnWindow)
            ];

        } catch (Exception $e) {
            Log::error('Return eligibility check failed', ['error' => $e->getMessage()]);
            return [
                'eligible' => false,
                'reason' => 'Unable to check return eligibility'
            ];
        }
    }

    /**
     * Get remaining return days
     */
    protected function getRemainingReturnDays(?string $deliveredDate, int $returnWindow): int
    {
        if (!$deliveredDate) {
            return $returnWindow;
        }

        $deliveredTimestamp = strtotime($deliveredDate);
        $returnDeadline = $deliveredTimestamp + ($returnWindow * 24 * 60 * 60);
        $remainingSeconds = $returnDeadline - time();
        
        return max(0, ceil($remainingSeconds / (24 * 60 * 60)));
    }

    /**
     * Get return status
     */
    public function getReturnStatus(int $returnOrderId): ?array
    {
        try {
            $returnOrder = $this->getOrderDetails($returnOrderId);
            return $returnOrder['data'] ?? null;
        } catch (Exception $e) {
            Log::error('Failed to get return status', [
                'return_order_id' => $returnOrderId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Calculate return shipping cost
     */
    public function calculateReturnShippingCost(
        string $pickupPostcode,
        string $deliveryPostcode,
        float $weight = 0.5
    ): ?float {
        try {
            $courierService = new ShiprocketCourierService();
            $cheapestCourier = $courierService->getCheapestCourier(
                $pickupPostcode,
                $deliveryPostcode,
                $weight,
                0 // No COD for returns
            );

            return $cheapestCourier['rate'] ?? null;

        } catch (Exception $e) {
            Log::error('Failed to calculate return shipping cost', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Validate return data
     */
    protected function validateReturnData(array $returnData): void
    {
        $requiredFields = [
            'order_id', 'order_date', 'pickup_customer_name', 'pickup_address',
            'pickup_city', 'pickup_state', 'pickup_country', 'pickup_pincode',
            'pickup_email', 'pickup_phone', 'shipping_customer_name',
            'shipping_address', 'shipping_city', 'shipping_state', 'shipping_country',
            'shipping_pincode', 'shipping_email', 'shipping_phone', 'order_items',
            'payment_method', 'sub_total'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($returnData[$field])) {
                throw new Exception("Missing required return field: {$field}");
            }
        }

        if (empty($returnData['order_items']) || !is_array($returnData['order_items'])) {
            throw new Exception('Return order items must be a non-empty array');
        }
    }

    /**
     * Validate exchange data
     */
    protected function validateExchangeData(array $exchangeData): void
    {
        $requiredFields = [
            'order_items', 'buyer_pickup_first_name', 'buyer_pickup_address',
            'buyer_pickup_city', 'buyer_pickup_state', 'buyer_pickup_country',
            'buyer_pickup_pincode', 'buyer_pickup_email', 'buyer_pickup_phone',
            'exchange_order_id', 'return_order_id', 'payment_method', 'sub_total'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($exchangeData[$field])) {
                throw new Exception("Missing required exchange field: {$field}");
            }
        }

        if (empty($exchangeData['order_items']) || !is_array($exchangeData['order_items'])) {
            throw new Exception('Exchange order items must be a non-empty array');
        }
    }

    /**
     * Generate return label
     */
    public function generateReturnLabel(int $returnShipmentId): array
    {
        $shipmentService = new ShiprocketShipmentService();
        return $shipmentService->generateLabel([$returnShipmentId]);
    }

    /**
     * Track return shipment
     */
    public function trackReturn(string $returnAwb): array
    {
        $shipmentService = new ShiprocketShipmentService();
        return $shipmentService->trackShipment($returnAwb);
    }

    /**
     * Bulk process returns
     */
    public function bulkProcessReturns(array $returnRequests): array
    {
        $results = [];
        
        foreach ($returnRequests as $index => $returnRequest) {
            try {
                $results[$index] = $this->processReturn($returnRequest);
            } catch (Exception $e) {
                $results[$index] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                Log::error('Bulk return processing failed', [
                    'index' => $index,
                    'request' => $returnRequest,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }
}