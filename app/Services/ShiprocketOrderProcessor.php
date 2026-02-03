<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Shiprocket Order Processor
 * Handles the complete flow of creating shipments in Shiprocket
 * when orders are confirmed and paid
 */
class ShiprocketOrderProcessor
{
    protected $shiprocketManager;
    protected $shippingCalculator;

    public function __construct(
        ShiprocketManager $shiprocketManager,
        ShippingCalculatorService $shippingCalculator
    ) {
        $this->shiprocketManager = $shiprocketManager;
        $this->shippingCalculator = $shippingCalculator;
    }

    /**
     * Process confirmed order for shipping
     * This is the main entry point called from OrderService
     */
    public function processConfirmedOrder(Order $order): array
    {
        try {
            Log::info("Starting Shiprocket processing for Order #{$order->id}");

            // Validate order is ready for shipment
            $this->validateOrderForShipment($order);

            // Prepare order data for Shiprocket
            $shiprocketOrderData = $this->prepareShiprocketOrderData($order);

            // Create order in Shiprocket
            $shiprocketResponse = $this->createShiprocketOrder($shiprocketOrderData);

            // Store shipment information locally
            $shipment = $this->createLocalShipment($order, $shiprocketResponse);

            // Get best courier recommendations
            $courierRecommendations = $this->getBestCourierOptions($order, $shiprocketResponse);

            // Update order with shipping information
            $this->updateOrderShippingInfo($order, $shipment, $courierRecommendations);

            Log::info("Successfully processed Order #{$order->id} for Shiprocket shipment", [
                'shiprocket_order_id' => $shiprocketResponse['order_id'] ?? null,
                'shipment_id' => $shipment->id
            ]);

            return [
                'success' => true,
                'shiprocket_order_id' => $shiprocketResponse['order_id'] ?? null,
                'shipment_id' => $shipment->id,
                'courier_recommendations' => $courierRecommendations,
                'message' => 'Order successfully processed for shipping'
            ];

        } catch (Exception $e) {
            Log::error("Failed to process Order #{$order->id} for Shiprocket", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to process order for shipping'
            ];
        }
    }

    /**
     * Validate order meets shipping requirements
     */
    protected function validateOrderForShipment(Order $order): void
    {
        if ($order->status !== Order::STATUS_CONFIRMED) {
            throw new Exception("Order must be confirmed before shipping");
        }

        if ($order->payment_status !== Order::PAYMENT_PAID) {
            throw new Exception("Order payment must be completed before shipping");
        }

        if (!$order->address) {
            throw new Exception("Order must have a shipping address");
        }

        if ($order->items->isEmpty()) {
            throw new Exception("Order must have items");
        }

        // Check if order is already shipped
        if ($order->shipments()->where('status', 'created')->exists()) {
            throw new Exception("Order already has active shipments");
        }
    }

    /**
     * Prepare order data in Shiprocket format
     */
    protected function prepareShiprocketOrderData(Order $order): array
    {
        $address = $order->address;
        $user = $order->user;
        
        // Calculate total weight and dimensions
        $totalWeight = $this->calculateOrderWeight($order);
        $dimensions = $this->calculateOrderDimensions($order);

        // Prepare order items
        $orderItems = [];
        foreach ($order->items as $item) {
            $product = $item->productVariation->product;
            $orderItems[] = [
                'name' => $product->name,
                'sku' => $item->productVariation->sku,
                'units' => $item->quantity,
                'selling_price' => $item->price,
                'discount' => 0, // Calculate if needed
                'tax' => 0, // GST will be calculated based on selling price
                'hsn' => $product->hsn_code ?? '62', // Default textile HSN
            ];
        }

        return [
            'order_id' => $order->id,
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'pickup_location' => config('shiprocket.default_pickup_location', 'Primary'),
            'channel_id' => config('shiprocket.channel_id', ''), 
            'comment' => "Order #{$order->id} - Ecommerce shipment",
            'billing_customer_name' => $user->name,
            'billing_last_name' => '', // Split if needed
            'billing_address' => $address->address_line_1,
            'billing_address_2' => $address->address_line_2 ?? '',
            'billing_city' => $address->city,
            'billing_pincode' => $address->postal_code,
            'billing_state' => $address->state,
            'billing_country' => $address->country ?? 'India',
            'billing_email' => $user->email,
            'billing_phone' => $user->phone ?? $address->phone ?? '',
            'shipping_is_billing' => true,
            'shipping_customer_name' => $address->name ?? $user->name,
            'shipping_last_name' => '',
            'shipping_address' => $address->address_line_1,
            'shipping_address_2' => $address->address_line_2 ?? '',
            'shipping_city' => $address->city,
            'shipping_pincode' => $address->postal_code,
            'shipping_country' => $address->country ?? 'India',
            'shipping_state' => $address->state,
            'shipping_email' => $user->email,
            'shipping_phone' => $user->phone ?? $address->phone ?? '',
            'order_items' => $orderItems,
            'payment_method' => $this->mapPaymentMethod($order->payment_method),
            'shipping_charges' => $order->shipping_cost ?? 0,
            'giftwrap_charges' => 0,
            'transaction_charges' => 0,
            'total_discount' => $order->coupon_discount ?? 0,
            'sub_total' => $order->subtotal,
            'length' => $dimensions['length'],
            'breadth' => $dimensions['breadth'],
            'height' => $dimensions['height'],
            'weight' => $totalWeight,
        ];
    }

    /**
     * Create order in Shiprocket
     */
    protected function createShiprocketOrder(array $orderData): array
    {
        try {
            return $this->shiprocketManager->orders()->createOrder($orderData);
        } catch (Exception $e) {
            Log::error("Shiprocket order creation failed", [
                'order_id' => $orderData['order_id'],
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to create Shiprocket order: " . $e->getMessage());
        }
    }

    /**
     * Create local shipment record
     */
    protected function createLocalShipment(Order $order, array $shiprocketResponse): Shipment
    {
        return DB::transaction(function () use ($order, $shiprocketResponse) {
            return Shipment::create([
                'order_id' => $order->id,
                'shiprocket_order_id' => $shiprocketResponse['order_id'] ?? null,
                'shiprocket_shipment_id' => $shiprocketResponse['shipment_id'] ?? null,
                'status' => 'created',
                'carrier' => null, // Will be set when courier is assigned
                'tracking_number' => null, // Will be set when shipped
                'estimated_delivery' => null,
                'shiprocket_response' => $shiprocketResponse,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });
    }

    /**
     * Get best courier options for the order
     */
    protected function getBestCourierOptions(Order $order, array $shiprocketResponse): array
    {
        try {
            if (!isset($shiprocketResponse['order_id'])) {
                return [];
            }

            $courierData = [
                'pickup_postcode' => config('shiprocket.pickup_postcode'),
                'delivery_postcode' => $order->address->postal_code,
                'weight' => $this->calculateOrderWeight($order),
                'cod' => $order->payment_method === 'cod' ? 1 : 0
            ];

            return $this->shiprocketManager->couriers()->getRecommendedCouriers($courierData);
        } catch (Exception $e) {
            Log::warning("Failed to get courier recommendations", [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Update order with shipping information
     */
    protected function updateOrderShippingInfo(Order $order, Shipment $shipment, array $courierRecommendations): void
    {
        $order->update([
            'status' => Order::STATUS_PROCESSING, // Move to processing after shipment creation
            'notes' => ($order->notes ? $order->notes . "\n" : '') . 
                      "Shipment created in Shiprocket on " . now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Calculate total weight of order items
     */
    protected function calculateOrderWeight(Order $order): float
    {
        $totalWeight = 0;
        
        foreach ($order->items as $item) {
            $product = $item->productVariation->product;
            $weight = $product->weight ?? $this->getDefaultWeight($product);
            $totalWeight += ($weight * $item->quantity);
        }

        // Convert to kg if in grams, minimum 0.5kg
        $totalWeightKg = $totalWeight > 10 ? $totalWeight / 1000 : $totalWeight;
        return max($totalWeightKg, 0.5);
    }

    /**
     * Get default weight based on product category or type
     */
    protected function getDefaultWeight($product): float
    {
        $category = strtolower($product->category->name ?? 'default');
        return ShippingCalculatorService::DEFAULT_WEIGHTS[$category] ?? 
               ShippingCalculatorService::DEFAULT_WEIGHTS['default'];
    }

    /**
     * Calculate package dimensions
     */
    protected function calculateOrderDimensions(Order $order): array
    {
        // Simple calculation - can be enhanced based on product dimensions
        $itemCount = $order->items->sum('quantity');
        
        return [
            'length' => max(15, min($itemCount * 5, 50)),
            'breadth' => max(10, min($itemCount * 3, 40)), 
            'height' => max(5, min($itemCount * 2, 30))
        ];
    }

    /**
     * Map internal payment method to Shiprocket format
     */
    protected function mapPaymentMethod(string $paymentMethod): string
    {
        $mapping = [
            'cod' => 'COD',
            'razorpay' => 'Prepaid',
            'stripe' => 'Prepaid',
            'bank_transfer' => 'Prepaid',
            'wallet' => 'Prepaid'
        ];

        return $mapping[$paymentMethod] ?? 'Prepaid';
    }

    /**
     * Process automatic courier assignment (optional)
     * This can be called after order creation to automatically assign best courier
     */
    public function assignBestCourier(Order $order): array
    {
        try {
            $shipment = $order->shipments()->where('status', 'created')->first();
            
            if (!$shipment || !$shipment->shiprocket_order_id) {
                throw new Exception("No active Shiprocket shipment found");
            }

            // Get courier recommendations
            $courierData = [
                'pickup_postcode' => config('shiprocket.pickup_postcode'),
                'delivery_postcode' => $order->address->postal_code,
                'weight' => $this->calculateOrderWeight($order),
                'cod' => $order->payment_method === 'cod' ? 1 : 0
            ];

            $couriers = $this->shiprocketManager->couriers()->getRecommendedCouriers($courierData);
            
            if (empty($couriers) || !isset($couriers[0])) {
                throw new Exception("No suitable couriers found");
            }

            // Assign best courier (first in recommendations)
            $bestCourier = $couriers[0];
            
            $assignmentResult = $this->shiprocketManager->couriers()->assignCourier([
                'shipment_id' => $shipment->shiprocket_shipment_id,
                'courier_id' => $bestCourier['id']
            ]);

            // Update local shipment
            $shipment->update([
                'status' => 'courier_assigned',
                'carrier' => $bestCourier['courier_name'],
                'estimated_delivery' => now()->addDays($bestCourier['etd'] ?? 7)
            ]);

            return [
                'success' => true,
                'courier' => $bestCourier,
                'assignment_result' => $assignmentResult
            ];

        } catch (Exception $e) {
            Log::error("Failed to assign courier for Order #{$order->id}", [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}