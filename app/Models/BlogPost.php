<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'blog_category_id',
        'author',
        'status',
        'is_trending',
        'is_featured',
        'views_count',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'canonical_url',
        'schema_markup',
        'published_at',
    ];

    protected $casts = [
        'is_trending' => 'boolean',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'schema_markup' => 'array',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            // Auto-set published_at when status becomes published
            if ($post->status === 'published' && empty($post->published_at)) {
                $post->published_at = now();
            }
        });
        static::updating(function ($post) {
            if ($post->isDirty('title') && !$post->isDirty('slug')) {
                $post->slug = Str::slug($post->title);
            }
            // Auto-set published_at when transitioning to published
            if ($post->isDirty('status') && $post->status === 'published' && empty($post->published_at)) {
                $post->published_at = now();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('excerpt', 'LIKE', "%{$term}%")
              ->orWhere('content', 'LIKE', "%{$term}%");
        });
    }

    public function getFormattedDateAttribute()
    {
        return $this->published_at ? $this->published_at->format('M d, Y') : $this->created_at->format('M d, Y');
    }

    public function getReadingTimeAttribute()
    {
        $words = str_word_count(strip_tags($this->content ?? ''));
        $minutes = ceil($words / 200);
        return $minutes < 1 ? '1 min read' : "{$minutes} min read";
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return asset('uploads/' . $this->featured_image);
        }
        return asset('images/blog-placeholder.jpg');
    }

    public function getOgImageUrlAttribute()
    {
        if ($this->og_image) {
            return asset('uploads/' . $this->og_image);
        }
        return $this->featured_image_url;
    }

    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->title;
    }

    public function getMetaDescriptionAttribute($value)
    {
        return $value ?: ($this->excerpt ?: strip_tags(substr($this->content ?? '', 0, 160)));
    }
}
