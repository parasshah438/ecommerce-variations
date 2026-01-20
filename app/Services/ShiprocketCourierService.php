<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Shiprocket Courier Service
 * Handles courier-related operations
 */
class ShiprocketCourierService extends ShiprocketService
{
    /**
     * Generate AWB for shipment
     */
    public function generateAwb(int $shipmentId, int $courierId): array
    {
        return $this->makeRequest('POST', '/courier/assign/awb', [
            'shipment_id' => (string) $shipmentId,
            'courier_id' => (string) $courierId,
        ]);
    }

    /**
     * Get list of available couriers
     */
    public function getCouriers(): array
    {
        return $this->makeRequest('GET', '/courier/courierListWithCounts');
    }

    /**
     * Check courier serviceability
     */
    public function checkServiceability(array $params): array
    {
        $queryString = http_build_query($params);
        return $this->makeRequest('GET', "/courier/serviceability/?{$queryString}");
    }

    /**
     * Check serviceability between pickup and delivery locations
     */
    public function checkServiceabilityForLocation(
        string $pickupPostcode,
        string $deliveryPostcode,
        float $weight = 0.5,
        int $cod = 0
    ): array {
        return $this->checkServiceability([
            'pickup_postcode' => $pickupPostcode,
            'delivery_postcode' => $deliveryPostcode,
            'weight' => $weight,
            'cod' => $cod,
        ]);
    }

    /**
     * Get cheapest courier option
     */
    public function getCheapestCourier(
        string $pickupPostcode,
        string $deliveryPostcode,
        float $weight = 0.5,
        int $cod = 0
    ): ?array {
        try {
            $serviceability = $this->checkServiceabilityForLocation(
                $pickupPostcode,
                $deliveryPostcode,
                $weight,
                $cod
            );

            if (!isset($serviceability['data']['available_courier_companies'])) {
                return null;
            }

            $couriers = $serviceability['data']['available_courier_companies'];
            
            // Sort by rate (ascending)
            usort($couriers, function ($a, $b) {
                return $a['rate'] <=> $b['rate'];
            });

            return $couriers[0] ?? null;

        } catch (Exception $e) {
            Log::error('Failed to get cheapest courier', [
                'pickup' => $pickupPostcode,
                'delivery' => $deliveryPostcode,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get fastest courier option
     */
    public function getFastestCourier(
        string $pickupPostcode,
        string $deliveryPostcode,
        float $weight = 0.5,
        int $cod = 0
    ): ?array {
        try {
            $serviceability = $this->checkServiceabilityForLocation(
                $pickupPostcode,
                $deliveryPostcode,
                $weight,
                $cod
            );

            if (!isset($serviceability['data']['available_courier_companies'])) {
                return null;
            }

            $couriers = $serviceability['data']['available_courier_companies'];
            
            // Sort by estimated delivery days (ascending)
            usort($couriers, function ($a, $b) {
                $daysA = (int) ($a['estimated_delivery_days'] ?? 999);
                $daysB = (int) ($b['estimated_delivery_days'] ?? 999);
                return $daysA <=> $daysB;
            });

            return $couriers[0] ?? null;

        } catch (Exception $e) {
            Log::error('Failed to get fastest courier', [
                'pickup' => $pickupPostcode,
                'delivery' => $deliveryPostcode,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Filter couriers by criteria
     */
    public function filterCouriers(array $couriers, array $criteria): array
    {
        return array_filter($couriers, function ($courier) use ($criteria) {
            // Filter by maximum rate
            if (isset($criteria['max_rate']) && $courier['rate'] > $criteria['max_rate']) {
                return false;
            }

            // Filter by maximum delivery days
            if (isset($criteria['max_delivery_days'])) {
                $deliveryDays = (int) ($courier['estimated_delivery_days'] ?? 999);
                if ($deliveryDays > $criteria['max_delivery_days']) {
                    return false;
                }
            }

            // Filter by COD availability
            if (isset($criteria['cod_required']) && $criteria['cod_required'] && !$courier['cod']) {
                return false;
            }

            // Filter by courier name
            if (isset($criteria['courier_name']) && 
                stripos($courier['courier_name'], $criteria['courier_name']) === false) {
                return false;
            }

            // Filter by minimum rating
            if (isset($criteria['min_rating']) && 
                (float) ($courier['rating'] ?? 0) < $criteria['min_rating']) {
                return false;
            }

            return true;
        });
    }

    /**
     * Get recommended courier based on business logic
     */
    public function getRecommendedCourier(
        string $pickupPostcode,
        string $deliveryPostcode,
        float $weight = 0.5,
        int $cod = 0,
        array $preferences = []
    ): ?array {
        try {
            $serviceability = $this->checkServiceabilityForLocation(
                $pickupPostcode,
                $deliveryPostcode,
                $weight,
                $cod
            );

            if (!isset($serviceability['data']['available_courier_companies'])) {
                return null;
            }

            $couriers = $serviceability['data']['available_courier_companies'];
            
            // Apply filters if provided
            if (!empty($preferences['filters'])) {
                $couriers = $this->filterCouriers($couriers, $preferences['filters']);
            }

            if (empty($couriers)) {
                return null;
            }

            // Default scoring: 40% price, 30% speed, 30% rating
            $priceWeight = $preferences['price_weight'] ?? 0.4;
            $speedWeight = $preferences['speed_weight'] ?? 0.3;
            $ratingWeight = $preferences['rating_weight'] ?? 0.3;

            // Normalize and score each courier
            $maxRate = max(array_column($couriers, 'rate'));
            $maxDays = max(array_column($couriers, 'estimated_delivery_days'));
            $maxRating = 5; // Assuming 5-star rating system

            foreach ($couriers as &$courier) {
                $priceScore = $maxRate > 0 ? (1 - ($courier['rate'] / $maxRate)) : 0;
                $speedScore = $maxDays > 0 ? (1 - ($courier['estimated_delivery_days'] / $maxDays)) : 0;
                $ratingScore = $maxRating > 0 ? (($courier['rating'] ?? 0) / $maxRating) : 0;

                $courier['recommendation_score'] = 
                    ($priceScore * $priceWeight) + 
                    ($speedScore * $speedWeight) + 
                    ($ratingScore * $ratingWeight);
            }

            // Sort by recommendation score (descending)
            usort($couriers, function ($a, $b) {
                return $b['recommendation_score'] <=> $a['recommendation_score'];
            });

            return $couriers[0];

        } catch (Exception $e) {
            Log::error('Failed to get recommended courier', [
                'pickup' => $pickupPostcode,
                'delivery' => $deliveryPostcode,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}