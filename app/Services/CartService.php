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

        $item = $cart->items()->where('product_variation_id', $variationId)->first();
        if ($item) {
            // Calculate new total quantity
            $newQuantity = $item->quantity + $qty;
            
            // Validate against stock (safety net)
            if ($newQuantity > $stock) {
                throw new \Exception("Cannot add {$qty} items. Total quantity ({$newQuantity}) would exceed available stock ({$stock})");
            }
            
            $item->quantity = $newQuantity;
            $item->price = $variation->price;
            $item->save();
        } else {
            // New item - validate quantity doesn't exceed stock
            if ($qty > $stock) {
                throw new \Exception("Cannot add {$qty} items. Available stock: {$stock}");
            }
            
            $cart->items()->create([
                'product_variation_id' => $variationId,
                'quantity' => $qty,
                'price' => $variation->price,
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
        $items = $cart->items()->with(['productVariation.product', 'productVariation.stock'])->get();
        
        // Handle empty cart
        if ($items->isEmpty()) {
            return [
                'items' => 0,
                'unique_items' => 0,
                'subtotal' => 0,
                'shipping_cost' => 50,
                'tax_amount' => 0,
                'tax_rate' => 0.18,
                'total' => 0,
                'savings' => 0,
                'free_shipping_eligible' => false,
                'free_shipping_remaining' => 500,
                'formatted' => [
                    'subtotal' => '₹0.00',
                    'shipping_cost' => '₹50.00',
                    'tax_amount' => '₹0.00',
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
        
        // Calculate tax (18% GST)
        $taxRate = 0.18;
        $taxAmount = $subtotal * $taxRate;
        
        // Calculate total
        $total = $subtotal + $shippingCost + $taxAmount;
        
        // Calculate savings if any items have MRP - simplified
        $savings = 0;
        foreach ($items as $item) {
            $product = $item->productVariation->product ?? null;
            if ($product && $product->mrp && $product->mrp > $item->price) {
                $savings += ($product->mrp - $item->price) * $item->quantity;
            }
        }
        
        return [
            'items' => $itemCount,
            'unique_items' => $uniqueItemCount,
            'subtotal' => round($subtotal, 2),
            'shipping_cost' => round($shippingCost, 2),
            'tax_amount' => round($taxAmount, 2),
            'tax_rate' => $taxRate,
            'total' => round($total, 2),
            'savings' => round($savings, 2),
            'free_shipping_eligible' => $subtotal >= 500,
            'free_shipping_remaining' => max(0, 500 - $subtotal),
            'formatted' => [
                'subtotal' => '₹' . number_format($subtotal, 2),
                'shipping_cost' => $shippingCost > 0 ? '₹' . number_format($shippingCost, 2) : 'Free',
                'tax_amount' => '₹' . number_format($taxAmount, 2),
                'total' => '₹' . number_format($total, 2),
                'savings' => '₹' . number_format($savings, 2),
            ]
        ];
    }}
