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

    /**
     * Get product variations that contain this attribute value
     * Since attribute_value_ids is stored as JSON, this returns a query builder
     */
    public function getProductVariationsQuery()
    {
        return \App\Models\ProductVariation::whereRaw('JSON_CONTAINS(attribute_value_ids, ?)', [json_encode([$this->id])]);
    }
    
    /**
     * Get count of products that have variations with this attribute value
     */
    public function getProductsCountAttribute()
    {
        return $this->getProductVariationsQuery()->count();
    }

    // Scope for default values
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
