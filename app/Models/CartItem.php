<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id', 
        'product_variation_id', 
        'quantity', 
        'price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the cart that owns the cart item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product variation for this cart item.
     */
    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    /**
     * Get the product variation for this cart item.
     */
    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    /**
     * Get the product through the variation relationship.
     */
    public function product()
    {
        return $this->productVariation->product ?? null;
    }

    /**
     * Get the total price for this cart item (price × quantity).
     */
    public function getTotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₹' . number_format($this->price, 2);
    }

    /**
     * Get formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '₹' . number_format($this->total, 2);
    }

    /**
     * Check if the item is in stock.
     */
    public function getIsInStockAttribute(): bool
    {
        $stock = $this->productVariation?->stock?->quantity ?? 0;
        return $stock > 0;
    }

    /**
     * Get available stock for this item.
     */
    public function getAvailableStockAttribute(): int
    {
        return $this->productVariation?->stock?->quantity ?? 0;
    }

    /**
     * Check if requested quantity is available in stock.
     */
    public function hasEnoughStock(): bool
    {
        return $this->quantity <= $this->available_stock;
    }

    /**
     * Get the product name through relationship.
     */
    public function getProductNameAttribute(): string
    {
        return $this->productVariation?->product?->name ?? 'Unknown Product';
    }

    /**
     * Get the product image through relationship.
     */
    public function getProductImageAttribute()
    {
        return $this->productVariation?->product?->images->first() ?? null;
    }

    /**
     * Get the product brand through relationship.
     */
    public function getProductBrandAttribute()
    {
        return $this->productVariation?->product?->brand ?? null;
    }

    /**
     * Get the product category through relationship.
     */
    public function getProductCategoryAttribute()
    {
        return $this->productVariation?->product?->category ?? null;
    }

    /**
     * Check if this cart item is valid (has valid product variation and product).
     */
    public function isValid(): bool
    {
        return $this->productVariation && $this->productVariation->product;
    }

    /**
     * Get the product SKU safely.
     */
    public function getProductSkuAttribute(): string
    {
        return $this->productVariation?->sku ?? 'N/A';
    }

    /**
     * Get the product slug safely.
     */
    public function getProductSlugAttribute(): ?string
    {
        return $this->productVariation?->product?->slug;
    }
}