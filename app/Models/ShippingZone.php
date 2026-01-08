<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'pincodes',
        'description',
        'active'
    ];

    protected $casts = [
        'pincodes' => 'array',
        'active' => 'boolean'
    ];

    public function shippingRates()
    {
        return $this->hasMany(ShippingRate::class, 'zone_id')->where('active', true)->orderBy('min_weight');
    }

    public function getShippingRate($weight)
    {
        return $this->shippingRates()
            ->where('min_weight', '<=', $weight)
            ->where(function ($query) use ($weight) {
                $query->whereNull('max_weight')
                    ->orWhere('max_weight', '>=', $weight);
            })
            ->first();
    }

    public static function findByPincode($pincode)
    {
        return static::where('active', true)
            ->where(function ($query) use ($pincode) {
                $query->whereJsonContains('pincodes', $pincode)
                    ->orWhereJsonContains('pincodes', substr($pincode, 0, 3)); // First 3 digits
            })
            ->first();
    }
}