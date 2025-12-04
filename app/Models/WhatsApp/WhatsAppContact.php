<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class WhatsAppContact extends Model
{
    use HasFactory;

    protected $table = 'whats_app_contacts';

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email',
        'company',
        'position',
        'notes',
        'tags',
        'status',
        'last_message_at',
        'message_count',
        'is_blocked'
    ];

    protected $casts = [
        'tags' => 'array',
        'last_message_at' => 'datetime',
        'message_count' => 'integer',
        'is_blocked' => 'boolean'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'last_message_at'
    ];

    /**
     * Get the user that owns the contact
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get messages sent to this contact
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'contact_id');
    }

    /**
     * Scope for active contacts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_blocked', false);
    }

    /**
     * Scope for blocked contacts
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    /**
     * Scope for contacts with specific tag
     */
    public function scopeWithTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($query) use ($term) {
            $query->where('name', 'like', '%' . $term . '%')
                  ->orWhere('phone', 'like', '%' . $term . '%')
                  ->orWhere('email', 'like', '%' . $term . '%')
                  ->orWhere('company', 'like', '%' . $term . '%');
        });
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedPhoneAttribute()
    {
        $phone = $this->phone;
        
        // Remove country code for display
        if (str_starts_with($phone, '91') && strlen($phone) > 10) {
            $phone = substr($phone, 2);
        }
        
        // Format as +91 XXXXX XXXXX
        if (strlen($phone) == 10) {
            return '+91 ' . substr($phone, 0, 5) . ' ' . substr($phone, 5);
        }
        
        return $this->phone;
    }

    /**
     * Get contact initials for avatar
     */
    public function getInitialsAttribute()
    {
        $name = $this->name ?? 'Unknown';
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }
        
        return $initials ? substr($initials, 0, 2) : 'UN';
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->is_blocked ?? false) {
            return '<span class="badge bg-danger">Blocked</span>';
        }

        $badges = [
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>'
        ];

        $status = $this->status ?? 'inactive';
        return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Add tag to contact
     */
    public function addTag($tag)
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    /**
     * Remove tag from contact
     */
    public function removeTag($tag)
    {
        $tags = $this->tags ?? [];
        $tags = array_diff($tags, [$tag]);
        $this->update(['tags' => array_values($tags)]);
    }

    /**
     * Block contact
     */
    public function block()
    {
        $this->update(['is_blocked' => true, 'status' => 'inactive']);
    }

    /**
     * Unblock contact
     */
    public function unblock()
    {
        $this->update(['is_blocked' => false, 'status' => 'active']);
    }

    /**
     * Update message statistics
     */
    public function updateMessageStats()
    {
        $this->update([
            'message_count' => $this->messages()->count(),
            'last_message_at' => $this->messages()->latest()->first()?->created_at
        ]);
    }

    /**
     * Get recent messages
     */
    public function getRecentMessages($limit = 5)
    {
        return $this->messages()->latest()->limit($limit)->get();
    }

    /**
     * Check if contact has WhatsApp
     */
    public function hasWhatsApp()
    {
        // This would typically be checked via API
        // For now, assume all contacts have WhatsApp
        return true;
    }

    /**
     * Get last message time for display
     */
    public function getLastMessageTimeAttribute()
    {
        return $this->last_message_at ? $this->last_message_at->diffForHumans() : 'Never';
    }

    /**
     * Get tags as comma separated string
     */
    public function getTagsStringAttribute()
    {
        return is_array($this->tags) ? implode(', ', $this->tags) : '';
    }
}