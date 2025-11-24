<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Countries extends Model
{
    use HasFactory;

    protected $table = 'countries';

    protected $fillable = [
        'name',
        'code',
        'iso_code',
        'phone_code',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Get all states for this country
     */
    public function states(): HasMany
    {
        return $this->hasMany(States::class, 'country_id');
    }

    /**
     * Get all users from this country
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'country_id');
    }

    /**
     * Scope to get active countries
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get country by code
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }
}