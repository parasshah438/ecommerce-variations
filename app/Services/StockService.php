<?php

namespace App\Services;

use App\Models\Order;
use App\Models\VariationStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Reserve stock when order is confirmed (payment successful)
     */
    public function reserveStockForOrder(Order $order)
    {
        DB::beginTransaction();
        try {
            foreach ($order->items as $item) {
                $stock = VariationStock::where('product_variation_id', $item->product_variation_id)->first();
                
                if (!$stock) {
                    throw new \Exception("Stock record not found for variation ID: {$item->product_variation_id}");
                }

                if ($stock->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for SKU: {$item->productVariation->sku}. Available: {$stock->quantity}, Required: {$item->quantity}");
                }

                // Deduct stock
                $stock->quantity -= $item->quantity;
                $stock->in_stock = $stock->quantity > 0;
                $stock->save();

                Log::info("Stock reserved - Order #{$order->id}, SKU: {$item->productVariation->sku}, Quantity: {$item->quantity}, Remaining: {$stock->quantity}");
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to reserve stock for order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restore stock when order is cancelled or payment fails
     */
    public function restoreStockForOrder(Order $order, string $reason = '')
    {
        DB::beginTransaction();
        try {
            foreach ($order->items as $item) {
                $stock = VariationStock::where('product_variation_id', $item->product_variation_id)->first();
                
                if (!$stock) {
                    Log::warning("Stock record not found for variation ID: {$item->product_variation_id} during restore");
                    continue;
                }

                // Restore stock
                $stock->quantity += $item->quantity;
                $stock->in_stock = true; // Always mark as in stock when adding quantity
                $stock->save();

                Log::info("Stock restored - Order #{$order->id}, SKU: {$item->productVariation->sku}, Quantity: {$item->quantity}, New Total: {$stock->quantity}, Reason: {$reason}");
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to restore stock for order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle stock when order is returned
     */
    public function handleReturnedOrder(Order $order, array $returnedItems = null)
    {
        DB::beginTransaction();
        try {
            $itemsToProcess = $returnedItems ? 
                $order->items->whereIn('id', array_keys($returnedItems)) : 
                $order->items;

            foreach ($itemsToProcess as $item) {
                $returnQuantity = $returnedItems ? $returnedItems[$item->id] : $item->quantity;
                
                $stock = VariationStock::where('product_variation_id', $item->product_variation_id)->first();
                
                if (!$stock) {
                    Log::warning("Stock record not found for variation ID: {$item->product_variation_id} during return");
                    continue;
                }

                // Restore stock for returned items
                $stock->quantity += $returnQuantity;
                $stock->in_stock = true;
                $stock->save();

                Log::info("Stock restored from return - Order #{$order->id}, SKU: {$item->productVariation->sku}, Returned: {$returnQuantity}, New Total: {$stock->quantity}");
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to handle returned stock for order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if order has sufficient stock before processing
     */
    public function validateOrderStock(Order $order)
    {
        foreach ($order->items as $item) {
            $stock = VariationStock::where('product_variation_id', $item->product_variation_id)->first();
            
            if (!$stock || $stock->quantity < $item->quantity) {
                $available = $stock ? $stock->quantity : 0;
                throw new \Exception("Insufficient stock for SKU: {$item->productVariation->sku}. Available: {$available}, Required: {$item->quantity}");
            }
        }
        
        return true;
    }

    /**
     * Get stock status for an order
     */
    public function getOrderStockStatus(Order $order)
    {
        $status = [
            'sufficient' => true,
            'items' => []
        ];

        foreach ($order->items as $item) {
            $stock = VariationStock::where('product_variation_id', $item->product_variation_id)->first();
            $available = $stock ? $stock->quantity : 0;
            
            $itemStatus = [
                'variation_id' => $item->product_variation_id,
                'sku' => $item->productVariation->sku ?? 'N/A',
                'required' => $item->quantity,
                'available' => $available,
                'sufficient' => $available >= $item->quantity
            ];
            
            if (!$itemStatus['sufficient']) {
                $status['sufficient'] = false;
            }
            
            $status['items'][] = $itemStatus;
        }

        return $status;
    }
}
