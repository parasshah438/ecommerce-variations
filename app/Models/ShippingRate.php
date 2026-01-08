<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'min_weight',
        'max_weight',
        'base_rate',
        'additional_rate',
        'active'
    ];

    protected $casts = [
        'min_weight' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'base_rate' => 'decimal:2',
        'additional_rate' => 'decimal:2',
        'active' => 'boolean'
    ];

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    public function calculateCost($weight)
    {
        $baseCost = $this->base_rate;
        
        if ($this->additional_rate > 0 && $weight > $this->min_weight) {
            $additionalWeight = $weight - $this->min_weight;
            $additionalKgs = ceil($additionalWeight / 1000); // Convert to kg and round up
            $baseCost += ($additionalKgs * $this->additional_rate);
        }

        return round($baseCost, 2);
    }
}