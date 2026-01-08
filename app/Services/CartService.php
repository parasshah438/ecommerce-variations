<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariation;
use App\Services\ShippingCalculatorService;
use Illuminate\Support\Str;

class CartService
{
    protected $shippingCalculator;

    public function __construct(ShippingCalculatorService $shippingCalculator)
    {
        $this->shippingCalculator = $shippingCalculator;
    }
    public function getOrCreateCart(?\Illuminate\Contracts\Auth\Authenticatable $user, ?string $uuid)
    {
        if ($user) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        if ($uuid) {
            $cart = Cart::where('uuid', $uuid)->first();
            if ($cart) return $cart;
        }

        return Cart::create(['uuid' => Str::uuid()->toString()]);
    }

    public function addItem(Cart $cart, int $variationId, int $qty = 1)
    {
        $variation = ProductVariation::findOrFail($variationId);
        $stock = optional($variation->stock)->quantity ?? 0;

        // Get the best sale price for this variation
        $currentPrice = $variation->getBestSalePrice();

        $item = $cart->items()->where('product_variation_id', $variationId)->first();
        if ($item) {
            // Calculate new total quantity
            $newQuantity = $item->quantity + $qty;
            
            // Validate against stock (safety net)
            if ($newQuantity > $stock) {
                throw new \Exception("Cannot add {$qty} items. Total quantity ({$newQuantity}) would exceed available stock ({$stock})");
            }
            
            $item->quantity = $newQuantity;
            $item->price = $currentPrice; // Update to current sale price
            $item->save();
        } else {
            // New item - validate quantity doesn't exceed stock
            if ($qty > $stock) {
                throw new \Exception("Cannot add {$qty} items. Available stock: {$stock}");
            }
            
            $cart->items()->create([
                'product_variation_id' => $variationId,
                'quantity' => $qty,
                'price' => $currentPrice, // Use sale price
            ]);
        }

        return $cart->refresh();
    }

    public function mergeGuestCartToUser(Cart $guestCart, \App\Models\User $user)
    {
        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);
        foreach ($guestCart->items as $item) {
            $existing = $userCart->items()->where('product_variation_id', $item->product_variation_id)->first();
            if ($existing) {
                $existing->quantity += $item->quantity;
                $existing->save();
            } else {
                $userCart->items()->create($item->toArray());
            }
        }

        // delete guest cart
        $guestCart->delete();
        return $userCart->refresh();
    }

    /**
     * Return cart summary: total items and total amount with weight-based shipping
     *
     * @param Cart $cart
     * @param string|null $pincode
     * @return array
     */
    public function cartSummary(Cart $cart, $pincode = null): array
    {
        // Load cart with coupon relationship
        $cart->load('coupon');
        $items = $cart->items()->with(['productVariation.product', 'productVariation.stock'])->get();
        
        // Handle empty cart
        if ($items->isEmpty()) {
            return $this->getEmptyCartSummary();
        }
        
        $subtotal = $items->sum(function ($item) {
            return ($item->price ?? 0) * ($item->quantity ?? 0);
        });
        
        $itemCount = $items->sum('quantity');
        $uniqueItemCount = $items->count();
        
        // Calculate total cart weight
        $totalWeight = $this->shippingCalculator->calculateCartWeight($items);
        
        // Calculate weight-based shipping
        $shippingData = $this->shippingCalculator->calculateShipping($totalWeight, $pincode, $subtotal);
        $shippingCost = $shippingData['cost'];
        
        // Get discount amount from applied coupon
        $discountAmount = $cart->discount_amount ?? 0;
        
        // Get tax configuration
        $taxRate = config('shop.tax.rate', 0.18);
        $taxCalculateOn = config('shop.tax.calculate_on', 'after_discount');
        $taxEnabled = config('shop.tax.enabled', true);
        
        // Calculate tax based on configuration
        if ($taxEnabled) {
            if ($taxCalculateOn === 'before_discount') {
                $taxAmount = $subtotal * $taxRate;
                $total = $subtotal + $shippingCost + $taxAmount - $discountAmount;
            } else {
                $taxableAmount = max($subtotal - $discountAmount, 0);
                $taxAmount = $taxableAmount * $taxRate;
                $total = $subtotal + $shippingCost + $taxAmount - $discountAmount;
            }
        } else {
            $taxAmount = 0;
            $total = $subtotal + $shippingCost - $discountAmount;
        }
        
        // Calculate savings
        $savings = $this->calculateSavings($items);
        
        // Get shipping suggestions
        $shippingSuggestions = $this->shippingCalculator->getWeightOptimizationSuggestions($items, $subtotal);
        
        return [
            'items' => $itemCount,
            'unique_items' => $uniqueItemCount,
            'subtotal' => round($subtotal, 2),
            'total_weight' => round($totalWeight, 2), // Weight in grams
            'total_weight_kg' => round($totalWeight / 1000, 2), // Weight in kg for display
            'shipping_cost' => round($shippingCost, 2),
            'shipping_zone' => $shippingData['zone'] ?? 'Standard',
            'shipping_message' => $shippingData['message'] ?? '',
            'weight_slab' => $shippingData['weight_slab'] ?? '',
            'tax_amount' => round($taxAmount ?? 0, 2),
            'tax_rate' => $taxRate,
            'tax_calculate_on' => $taxCalculateOn,
            'tax_enabled' => $taxEnabled,
            'tax_name' => config('shop.tax.name', 'GST'),
            'discount_amount' => round($discountAmount, 2),
            'total' => round($total, 2),
            'savings' => round($savings, 2),
            'free_shipping_eligible' => $shippingData['is_free'] ?? false,
            'free_shipping_remaining' => max(0, config('shop.free_shipping_threshold', 999) - $subtotal),
            'shipping_suggestions' => $shippingSuggestions,
            'coupon' => $cart->coupon ? [
                'id' => $cart->coupon->id,
                'code' => $cart->coupon->code,
                'discount' => $cart->coupon->discount,
                'type' => $cart->coupon->type
            ] : null,
            'formatted' => [
                'subtotal' => '₹' . number_format($subtotal, 2),
                'total_weight' => number_format($totalWeight / 1000, 2) . ' kg',
                'shipping_cost' => $shippingCost > 0 ? '₹' . number_format($shippingCost, 2) : 'Free',
                'tax_amount' => '₹' . number_format($taxAmount ?? 0, 2),
                'discount_amount' => '₹' . number_format($discountAmount, 2),
                'total' => '₹' . number_format($total, 2),
                'savings' => '₹' . number_format($savings, 2),
            ]
        ];
    }

    /**
     * Get empty cart summary
     */
    protected function getEmptyCartSummary(): array
    {
        return [
            'items' => 0,
            'unique_items' => 0,
            'subtotal' => 0,
            'total_weight' => 0,
            'total_weight_kg' => 0,
            'shipping_cost' => 50,
            'shipping_zone' => 'Standard',
            'shipping_message' => 'Standard shipping: ₹50',
            'weight_slab' => '0kg',
            'tax_amount' => 0,
            'tax_rate' => config('shop.tax.rate', 0.18),
            'tax_calculate_on' => config('shop.tax.calculate_on', 'after_discount'),
            'tax_enabled' => config('shop.tax.enabled', true),
            'tax_name' => config('shop.tax.name', 'GST'),
            'discount_amount' => 0,
            'total' => 0,
            'savings' => 0,
            'free_shipping_eligible' => false,
            'free_shipping_remaining' => config('shop.free_shipping_threshold', 999),
            'shipping_suggestions' => [],
            'coupon' => null,
            'formatted' => [
                'subtotal' => '₹0.00',
                'total_weight' => '0.00 kg',
                'shipping_cost' => '₹50.00',
                'tax_amount' => '₹0.00',
                'discount_amount' => '₹0.00',
                'total' => '₹0.00',
                'savings' => '₹0.00',
            ]
        ];
    }

    /**
     * Calculate savings from sales and MRP differences
     */
    protected function calculateSavings($items): float
    {
        $savings = 0;
        
        foreach ($items as $item) {
            $variation = $item->productVariation ?? null;
            if ($variation) {
                // Sale discount savings
                $originalPrice = $variation->price;
                $currentSalePrice = $variation->getBestSalePrice();
                
                if ($currentSalePrice < $originalPrice) {
                    $savings += ($originalPrice - $currentSalePrice) * $item->quantity;
                }
                
                // MRP savings
                $product = $variation->product ?? null;
                if ($product && $product->mrp && $product->mrp > $originalPrice) {
                    $savings += ($product->mrp - $originalPrice) * $item->quantity;
                }
            }
        }
        
        return $savings;
    }

    /**
     * Get shipping options for checkout
     */
    public function getShippingOptions(Cart $cart, $pincode = null): array
    {
        $items = $cart->items()->with(['productVariation.product'])->get();
        
        if ($items->isEmpty()) {
            return [];
        }
        
        $totalWeight = $this->shippingCalculator->calculateCartWeight($items);
        $subtotal = $items->sum(fn($item) => ($item->price ?? 0) * ($item->quantity ?? 0));
        
        return $this->shippingCalculator->getShippingOptions($totalWeight, $pincode, $subtotal);
    }}
