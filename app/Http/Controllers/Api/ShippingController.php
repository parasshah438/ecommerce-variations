<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShippingCalculatorService;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    protected $shippingCalculator;

    public function __construct(ShippingCalculatorService $shippingCalculator)
    {
        $this->shippingCalculator = $shippingCalculator;
    }

    /**
     * Calculate shipping for given weight and pincode
     */
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric|min:0',
            'pincode' => 'nullable|string|max:6',
            'subtotal' => 'numeric|min:0'
        ]);

        $weight = $request->weight;
        $pincode = $request->pincode;
        $subtotal = $request->subtotal ?? 0;

        $shippingData = $this->shippingCalculator->calculateShipping($weight, $pincode, $subtotal);
        
        return response()->json([
            'success' => true,
            'shipping' => $shippingData,
            'formatted_cost' => $shippingData['cost'] > 0 ? '₹' . number_format($shippingData['cost'], 2) : 'Free',
            'weight_display' => number_format($weight / 1000, 2) . ' kg'
        ]);
    }

    /**
     * Get shipping options for checkout
     */
    public function getShippingOptions(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric|min:0',
            'pincode' => 'nullable|string|max:6',
            'subtotal' => 'numeric|min:0'
        ]);

        $options = $this->shippingCalculator->getShippingOptions(
            $request->weight,
            $request->pincode,
            $request->subtotal ?? 0
        );

        return response()->json([
            'success' => true,
            'options' => $options
        ]);
    }

    /**
     * Get weight optimization suggestions
     */
    public function getOptimizationSuggestions(Request $request)
    {
        $request->validate([
            'cart_items' => 'required|array',
            'subtotal' => 'numeric|min:0'
        ]);

        // This would typically get cart items from the actual cart
        // For now, we'll return general suggestions
        
        $suggestions = $this->shippingCalculator->getWeightOptimizationSuggestions(
            collect($request->cart_items), 
            $request->subtotal ?? 0
        );

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Get default product weights for admin
     */
    public function getDefaultWeights()
    {
        $weights = config('shop.default_weights', []);
        $categories = config('shop.weight_categories', []);

        return response()->json([
            'success' => true,
            'default_weights' => $weights,
            'weight_categories' => $categories
        ]);
    }
}