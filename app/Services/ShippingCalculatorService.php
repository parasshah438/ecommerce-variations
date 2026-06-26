<?php

namespace App\Services;

use App\Models\ShippingZone;
use App\Models\ShippingRate;
use Illuminate\Support\Facades\Log;

class ShippingCalculatorService
{
    /**
     * Default clothing weights by category (in grams)
     */
    const DEFAULT_WEIGHTS = [
        'tshirt' => 200,
        'shirt' => 300,
        'jeans' => 600,
        'dress' => 400,
        'jacket' => 800,
        'shoes' => 500,
        'cap' => 100,
        'socks' => 50,
        'default' => 200,
    ];

    /**
     * Calculate shipping cost for given weight and pincode
     */
    public function calculateShipping($totalWeight, $pincode = null, $subtotal = 0, array $dimensions = [], ?int $declaredValue = null)
    {
        // Free shipping threshold check first
        if ($subtotal >= config('shop.free_shipping_threshold', 999)) {
            return [
                'cost' => 0,
                'zone' => 'Free Shipping',
                'weight_slab' => 'N/A',
                'is_free' => true,
                'source' => 'local_db',
                'message' => 'Free shipping on orders above ₹999'
            ];
        }

        // Try real-time ShipRocket rate first
        $realTimeRate = $this->getShiprocketRealtimeRate($totalWeight, $pincode, $dimensions, $declaredValue ?? (int) $subtotal);
        if ($realTimeRate) {
            return $realTimeRate;
        }

        // Find shipping zone by pincode
        $zone = $this->getShippingZone($pincode);
        
        if (!$zone) {
            // Fallback to default shipping
            return $this->getDefaultShipping($totalWeight);
        }

        // Get shipping rate for the weight
        $shippingRate = $zone->getShippingRate($totalWeight);
        
        if (!$shippingRate) {
            return $this->getDefaultShipping($totalWeight);
        }

        $cost = $shippingRate->calculateCost($totalWeight);

        return [
            'cost' => $cost,
            'zone' => $zone->name,
            'weight_slab' => $this->getWeightSlabDescription($shippingRate, $totalWeight),
            'is_free' => false,
            'source' => 'local_db',
            'message' => "Shipping to {$zone->name}: ₹{$cost}"
        ];
    }

    /**
     * Calculate total cart weight from items
     */
    public function calculateCartWeight($cartItems)
    {
        $totalWeight = 0;

        foreach ($cartItems as $item) {
            $product = $item->productVariation->product ?? null;
            $productWeight = $product ? $product->getFinalWeight() : self::DEFAULT_WEIGHTS['default'];
            $itemWeight = $productWeight * $item->quantity;
            $totalWeight += $itemWeight;
        }

        return $totalWeight;
    }

    /**
     * Calculate cart package dimensions from items.
     * length and breadth are max item dimensions, height is stacked by quantity.
     */
    public function calculateCartDimensions($cartItems): array
    {
        $maxLength = 0;
        $maxBreadth = 0;
        $totalHeight = 0;

        foreach ($cartItems as $item) {
            $variation = $item->productVariation ?? null;
            $product = $variation?->product;

            $length = (float) ($variation->length ?? $product->length ?? config('shiprocket.default_dimensions.length', 10));
            $breadth = (float) ($variation->width ?? $product->width ?? config('shiprocket.default_dimensions.breadth', 10));
            $height = (float) ($variation->height ?? $product->height ?? config('shiprocket.default_dimensions.height', 10));

            $maxLength = max($maxLength, $length);
            $maxBreadth = max($maxBreadth, $breadth);
            $totalHeight += $height * max(1, (int) $item->quantity);
        }

        return [
            'length' => (int) max(1, round($maxLength ?: config('shiprocket.default_dimensions.length', 10))),
            'breadth' => (int) max(1, round($maxBreadth ?: config('shiprocket.default_dimensions.breadth', 10))),
            'height' => (int) max(1, round($totalHeight ?: config('shiprocket.default_dimensions.height', 10))),
        ];
    }

    /**
     * Get shipping zone by pincode
     */
    public function getShippingZone($pincode)
    {
        if (!$pincode) {
            return $this->getDefaultZone();
        }

        $zone = ShippingZone::findByPincode($pincode);
        
        return $zone ?: $this->getDefaultZone();
    }

    /**
     * Get default shipping zone
     */
    protected function getDefaultZone()
    {
        return ShippingZone::where('name', 'Default')->where('active', true)->first();
    }

    /**
     * Fallback shipping calculation
     */
    protected function getDefaultShipping($weight)
    {
        $baseCost = 50;
        
        if ($weight > 1000) {
            $additionalKgs = ceil(($weight - 1000) / 1000);
            $baseCost += ($additionalKgs * 25);
        }

        return [
            'cost' => $baseCost,
            'zone' => 'Standard',
            'weight_slab' => $this->getDefaultWeightSlab($weight),
            'is_free' => false,
            'source' => 'default_fallback',
            'message' => "Standard shipping: ₹{$baseCost}"
        ];
    }

    /**
     * Fetch real-time shipping charge from ShipRocket courier serviceability API.
     * Returns null if API is unavailable or config is incomplete, so DB fallback can apply.
     */
    protected function getShiprocketRealtimeRate($totalWeight, $pincode = null, array $dimensions = [], ?int $declaredValue = null): ?array
    {
        if (empty($pincode) || !app('shiprocket.enabled')) {
            return null;
        }

        $pickupPostcode = config('shiprocket.pickup_postcode');
        if (empty($pickupPostcode)) {
            return null;
        }

        try {
            $weightKg = max(((float) $totalWeight) / 1000, 0.5);
            $length = (int) ($dimensions['length'] ?? config('shiprocket.default_dimensions.length', 10));
            $breadth = (int) ($dimensions['breadth'] ?? config('shiprocket.default_dimensions.breadth', 10));
            $height = (int) ($dimensions['height'] ?? config('shiprocket.default_dimensions.height', 10));

            $courierService = app(ShiprocketCourierService::class);
            $serviceability = $courierService->checkServiceabilityForLocation(
                (string) $pickupPostcode,
                (string) $pincode,
                $weightKg,
                0,
                0,
                $length,
                $breadth,
                $height,
                $declaredValue
            );

            $couriers = $serviceability['data']['available_courier_companies'] ?? [];
            if (empty($couriers)) {
                return null;
            }

            usort($couriers, function ($a, $b) {
                return ((float) ($a['rate'] ?? 0)) <=> ((float) ($b['rate'] ?? 0));
            });

            $bestCourier = $couriers[0] ?? null;
            if (!$bestCourier) {
                return null;
            }

            $cost = (float) ($bestCourier['rate'] ?? 0);

            return [
                'cost' => $cost,
                'zone' => 'ShipRocket Real-Time',
                'weight_slab' => number_format($weightKg, 2) . 'kg',
                'is_free' => false,
                'source' => 'shiprocket_realtime',
                'courier_name' => $bestCourier['courier_name'] ?? null,
                'estimated_delivery_days' => $bestCourier['estimated_delivery_days'] ?? null,
                'message' => 'Real-time rate via ShipRocket' .
                    (isset($bestCourier['courier_name']) ? (': ' . $bestCourier['courier_name']) : ''),
            ];
        } catch (\Exception $e) {
            Log::warning('ShipRocket realtime shipping rate failed, using DB fallback', [
                'pincode' => $pincode,
                'pickup_postcode' => $pickupPostcode,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get weight slab description
     */
    protected function getWeightSlabDescription($shippingRate, $weight)
    {
        $minKg = $shippingRate->min_weight / 1000;
        $maxKg = $shippingRate->max_weight ? $shippingRate->max_weight / 1000 : null;
        $weightKg = $weight / 1000;

        if ($maxKg) {
            return "{$minKg}kg - {$maxKg}kg (Your weight: {$weightKg}kg)";
        } else {
            return "{$minKg}kg+ (Your weight: {$weightKg}kg)";
        }
    }

    /**
     * Get default weight slab description
     */
    protected function getDefaultWeightSlab($weight)
    {
        $weightKg = round($weight / 1000, 2);
        
        if ($weight <= 500) return "0-0.5kg (Your weight: {$weightKg}kg)";
        if ($weight <= 1000) return "0.5-1kg (Your weight: {$weightKg}kg)";
        if ($weight <= 2000) return "1-2kg (Your weight: {$weightKg}kg)";
        if ($weight <= 5000) return "2-5kg (Your weight: {$weightKg}kg)";
        return "5kg+ (Your weight: {$weightKg}kg)";
    }

    /**
     * Get shipping options for a cart (standard, express, etc.)
     */
    public function getShippingOptions($totalWeight, $pincode = null, $subtotal = 0, array $dimensions = [], ?int $declaredValue = null)
    {
        $standardShipping = $this->calculateShipping($totalWeight, $pincode, $subtotal, $dimensions, $declaredValue);
        
        $options = [
            'standard' => [
                'name' => 'Standard Delivery',
                'cost' => $standardShipping['cost'],
                'days' => '3-5 business days',
                'description' => $standardShipping['message']
            ]
        ];

        // Add express option if weight is reasonable
        if ($totalWeight <= 5000) {
            $expressCost = $standardShipping['is_free'] ? 99 : $standardShipping['cost'] + 50;
            $options['express'] = [
                'name' => 'Express Delivery',
                'cost' => $expressCost,
                'days' => '1-2 business days',
                'description' => "Express delivery: ₹{$expressCost}"
            ];
        }

        return $options;
    }

    /**
     * Get weight-based suggestions for customer
     */
    public function getWeightOptimizationSuggestions($cartItems, $subtotal)
    {
        $suggestions = [];
        $currentWeight = $this->calculateCartWeight($cartItems);
        $currentShipping = $this->calculateShipping($currentWeight, null, $subtotal);

        // Suggest free shipping if close
        $freeShippingThreshold = config('shop.free_shipping_threshold', 999);
        $remaining = $freeShippingThreshold - $subtotal;
        
        if ($remaining > 0 && $remaining <= 300) {
            $suggestions[] = [
                'type' => 'free_shipping',
                'message' => "Add ₹{$remaining} more for FREE shipping!",
                'action' => 'Add lightweight items to cart'
            ];
        }

        // Weight optimization suggestions
        if ($currentWeight > 5000) {
            $suggestions[] = [
                'type' => 'weight_optimization',
                'message' => 'Your cart is heavy. Consider splitting into multiple orders for better shipping rates.',
                'action' => 'Split order'
            ];
        }

        return $suggestions;
    }
}