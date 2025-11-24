<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User_activity extends Model
{
    use HasFactory;

    protected $table = 'user_activities';

    protected $fillable = [
        'user_id',
        'log_description',
        'ip_address',
        'user_agent',
        'action',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the activity log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get activities for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get activities by action type
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Log user activity
     */
    public static function logActivity($userId, $description, $action = null, $details = null)
    {
        return self::create([
            'user_id' => $userId,
            'log_description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'action' => $action,
            'details' => $details
        ]);
    }
}