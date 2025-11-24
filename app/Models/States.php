<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class States extends Model
{
    use HasFactory;

    protected $table = 'states';

    protected $fillable = [
        'name',
        'code',
        'country_id',
        'status'
    ];

    protected $casts = [
        'country_id' => 'integer',
        'status' => 'string'
    ];

    /**
     * Get the country this state belongs to
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }

    /**
     * Get all cities in this state
     */
    public function cities(): HasMany
    {
        return $this->hasMany(Cities::class, 'state_id');
    }

    /**
     * Get all users from this state
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'state_id');
    }

    /**
     * Scope to get active states
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get states by country
     */
    public function scopeForCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }
}