<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    use HasFactory;

    protected $table = 'universities';

    protected $fillable = [
        'name',
        'code',
        'location',
        'country_id',
        'state_id',
        'city_id',
        'type',
        'status',
        'established_year',
        'website',
        'description'
    ];

    protected $casts = [
        'country_id' => 'integer',
        'state_id' => 'integer',
        'city_id' => 'integer',
        'established_year' => 'integer',
        'status' => 'string'
    ];

    /**
     * Get all users associated with this university
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'institute');
    }

    /**
     * Get the country this university is located in
     */
    public function country()
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }

    /**
     * Get the state this university is located in
     */
    public function state()
    {
        return $this->belongsTo(States::class, 'state_id');
    }

    /**
     * Get the city this university is located in
     */
    public function city()
    {
        return $this->belongsTo(Cities::class, 'city_id');
    }

    /**
     * Scope to get active universities
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get universities by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get universities by location
     */
    public function scopeInCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    public function scopeInState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    public function scopeInCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }
}