<?php

namespace App\Http\Controllers;

use App\Services\ShiprocketManager;
use App\Services\ShiprocketService;
use App\Services\ShiprocketCourierService;
use App\Services\ShiprocketShipmentService;
use App\Services\ShiprocketReturnService;
use App\Helpers\ShiprocketHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class ShiprocketController extends Controller
{
    protected $shiprocket;

    public function __construct(ShiprocketManager $shiprocket)
    {
        $this->shiprocket = $shiprocket;
    }

    /**
     * Create a new order
     */
    public function createOrder(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|string',
                'order_date' => 'required|date',
                'pickup_location' => 'required|string',
                'billing_customer_name' => 'required|string',
                'billing_address' => 'required|string',
                'billing_city' => 'required|string',
                'billing_pincode' => 'required|string',
                'billing_state' => 'required|string',
                'billing_country' => 'required|string',
                'billing_email' => 'required|email',
                'billing_phone' => 'required|string',
                'order_items' => 'required|array',
                'order_items.*.name' => 'required|string',
                'order_items.*.sku' => 'required|string',
                'order_items.*.units' => 'required|integer|min:1',
                'order_items.*.selling_price' => 'required|numeric|min:0',
                'payment_method' => 'required|string',
                'sub_total' => 'required|numeric|min:0',
            ]);

            $result = $this->shiprocket->createOrder($validated);

            return response()->json($result);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order details
     */
    public function getOrder(int $orderId): JsonResponse
    {
        try {
            $order = $this->shiprocket->orders()->getOrderDetails($orderId);
            return response()->json($order);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel orders
     */
    public function cancelOrders(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'required|integer',
            ]);

            $result = $this->shiprocket->orders()->cancelOrders($validated['order_ids']);
            return response()->json($result);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check courier serviceability
     */
    public function checkServiceability(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'pickup_postcode' => 'required|string',
                'delivery_postcode' => 'required|string',
                'weight' => 'numeric|min:0.1',
                'cod' => 'boolean',
            ]);

            $weight = $validated['weight'] ?? 0.5;
            $cod = $validated['cod'] ? 1 : 0;

            $serviceability = $this->shiprocket->couriers()->checkServiceabilityForLocation(
                $validated['pickup_postcode'],
                $validated['delivery_postcode'],
                $weight,
                $cod
            );

            return response()->json($serviceability);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get courier recommendations
     */
    public function getCourierRecommendations(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'pickup_postcode' => 'required|string',
                'delivery_postcode' => 'required|string',
                'weight' => 'numeric|min:0.1',
                'cod' => 'boolean',
                'preferences' => 'array',
            ]);

            $weight = $validated['weight'] ?? 0.5;
            $cod = $validated['cod'] ? 1 : 0;
            $preferences = $validated['preferences'] ?? [];

            $cheapest = $this->shiprocket->couriers()->getCheapestCourier(
                $validated['pickup_postcode'],
                $validated['delivery_postcode'],
                $weight,
                $cod
            );

            $fastest = $this->shiprocket->couriers()->getFastestCourier(
                $validated['pickup_postcode'],
                $validated['delivery_postcode'],
                $weight,
                $cod
            );

            $recommended = $this->shiprocket->couriers()->getRecommendedCourier(
                $validated['pickup_postcode'],
                $validated['delivery_postcode'],
                $weight,
                $cod,
                $preferences
            );

            return response()->json([
                'cheapest' => $cheapest,
                'fastest' => $fastest,
                'recommended' => $recommended,
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate AWB for shipment
     */
    public function generateAwb(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'shipment_id' => 'required|integer',
                'courier_id' => 'required|integer',
            ]);

            $result = $this->shiprocket->couriers()->generateAwb(
                $validated['shipment_id'],
                $validated['courier_id']
            );

            return response()->json($result);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Track shipment
     */
    public function trackShipment(string $awbCode): JsonResponse
    {
        try {
            $tracking = $this->shiprocket->shipments()->trackShipment($awbCode);
            return response()->json($tracking);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get shipment details
     */
    public function getShipment(int $shipmentId): JsonResponse
    {
        try {
            $shipment = $this->shiprocket->shipments()->getShipmentDetails($shipmentId);
            return response()->json($shipment);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate shipping label
     */
    public function generateLabel(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'shipment_ids' => 'required|array',
                'shipment_ids.*' => 'required|integer',
            ]);

            $result = $this->shiprocket->shipments()->generateLabel($validated['shipment_ids']);
            return response()->json($result);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate manifest
     */
    public function generateManifest(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'shipment_ids' => 'required|array',
                'shipment_ids.*' => 'required|integer',
            ]);

            $result = $this->shiprocket->shipments()->generateManifest($validated['shipment_ids']);
            return response()->json($result);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create return order
     */
    public function createReturn(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|string',
                'original_order_id' => 'required|integer',
                'pickup_customer_name' => 'required|string',
                'pickup_address' => 'required|string',
                'pickup_city' => 'required|string',
                'pickup_state' => 'required|string',
                'pickup_pincode' => 'required|string',
                'pickup_email' => 'required|email',
                'pickup_phone' => 'required|string',
                'return_to_name' => 'required|string',
                'return_to_address' => 'required|string',
                'return_to_city' => 'required|string',
                'return_to_state' => 'required|string',
                'return_to_pincode' => 'required|string',
                'return_to_email' => 'required|email',
                'return_to_phone' => 'required|string',
                'items' => 'required|array',
                'sub_total' => 'required|numeric|min:0',
            ]);

            $result = $this->shiprocket->processReturnWorkflow($validated);
            return response()->json($result);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all returns
     */
    public function getReturns(Request $request): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);

            $returns = $this->shiprocket->returns()->getReturns($page, $perPage);
            return response()->json($returns);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check return eligibility
     */
    public function checkReturnEligibility(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'original_order_id' => 'required|integer',
            ]);

            $eligibility = $this->shiprocket->returns()->checkReturnEligibility($validated);
            return response()->json($eligibility);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get dashboard data
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $dashboard = $this->shiprocket->getDashboardData();
            return response()->json($dashboard);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Health check
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $health = $this->shiprocket->healthCheck();
            $status = $health['overall'] ? 200 : 503;
            
            return response()->json($health, $status);
        } catch (Exception $e) {
            return response()->json([
                'overall' => false,
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Bulk track shipments
     */
    public function bulkTrack(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'awb_codes' => 'required|array',
                'awb_codes.*' => 'required|string',
            ]);

            $results = $this->shiprocket->shipments()->bulkTrack($validated['awb_codes']);
            return response()->json($results);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'shipment_ids' => 'required|array',
                'shipment_ids.*' => 'required|integer',
            ]);

            $metrics = $this->shiprocket->shipments()->getPerformanceMetrics($validated['shipment_ids']);
            return response()->json($metrics);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}