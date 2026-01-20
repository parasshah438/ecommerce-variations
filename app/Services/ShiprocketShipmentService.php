<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Shiprocket Shipment Service
 * Handles shipment-related operations
 */
class ShiprocketShipmentService extends ShiprocketService
{
    /**
     * Get all shipments with pagination
     */
    public function getShipments(int $page = 1, int $perPage = 10): array
    {
        return $this->makeRequest('GET', "/shipments?page={$page}&per_page={$perPage}");
    }

    /**
     * Get specific shipment details
     */
    public function getShipmentDetails(int $shipmentId): array
    {
        return $this->makeRequest('GET', "/shipments/{$shipmentId}");
    }

    /**
     * Cancel shipments by AWB codes
     */
    public function cancelShipments(array $awbCodes): array
    {
        return $this->makeRequest('POST', '/orders/cancel/shipment/awbs', [
            'awbs' => $awbCodes,
        ]);
    }

    /**
     * Generate manifest for shipments
     */
    public function generateManifest(array $shipmentIds): array
    {
        return $this->makeRequest('POST', '/manifests/generate', [
            'shipment_id' => $shipmentIds,
        ]);
    }

    /**
     * Print manifest for orders
     */
    public function printManifest(array $orderIds): array
    {
        return $this->makeRequest('POST', '/manifests/print', [
            'order_ids' => $orderIds,
        ]);
    }

    /**
     * Generate shipping label
     */
    public function generateLabel(array $shipmentIds): array
    {
        return $this->makeRequest('POST', '/courier/generate/label', [
            'shipment_id' => $shipmentIds,
        ]);
    }

    /**
     * Generate invoice
     */
    public function generateInvoice(array $orderIds): array
    {
        return $this->makeRequest('POST', '/orders/print/invoice', [
            'ids' => $orderIds,
        ]);
    }

    /**
     * Track shipment by AWB
     */
    public function trackShipment(string $awbCode): array
    {
        return $this->makeRequest('GET', "/courier/track/awb/{$awbCode}");
    }

    /**
     * Get shipment tracking history
     */
    public function getTrackingHistory(string $awbCode): array
    {
        try {
            $tracking = $this->trackShipment($awbCode);
            return $tracking['tracking_data']['track_status'] ?? [];
        } catch (Exception $e) {
            Log::error('Failed to get tracking history', [
                'awb' => $awbCode,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get current shipment status
     */
    public function getCurrentStatus(string $awbCode): ?string
    {
        try {
            $tracking = $this->trackShipment($awbCode);
            $trackStatus = $tracking['tracking_data']['track_status'] ?? [];
            
            if (empty($trackStatus)) {
                return null;
            }

            // Get the latest status
            $latestStatus = end($trackStatus);
            return $latestStatus['status'] ?? null;

        } catch (Exception $e) {
            Log::error('Failed to get current status', [
                'awb' => $awbCode,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check if shipment is delivered
     */
    public function isDelivered(string $awbCode): bool
    {
        $status = $this->getCurrentStatus($awbCode);
        return in_array(strtolower($status ?? ''), ['delivered', 'delivered successfully']);
    }

    /**
     * Check if shipment is in transit
     */
    public function isInTransit(string $awbCode): bool
    {
        $status = $this->getCurrentStatus($awbCode);
        $transitStatuses = [
            'picked up', 'in transit', 'out for delivery', 'shipped', 'dispatched'
        ];
        return in_array(strtolower($status ?? ''), $transitStatuses);
    }

    /**
     * Check if shipment is returned
     */
    public function isReturned(string $awbCode): bool
    {
        $status = $this->getCurrentStatus($awbCode);
        $returnStatuses = [
            'returned', 'rto delivered', 'rto', 'return to origin'
        ];
        return in_array(strtolower($status ?? ''), $returnStatuses);
    }

    /**
     * Get shipments by status
     */
    public function getShipmentsByStatus(string $status, int $page = 1, int $perPage = 10): array
    {
        return $this->makeRequest('GET', "/shipments?status={$status}&page={$page}&per_page={$perPage}");
    }

    /**
     * Get delivered shipments
     */
    public function getDeliveredShipments(int $page = 1, int $perPage = 10): array
    {
        return $this->getShipmentsByStatus('DELIVERED', $page, $perPage);
    }

    /**
     * Get pending shipments
     */
    public function getPendingShipments(int $page = 1, int $perPage = 10): array
    {
        return $this->getShipmentsByStatus('PICKUP_PENDING', $page, $perPage);
    }

    /**
     * Get shipments with delivery delays
     */
    public function getDelayedShipments(): array
    {
        try {
            $shipments = $this->getShipments(1, 100); // Get more shipments for analysis
            $delayed = [];

            if (!isset($shipments['data'])) {
                return $delayed;
            }

            foreach ($shipments['data'] as $shipment) {
                // Check if shipment is delayed based on expected delivery date
                if (isset($shipment['expected_delivery_date']) && 
                    isset($shipment['status']) && 
                    $shipment['status'] !== 'DELIVERED') {
                    
                    $expectedDate = strtotime($shipment['expected_delivery_date']);
                    $currentDate = time();
                    
                    if ($currentDate > $expectedDate) {
                        $shipment['delay_days'] = ceil(($currentDate - $expectedDate) / (24 * 60 * 60));
                        $delayed[] = $shipment;
                    }
                }
            }

            return $delayed;

        } catch (Exception $e) {
            Log::error('Failed to get delayed shipments', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Bulk track multiple shipments
     */
    public function bulkTrack(array $awbCodes): array
    {
        $results = [];
        
        foreach ($awbCodes as $awb) {
            try {
                $results[$awb] = $this->trackShipment($awb);
            } catch (Exception $e) {
                $results[$awb] = [
                    'error' => $e->getMessage(),
                    'success' => false
                ];
                Log::warning('Failed to track shipment in bulk', [
                    'awb' => $awb,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Get shipment performance metrics
     */
    public function getPerformanceMetrics(array $shipmentIds): array
    {
        try {
            $metrics = [
                'total_shipments' => count($shipmentIds),
                'delivered' => 0,
                'in_transit' => 0,
                'returned' => 0,
                'delayed' => 0,
                'average_delivery_days' => 0,
            ];

            $deliveryDays = [];

            foreach ($shipmentIds as $shipmentId) {
                $shipment = $this->getShipmentDetails($shipmentId);
                
                if (isset($shipment['data'])) {
                    $status = strtolower($shipment['data']['status'] ?? '');
                    
                    switch ($status) {
                        case 'delivered':
                            $metrics['delivered']++;
                            // Calculate delivery days if dates are available
                            if (isset($shipment['data']['shipped_date']) && 
                                isset($shipment['data']['delivered_date'])) {
                                $shipped = strtotime($shipment['data']['shipped_date']);
                                $delivered = strtotime($shipment['data']['delivered_date']);
                                if ($shipped && $delivered) {
                                    $deliveryDays[] = ceil(($delivered - $shipped) / (24 * 60 * 60));
                                }
                            }
                            break;
                        case 'in transit':
                        case 'shipped':
                            $metrics['in_transit']++;
                            break;
                        case 'returned':
                        case 'rto':
                            $metrics['returned']++;
                            break;
                    }
                }
            }

            if (!empty($deliveryDays)) {
                $metrics['average_delivery_days'] = round(array_sum($deliveryDays) / count($deliveryDays), 2);
            }

            return $metrics;

        } catch (Exception $e) {
            Log::error('Failed to get performance metrics', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }
}