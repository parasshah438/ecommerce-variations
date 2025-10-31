<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 
        'sku', 
        'price', 
        'min_qty', 
        'attribute_value_ids'
    ];

    protected $casts = [
        'attribute_value_ids' => 'array',
        'price' => 'decimal:2',
        'min_qty' => 'integer',
    ];

    /**
     * Mutator to ensure attribute_value_ids is always an array
     */
    public function setAttributeValueIdsAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['attribute_value_ids'] = is_array($decoded) ? json_encode($decoded) : json_encode([]);
        } elseif (is_array($value)) {
            $this->attributes['attribute_value_ids'] = json_encode($value);
        } else {
            $this->attributes['attribute_value_ids'] = json_encode([]);
        }
    }

    /**
     * Accessor to ensure attribute_value_ids is always an array
     */
    public function getAttributeValueIdsAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    /**
     * Get the product that owns the variation.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the stock information for this variation.
     */
    public function stock(): HasOne
    {
        return $this->hasOne(VariationStock::class, 'product_variation_id');
    }

    /**
     * Get all cart items for this variation.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get all order items for this variation.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all saved items for this variation.
     */
    public function saveForLaterItems(): HasMany
    {
        return $this->hasMany(SaveForLater::class);
    }

    /**
     * Get all images for this variation.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductVariationImage::class);
    }

    /**
     * Get the attribute values for this variation.
     */
    public function attributeValues()
    {
        if (!$this->attribute_value_ids) {
            return collect();
        }
        
        return AttributeValue::whereIn('id', $this->attribute_value_ids)
            ->with('attribute')
            ->get();
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₹' . number_format($this->price, 2);
    }

    /**
     * Check if variation is in stock.
     */
    public function getIsInStockAttribute(): bool
    {
        $stock = optional($this->stock)->quantity ?? 0;
        return $stock > 0;
    }

    /**
     * Get available quantity.
     */
    public function getAvailableQuantityAttribute(): int
    {
        return optional($this->stock)->quantity ?? 0;
    }

    /**
     * Get attribute values with their names.
     */
    public function getAttributeValuesAttribute()
    {
        if (!$this->attribute_value_ids) {
            return collect();
        }

        return \App\Models\AttributeValue::whereIn('id', $this->attribute_value_ids)
            ->with('attribute')
            ->get();
    }

    /**
     * Get formatted attribute values for display.
     */
    public function getFormattedAttributesAttribute(): string
    {
        return $this->attribute_values
            ->map(fn($attr) => $attr->attribute->name . ': ' . $attr->value)
            ->implode(', ');
    }

    /**
     * Check if variation has enough stock for given quantity.
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->available_quantity >= $quantity;
    }

    /**
     * Scope to filter only in-stock variations.
     */
    public function scopeInStock($query)
    {
        return $query->whereHas('stock', function ($q) {
            $q->where('quantity', '>', 0)->where('in_stock', true);
        });
    }

    /**
     * Get the best sale price for this variation
     */
    public function getBestSalePrice()
    {
        $activeSales = $this->product->activeSales;
        
        if ($activeSales->isEmpty()) {
            return $this->price;
        }
        
        $bestPrice = $this->price;
        
        foreach ($activeSales as $sale) {
            $discount = $sale->getDiscountForProduct($this->product);
            $salePrice = $sale->calculateSalePrice($this->price, $discount);
            $bestPrice = min($bestPrice, $salePrice);
        }
        
        return $bestPrice;
    }

    /**
     * Get discount percentage for this variation
     */
    public function getDiscountPercentage()
    {
        $salePrice = $this->getBestSalePrice();
        if ($salePrice < $this->price) {
            return round((($this->price - $salePrice) / $this->price) * 100);
        }
        return 0;
    }

    /**
     * Check if this variation has active sale
     */
    public function hasActiveSale()
    {
        return $this->product->hasActiveSale();
    }

    /**
     * Get active sale for this variation
     */
    public function getActiveSale()
    {
        return $this->product->getActiveSale();
    }

    /**
     * Get formatted sale price.
     */
    public function getFormattedSalePriceAttribute(): string
    {
        return '₹' . number_format($this->getBestSalePrice(), 2);
    }

    /**
     * Scope to filter variations by price range.
     */
    public function scopePriceBetween($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }
}