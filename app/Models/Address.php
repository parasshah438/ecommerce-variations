<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'label', 
        'name', 
        'phone', 
        'alternate_phone',
        'address_line', 
        'city', 
        'state', 
        'zip', 
        'country',
        'type',
        'is_default',
        'delivery_instructions',
        'landmark'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set this address as default and unset others for the user
     */
    public function setAsDefault()
    {
        // Unset all other default addresses for this user
        $this->user->addresses()->where('id', '!=', $this->id)->update(['is_default' => false]);
        
        // Set this address as default
        $this->update(['is_default' => true]);
    }

    /**
     * Get formatted address string
     */
    public function getFormattedAddressAttribute()
    {
        $parts = [];
        
        if ($this->address_line) $parts[] = $this->address_line;
        if ($this->landmark) $parts[] = 'Near ' . $this->landmark;
        if ($this->city) $parts[] = $this->city;
        if ($this->state) $parts[] = $this->state;
        if ($this->zip) $parts[] = $this->zip;
        if ($this->country) $parts[] = $this->country;
        
        return implode(', ', $parts);
    }

    /**
     * Get address type with icon
     */
    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'home' => 'bi-house-door',
            'work' => 'bi-building',
            'other' => 'bi-geo-alt',
            default => 'bi-geo-alt'
        };
    }

    /**
     * Get address type label
     */
    public function getTypeLabelAttribute()
    {
        return ucfirst($this->type);
    }
}
