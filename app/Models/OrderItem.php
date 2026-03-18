<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    const STATUS_ACTIVE    = 'active';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'product_variation_id',
        'quantity',
        'price',
        'status',
        'cancelled_at',
        'cancellation_reason',
        'refund_amount',
        'refund_id',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'refund_amount'=> 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    /**
     * Get the product name safely, handling null relationships
     */
    public function getProductNameAttribute()
    {
        if ($this->productVariation && $this->productVariation->product) {
            return $this->productVariation->product->name;
        }
        return 'Product unavailable';
    }

    /**
     * Get the product safely
     */
    public function getProductAttribute()
    {
        if ($this->productVariation && $this->productVariation->product) {
            return $this->productVariation->product;
        }
        return null;
    }
}
