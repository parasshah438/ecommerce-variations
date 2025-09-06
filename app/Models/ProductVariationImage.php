<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariationImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'product_variation_id', 'path', 'alt', 'position'];

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
