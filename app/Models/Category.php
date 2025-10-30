<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'image', 'parent_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the image URL attribute
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return asset('images/category-placeholder.svg');
    }

    /**
     * Delete image when category is deleted
     */
    public function deleteImage()
    {
        if ($this->image && Storage::exists($this->image)) {
            Storage::delete($this->image);
        }
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the full breadcrumb path for this category
     */
    public function getBreadcrumbPath()
    {
        $path = collect([$this]);
        
        $current = $this;
        while ($current->parent) {
            $current = $current->parent;
            $path->prepend($current);
        }
        
        return $path;
    }

    /**
     * Get formatted breadcrumb string
     */
    public function getBreadcrumbString($separator = ' > ')
    {
        return $this->getBreadcrumbPath()->pluck('name')->implode($separator);
    }
}
