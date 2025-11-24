<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cities extends Model
{
    use HasFactory;

    protected $table = 'cities';

    protected $fillable = [
        'name',
        'state_id',
        'country_id',
        'status'
    ];

    protected $casts = [
        'state_id' => 'integer',
        'country_id' => 'integer',
        'status' => 'string'
    ];

    /**
     * Get the state this city belongs to
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(States::class, 'state_id');
    }

    /**
     * Get the country this city belongs to
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }

    /**
     * Get all users from this city
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'city_id');
    }

    /**
     * Scope to get active cities
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get cities by state
     */
    public function scopeForState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    /**
     * Scope to get cities by country
     */
    public function scopeForCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }
}