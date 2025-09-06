<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'uuid'
    ];

    /**
     * Get all items in the cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get total items count in cart.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get cart subtotal (sum of all item totals).
     */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Get formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '₹' . number_format($this->subtotal, 2);
    }

    /**
     * Check if cart is empty.
     */
    public function getIsEmptyAttribute(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Get unique products count in cart.
     */
    public function getUniqueProductsCountAttribute(): int
    {
        return $this->items->count();
    }

    /**
     * Calculate shipping cost.
     */
    public function getShippingCostAttribute(): float
    {
        return $this->subtotal >= 500 ? 0 : 50;
    }

    /**
     * Calculate tax amount.
     */
    public function getTaxAmountAttribute(): float
    {
        return $this->subtotal * 0.18; // 18% GST
    }

    /**
     * Calculate total amount including shipping and tax.
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->subtotal + $this->shipping_cost + $this->tax_amount;
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    /**
     * Add item to cart or update quantity if exists.
     */
    public function addItem(int $productVariationId, int $quantity = 1, float $price = null): CartItem
    {
        $existingItem = $this->items()
            ->where('product_variation_id', $productVariationId)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            return $existingItem->fresh();
        }

        // Get price from variation if not provided
        if ($price === null) {
            $variation = ProductVariation::find($productVariationId);
            $price = $variation ? $variation->price : 0;
        }

        return $this->items()->create([
            'product_variation_id' => $productVariationId,
            'quantity' => $quantity,
            'price' => $price,
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(int $cartItemId): bool
    {
        return $this->items()->where('id', $cartItemId)->delete() > 0;
    }

    /**
     * Clear all items from cart.
     */
    public function clearItems(): int
    {
        return $this->items()->delete();
    }

    /**
     * Check if cart has any out of stock items.
     */
    public function hasOutOfStockItems(): bool
    {
        return $this->items->some(function ($item) {
            return !$item->is_in_stock;
        });
    }

    /**
     * Get out of stock items.
     */
    public function getOutOfStockItems()
    {
        return $this->items->filter(function ($item) {
            return !$item->is_in_stock;
        });
    }
}