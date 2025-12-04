<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class WhatsAppGroup extends Model
{
    use HasFactory;

    protected $table = 'whats_app_groups';

    protected $fillable = [
        'user_id',
        'group_id',
        'name',
        'description',
        'avatar',
        'admin_only',
        'invite_link',
        'participant_count',
        'status',
        'created_by_me',
        'last_message_at'
    ];

    protected $casts = [
        'admin_only' => 'boolean',
        'created_by_me' => 'boolean',
        'participant_count' => 'integer',
        'last_message_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'last_message_at'
    ];

    /**
     * Get the user that manages this group
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get contacts that are members of this group
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(WhatsAppContact::class, 'whats_app_group_members', 'group_id', 'contact_id')
                    ->withPivot(['role', 'joined_at'])
                    ->withTimestamps();
    }

    /**
     * Scope for active groups
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for groups created by user
     */
    public function scopeCreatedByMe($query)
    {
        return $query->where('created_by_me', true);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
            'archived' => '<span class="badge bg-warning">Archived</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>';
    }

    /**
     * Get group initials for avatar
     */
    public function getInitialsAttribute()
    {
        $words = explode(' ', trim($this->name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }
        
        return substr($initials, 0, 2);
    }

    /**
     * Add member to group
     */
    public function addMember(WhatsAppContact $contact, $role = 'member')
    {
        if (!$this->contacts()->where('contact_id', $contact->id)->exists()) {
            $this->contacts()->attach($contact->id, [
                'role' => $role,
                'joined_at' => now()
            ]);
            
            $this->increment('participant_count');
        }
    }

    /**
     * Remove member from group
     */
    public function removeMember(WhatsAppContact $contact)
    {
        if ($this->contacts()->where('contact_id', $contact->id)->exists()) {
            $this->contacts()->detach($contact->id);
            $this->decrement('participant_count');
        }
    }

    /**
     * Get admins
     */
    public function getAdmins()
    {
        return $this->contacts()->wherePivot('role', 'admin')->get();
    }

    /**
     * Get members
     */
    public function getMembers()
    {
        return $this->contacts()->wherePivot('role', 'member')->get();
    }

    /**
     * Check if contact is admin
     */
    public function isAdmin(WhatsAppContact $contact)
    {
        return $this->contacts()
                    ->where('contact_id', $contact->id)
                    ->wherePivot('role', 'admin')
                    ->exists();
    }

    /**
     * Check if contact is member
     */
    public function isMember(WhatsAppContact $contact)
    {
        return $this->contacts()->where('contact_id', $contact->id)->exists();
    }

    /**
     * Promote member to admin
     */
    public function promoteToAdmin(WhatsAppContact $contact)
    {
        $this->contacts()->updateExistingPivot($contact->id, ['role' => 'admin']);
    }

    /**
     * Demote admin to member
     */
    public function demoteToMember(WhatsAppContact $contact)
    {
        $this->contacts()->updateExistingPivot($contact->id, ['role' => 'member']);
    }

    /**
     * Update participant count
     */
    public function updateParticipantCount()
    {
        $count = $this->contacts()->count();
        $this->update(['participant_count' => $count]);
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        return null;
    }

    /**
     * Get short description
     */
    public function getShortDescriptionAttribute()
    {
        return \Illuminate\Support\Str::limit($this->description, 50);
    }

    /**
     * Archive group
     */
    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Unarchive group
     */
    public function unarchive()
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Leave group
     */
    public function leave()
    {
        $this->update(['status' => 'inactive']);
    }
}