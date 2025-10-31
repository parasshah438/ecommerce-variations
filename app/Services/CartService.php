<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariation;
use Illuminate\Support\Str;

class CartService
{
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
     * Return cart summary: total items and total amount
     *
     * @param Cart $cart
     * @return array
     */
    public function cartSummary(Cart $cart): array
    {
        // Load cart with coupon relationship
        $cart->load('coupon');
        $items = $cart->items()->with(['productVariation.product', 'productVariation.stock'])->get();
        
        // Handle empty cart
        if ($items->isEmpty()) {
            return [
                'items' => 0,
                'unique_items' => 0,
                'subtotal' => 0,
                'shipping_cost' => 50,
                'tax_amount' => 0,
                'tax_rate' => config('shop.tax.rate', 0.18),
                'tax_calculate_on' => config('shop.tax.calculate_on', 'after_discount'),
                'tax_enabled' => config('shop.tax.enabled', true),
                'tax_name' => config('shop.tax.name', 'GST'),
                'discount_amount' => 0,
                'total' => 0,
                'savings' => 0,
                'free_shipping_eligible' => false,
                'free_shipping_remaining' => 500,
                'coupon' => null,
                'formatted' => [
                    'subtotal' => '₹0.00',
                    'shipping_cost' => '₹50.00',
                    'tax_amount' => '₹0.00',
                    'discount_amount' => '₹0.00',
                    'total' => '₹0.00',
                    'savings' => '₹0.00',
                ]
            ];
        }
        
        $subtotal = $items->sum(function ($item) {
            return ($item->price ?? 0) * ($item->quantity ?? 0);
        });
        
        $itemCount = $items->sum('quantity');
        $uniqueItemCount = $items->count();
        
        // Calculate shipping
        $shippingCost = $subtotal >= 500 ? 0 : 50;
        
        // Get discount amount from applied coupon
        $discountAmount = $cart->discount_amount ?? 0;
        
        // Get tax configuration
        $taxRate = config('shop.tax.rate', 0.18);
        $taxCalculateOn = config('shop.tax.calculate_on', 'after_discount');
        $taxEnabled = config('shop.tax.enabled', true);
        
        // Calculate tax based on configuration
        if ($taxEnabled) {
            if ($taxCalculateOn === 'before_discount') {
                // Option 1: Calculate tax BEFORE discount
                $taxAmount = $subtotal * $taxRate;
                $total = $subtotal + $shippingCost + $taxAmount - $discountAmount;
            } else {
                // Option 2: Calculate tax AFTER discount (default)
                $taxableAmount = max($subtotal - $discountAmount, 0); // Ensure non-negative
                $taxAmount = $taxableAmount * $taxRate;
                $total = $subtotal + $shippingCost + $taxAmount - $discountAmount;
            }
        } else {
            // No tax
            $taxAmount = 0;
            $total = $subtotal + $shippingCost - $discountAmount;
        }
        
        // Calculate savings including sale discounts
        $savings = 0;
        foreach ($items as $item) {
            $variation = $item->productVariation ?? null;
            if ($variation) {
                // Calculate sale savings (original price vs sale price)
                $originalPrice = $variation->price;
                $currentSalePrice = $variation->getBestSalePrice();
                
                if ($currentSalePrice < $originalPrice) {
                    // Sale discount savings
                    $savings += ($originalPrice - $currentSalePrice) * $item->quantity;
                }
                
                // Additional MRP savings if applicable
                $product = $variation->product ?? null;
                if ($product && $product->mrp && $product->mrp > $originalPrice) {
                    $savings += ($product->mrp - $originalPrice) * $item->quantity;
                }
            }
        }
        
        return [
            'items' => $itemCount,
            'unique_items' => $uniqueItemCount,
            'subtotal' => round($subtotal, 2),
            'shipping_cost' => round($shippingCost, 2),
            'tax_amount' => round($taxAmount, 2),
            'tax_rate' => $taxRate,
            'tax_calculate_on' => $taxCalculateOn,
            'tax_enabled' => $taxEnabled,
            'tax_name' => config('shop.tax.name', 'GST'),
            'discount_amount' => round($discountAmount, 2),
            'total' => round($total, 2),
            'savings' => round($savings, 2),
            'free_shipping_eligible' => $subtotal >= 500,
            'free_shipping_remaining' => max(0, 500 - $subtotal),
            'coupon' => $cart->coupon ? [
                'id' => $cart->coupon->id,
                'code' => $cart->coupon->code,
                'discount' => $cart->coupon->discount,
                'type' => $cart->coupon->type
            ] : null,
            'formatted' => [
                'subtotal' => '₹' . number_format($subtotal, 2),
                'shipping_cost' => $shippingCost > 0 ? '₹' . number_format($shippingCost, 2) : 'Free',
                'tax_amount' => '₹' . number_format($taxAmount, 2),
                'discount_amount' => '₹' . number_format($discountAmount, 2),
                'total' => '₹' . number_format($total, 2),
                'savings' => '₹' . number_format($savings, 2),
            ]
        ];
    }}
