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

    public function getShiprocketManager(): ShiprocketManager
    {
        return $this->shiprocketManager;
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

        // Prepare order items — only active (non-cancelled) items
        $orderItems = [];
        foreach ($order->items->where('status', \App\Models\OrderItem::STATUS_ACTIVE) as $item) {
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
            'billing_address' => $address->address_line,
            'billing_address_2' => '',
            'billing_city' => $address->city,
            'billing_pincode' => $address->zip,
            'billing_state' => $address->state,
            'billing_country' => $address->country ?? 'India',
            'billing_email' => $user->email,
            'billing_phone' => $user->phone ?? $address->phone ?? '',
            'billing_isd_code' => '91',
            'billing_alternate_phone' => $address->alternate_phone ?? '',
            'shipping_is_billing' => true,
            'shipping_customer_name' => $address->name ?? $user->name,
            'shipping_last_name' => '',
            'shipping_address' => $address->address_line,
            'shipping_address_2' => '',
            'shipping_city' => $address->city,
            'shipping_pincode' => $address->zip,
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
            'invoice_number' => (string) $order->id,
            'order_type' => 'ESSENTIALS',
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

            $dimensions = $this->calculateOrderDimensions($order);

            return $this->shiprocketManager->couriers()->getRecommendedCourier(
                config('shiprocket.pickup_postcode'),
                $order->address->zip,
                $this->calculateOrderWeight($order),
                $order->payment_method === 'cod' ? 1 : 0,
                [],                                      // preferences
                (int) $dimensions['length'],
                (int) $dimensions['breadth'],
                (int) $dimensions['height'],
                (int) $order->subtotal                   // declared_value = order subtotal
            ) ?? [];
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
        
        foreach ($order->items->where('status', \App\Models\OrderItem::STATUS_ACTIVE) as $item) {
            $product = $item->productVariation->product;
            $weight  = $product->weight ?? $this->getDefaultWeight($product);
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
     * Calculate package dimensions from actual product/variation data.
     *
     * Priority: variation dimensions → product dimensions → default fallback.
     * Box sizing: max length, max breadth (width) across all items,
     * height = sum of (item height × qty) for stacked items.
     */
    protected function calculateOrderDimensions(Order $order): array
    {
        $maxLength  = 0;
        $maxBreadth = 0;
        $totalHeight = 0;

        foreach ($order->items->where('status', \App\Models\OrderItem::STATUS_ACTIVE) as $item) {
            $variation = $item->productVariation;
            $product   = $variation->product;

            // Resolve each dimension: variation first, then product, then default
            $length  = (float) ($variation->length  ?: $product->length  ?: 15);
            $breadth = (float) ($variation->width   ?: $product->width   ?: 10);
            $height  = (float) ($variation->height  ?: $product->height  ?: 5);

            $maxLength   = max($maxLength, $length);
            $maxBreadth  = max($maxBreadth, $breadth);
            $totalHeight += $height * $item->quantity;
        }

        // Ensure minimums in case all dimensions were zero/null
        return [
            'length'  => max($maxLength, 15),
            'breadth' => max($maxBreadth, 10),
            'height'  => max($totalHeight, 5),
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

            // Get recommended courier — pass full dimensions and declared value
            $dimensions  = $this->calculateOrderDimensions($order);
            $bestCourier = $this->shiprocketManager->couriers()->getRecommendedCourier(
                config('shiprocket.pickup_postcode'),
                $order->address->zip,
                $this->calculateOrderWeight($order),
                $order->payment_method === 'cod' ? 1 : 0,
                [],                                      // preferences
                (int) $dimensions['length'],
                (int) $dimensions['breadth'],
                (int) $dimensions['height'],
                (int) $order->subtotal                   // declared_value = order subtotal
            );
            
            if (!$bestCourier || !isset($bestCourier['courier_company_id'])) {
                throw new Exception("No suitable couriers found");
            }

            // Generate AWB — this assigns the courier and creates the tracking number
            $assignmentResult = $this->shiprocketManager->couriers()->generateAwb(
                (int) $shipment->shiprocket_shipment_id,
                (int) $bestCourier['courier_company_id']
            );

            // Update local shipment with AWB and carrier
            $shipment->update([
                'status' => 'courier_assigned',
                'carrier' => $bestCourier['courier_name'] ?? null,
                'awb_code' => $assignmentResult['awb_code'] ?? null,
                'tracking_number' => $assignmentResult['awb_code'] ?? null,
                'estimated_delivery' => isset($bestCourier['estimated_delivery_days'])
                    ? now()->addDays((int) $bestCourier['estimated_delivery_days'])
                    : now()->addDays(7)
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