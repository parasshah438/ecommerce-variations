<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_variation_id', 'quantity', 'price'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

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
