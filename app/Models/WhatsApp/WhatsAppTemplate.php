<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class WhatsAppTemplate extends Model
{
    use HasFactory;

    protected $table = 'whats_app_templates';

    protected $fillable = [
        'user_id',
        'name',
        'content',
        'category',
        'variables',
        'status',
        'usage_count',
        'description'
    ];

    protected $casts = [
        'variables' => 'array',
        'usage_count' => 'integer'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the user that owns the template
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get messages that use this template
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'template_id');
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for templates by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for user templates
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get template content with variables replaced
     */
    public function getProcessedContent($variables = [])
    {
        $content = $this->content;
        
        // Replace template variables
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string)$value, $content);
        }
        
        // Replace default variables
        $defaultVars = [
            'user_name' => auth()->user()?->name ?? '',
            'user_email' => auth()->user()?->email ?? '',
            'current_date' => now()->format('Y-m-d'),
            'current_time' => now()->format('H:i:s'),
            'site_name' => config('app.name', ''),
            'site_url' => config('app.url', '')
        ];
        
        foreach ($defaultVars as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string)$value, $content);
        }
        
        return $content;
    }

    /**
     * Extract variables from template content
     */
    public function extractVariables()
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $this->content, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
            'draft' => '<span class="badge bg-warning">Draft</span>'
        ];

        $status = $this->status ?? 'inactive';
        return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get category badge color
     */
    public function getCategoryBadgeAttribute()
    {
        $categories = [
            'marketing' => 'primary',
            'notification' => 'info',
            'greeting' => 'success',
            'reminder' => 'warning',
            'support' => 'secondary',
            'promotional' => 'danger',
            'general' => 'secondary'
        ];

        $category = $this->category ?? 'general';
        $color = $categories[$category] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . ucfirst($category) . '</span>';
    }

    /**
     * Get short content for display
     */
    public function getShortContentAttribute()
    {
        return \Illuminate\Support\Str::limit($this->content, 100);
    }

    /**
     * Check if template has variables
     */
    public function hasVariables()
    {
        return !empty($this->extractVariables());
    }

    /**
     * Get template preview with sample data
     */
    public function getPreview()
    {
        $sampleData = [
            'user_name' => 'John Doe',
            'user_email' => 'john@example.com',
            'current_date' => now()->format('Y-m-d'),
            'current_time' => now()->format('H:i:s'),
            'site_name' => config('app.name', 'Your Site'),
            'site_url' => config('app.url', 'https://example.com'),
            'customer_name' => 'Jane Smith',
            'order_id' => '#12345',
            'amount' => '$99.99',
            'product_name' => 'Sample Product'
        ];

        try {
            return $this->getProcessedContent($sampleData);
        } catch (\Exception $e) {
            return $this->content; // Return original content if processing fails
        }
    }
}