<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'country_code',
        'mobile_number',
        'active_session_id',
        'last_login_at',
        'last_login_ip',
        'last_device_info',
        'social_providers',
        'avatar',
        'role',
        'status',
        'date_of_birth',
        'address',
        'city',
        'country',
        'bio',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'last_device_info' => 'array',
            'social_providers' => 'array',
        ];
    }

    /**
     * Get all addresses for the user
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the user's cart
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get all orders for the user
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the mobile attribute (for backward compatibility)
     */
    public function getMobileAttribute()
    {
        return $this->mobile_number;
    }

    /**
     * Get the user's activity logs
     */
    public function activities()
    {
        return $this->hasMany(User_activity::class);
    }

    /**
     * Get the user's country
     */
    public function countryRelation()
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }

    /**
     * Get the user's state
     */
    public function stateRelation()
    {
        return $this->belongsTo(States::class, 'state_id');
    }

    /**
     * Get the user's city
     */
    public function cityRelation()
    {
        return $this->belongsTo(Cities::class, 'city_id');
    }

    /**
     * Get the user's university/institute
     */
    public function university()
    {
        return $this->belongsTo(University::class, 'institute');
    }

    /**
     * Scope to get users by status
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get user's avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if (empty($this->avatar)) {
            return asset('images/default-avatar.svg');
        }

        // If avatar is a full URL (from social provider), return as is
        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        // If avatar is a local path, return storage URL
        return asset('storage/' . $this->avatar);
    }

    /**
     * Get connected social providers
     */
    public function getConnectedProvidersAttribute(): array
    {
        return array_keys($this->social_providers ?? []);
    }

    /**
     * Check if specific social provider is connected
     */
    public function hasProviderConnected(string $provider): bool
    {
        return isset($this->social_providers[$provider]);
    }

    /**
     * Get social provider data for specific provider
     */
    public function getSocialProvider(string $provider): ?array
    {
        return $this->social_providers[$provider] ?? null;
    }
}
