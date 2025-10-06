<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id', 
        'value', 
        'code', 
        'hex_color', 
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function productVariations()
    {
        return $this->belongsToMany(ProductVariation::class, 'product_variation_attribute_values');
    }

    // Scope for default values
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
