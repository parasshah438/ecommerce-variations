<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'slug', 
        'type', 
        'is_required', 
        'is_filterable'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_filterable' => 'boolean'
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class);
    }

    // Scope for filterable attributes
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    // Scope for required attributes
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }
}
