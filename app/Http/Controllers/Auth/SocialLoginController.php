<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialLoginController extends Controller
{
    /**
     * Available social providers configuration
     */
    private function getSocialProviders()
    {
        return [
            'google' => [
                'name' => 'Google',
                'icon' => 'google',
                'color' => '#4285F4',
                'background' => '#ffffff',
                'text_color' => '#000000',
                'enabled' => config('services.google.client_id') !== null,
            ],
            'facebook' => [
                'name' => 'Facebook',
                'icon' => 'facebook',
                'color' => '#1877F2',
                'background' => '#ffffff',
                'text_color' => '#000000',
                'enabled' => config('services.facebook.client_id') !== null,
            ],
            'github' => [
                'name' => 'GitHub',
                'icon' => 'github',
                'color' => '#333333',
                'background' => '#ffffff',
                'text_color' => '#000000',
                'enabled' => config('services.github.client_id') !== null,
            ],
            'linkedin' => [
                'name' => 'LinkedIn',
                'icon' => 'linkedin',
                'color' => '#0A66C2',
                'background' => '#ffffff',
                'text_color' => '#000000',
                'enabled' => config('services.linkedin.client_id') !== null,
            ],
            'twitter' => [
                'name' => 'Twitter',
                'icon' => 'twitter',
                'color' => '#1DA1F2',
                'background' => '#ffffff',
                'text_color' => '#000000',
                'enabled' => config('services.twitter.client_id') !== null,
            ],
        ];
    }

    /**
     * Get enabled social providers
     */
    public function getEnabledProviders()
    {
        $providers = $this->getSocialProviders();
        return collect($providers)->filter(function ($provider) {
            return $provider['enabled'];
        })->all();
    }

    /**
     * Redirect to social provider
     */
    public function redirectToProvider($provider)
    {
        try {
            // Validate provider
            if (!$this->isProviderSupported($provider)) {
                return redirect()->route('login')->with('error', 'Social provider not supported.');
            }

            // Check if provider is enabled
            if (!$this->isProviderEnabled($provider)) {
                return redirect()->route('login')->with('error', 'Social login with ' . ucfirst($provider) . ' is not available.');
            }

            Log::info('Redirecting to social provider', [
                'provider' => $provider,
                'user_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return Socialite::driver($provider)->redirect();

        } catch (Exception $e) {
            Log::error('Social login redirect failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'user_ip' => request()->ip(),
            ]);

            return redirect()->route('login')->with('error', 'Unable to connect to ' . ucfirst($provider) . '. Please try again.');
        }
    }

    /**
     * Handle callback from social provider
     */
    public function handleProviderCallback($provider)
    {
        try {
            // Validate provider
            if (!$this->isProviderSupported($provider)) {
                return redirect()->route('login')->with('error', 'Social provider not supported.');
            }

            // Get user from provider
            $socialUser = Socialite::driver($provider)->user();

            Log::info('Social login callback received', [
                'provider' => $provider,
                'social_id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName(),
            ]);

            // Find or create user
            $user = $this->findOrCreateUser($socialUser, $provider);

            // Enforce single session (same as regular login)
            $this->enforceUniqueSession($user);

            // Login user
            Auth::login($user, true);
            
            // Regenerate session after login to ensure consistency
            request()->session()->regenerate();
            
            // Update the session ID again after regeneration
            $user->update(['active_session_id' => session()->getId()]);

            Log::info('Social login successful', [
                'provider' => $provider,
                'user_id' => $user->id,
                'email' => $user->email,
                'session_id' => session()->getId(),
                'has_avatar' => !empty($user->avatar),
                'total_social_providers' => count($user->social_providers ?? [])
            ]);

            return redirect()->intended(route('dashboard'))->with('success', 'Successfully logged in with ' . ucfirst($provider) . '!');

        } catch (Exception $e) {
            Log::error('Social login callback failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')->with('error', 'Social login failed. Please try again or use email login.');
        }
    }

    /**
     * Find or create user from social login with enhanced account merging and avatar sync
     */
    private function findOrCreateUser($socialUser, $provider)
    {
        $email = $socialUser->getEmail();
        $socialId = $socialUser->getId();
        $socialName = $socialUser->getName();
        $socialAvatar = $socialUser->getAvatar();

        // First, try to find user by email (account merging)
        $user = User::where('email', $email)->first();

        if ($user) {
            Log::info('Existing user found for social login', [
                'provider' => $provider,
                'user_id' => $user->id,
                'email' => $email,
                'merging_account' => true
            ]);

            // Merge social provider data
            $socialData = $user->social_providers ?? [];
            $providerData = [
                'id' => $socialId,
                'name' => $socialName,
                'avatar' => $socialAvatar,
                'connected_at' => now()->toISOString(),
                'last_login_at' => now()->toISOString(),
            ];

            // Update or add provider data
            $socialData[$provider] = $providerData;

            // Sync avatar if user doesn't have one or if social avatar is newer/better
            $shouldUpdateAvatar = $this->shouldUpdateAvatar($user, $socialAvatar, $provider);
            
            $updateData = ['social_providers' => $socialData];
            if ($shouldUpdateAvatar && $socialAvatar) {
                $updateData['avatar'] = $this->processSocialAvatar($socialAvatar, $provider);
                Log::info('Avatar synchronized from social provider', [
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'avatar_url' => $socialAvatar
                ]);
            }

            $user->update($updateData);

            Log::info('Social provider merged with existing account', [
                'user_id' => $user->id,
                'provider' => $provider,
                'total_providers' => count($socialData)
            ]);

            return $user;
        }

        // Check if social ID already exists with different email (edge case)
        $existingSocialUser = User::whereJsonContains('social_providers->' . $provider . '->id', $socialId)->first();
        
        if ($existingSocialUser) {
            Log::warning('Social ID exists with different email', [
                'provider' => $provider,
                'social_id' => $socialId,
                'existing_email' => $existingSocialUser->email,
                'new_email' => $email
            ]);
            
            // Update email if it's different (user might have changed email on social platform)
            if ($existingSocialUser->email !== $email) {
                $existingSocialUser->update(['email' => $email]);
                Log::info('Updated user email from social provider', [
                    'user_id' => $existingSocialUser->id,
                    'old_email' => $existingSocialUser->email,
                    'new_email' => $email
                ]);
            }
            
            return $existingSocialUser;
        }

        // Create new user with social avatar
        $avatarPath = $socialAvatar ? $this->processSocialAvatar($socialAvatar, $provider) : null;

        $user = User::create([
            'name' => $socialName ?: 'Social User',
            'email' => $email,
            'email_verified_at' => now(), // Social emails are pre-verified
            'password' => Hash::make(str()->random(32)), // Random password for social users
            'avatar' => $avatarPath,
            'social_providers' => [
                $provider => [
                    'id' => $socialId,
                    'name' => $socialName,
                    'avatar' => $socialAvatar,
                    'connected_at' => now()->toISOString(),
                    'last_login_at' => now()->toISOString(),
                ]
            ],
        ]);

        Log::info('New user created via social login', [
            'provider' => $provider,
            'user_id' => $user->id,
            'email' => $email,
            'has_avatar' => !empty($avatarPath)
        ]);

        return $user;
    }

    /**
     * Determine if user avatar should be updated from social provider
     */
    private function shouldUpdateAvatar($user, $socialAvatar, $provider)
    {
        // No social avatar available
        if (empty($socialAvatar)) {
            return false;
        }

        // User has no avatar - always update
        if (empty($user->avatar)) {
            return true;
        }

        // Check if current avatar is from a social provider
        $currentProviders = $user->social_providers ?? [];
        
        // If current avatar is from social provider, update with newer one
        if ($this->isAvatarFromSocialProvider($user->avatar)) {
            return true;
        }

        // User has custom avatar - don't override unless it's been a while
        $lastUpdate = null;
        if (isset($currentProviders[$provider]['last_avatar_sync'])) {
            $lastUpdate = \Carbon\Carbon::parse($currentProviders[$provider]['last_avatar_sync']);
        }

        // Update avatar if it hasn't been synced in the last 30 days
        return $lastUpdate === null || $lastUpdate->diffInDays(now()) > 30;
    }

    /**
     * Check if current avatar is from social provider
     */
    private function isAvatarFromSocialProvider($avatarPath)
    {
        if (empty($avatarPath)) {
            return false;
        }

        // Check if avatar path contains social provider indicators
        return str_contains($avatarPath, 'social-avatars') || 
               str_contains($avatarPath, 'google') || 
               str_contains($avatarPath, 'facebook') || 
               str_contains($avatarPath, 'github') || 
               str_contains($avatarPath, 'linkedin') || 
               str_contains($avatarPath, 'twitter');
    }

    /**
     * Process and store social avatar
     */
    private function processSocialAvatar($avatarUrl, $provider)
    {
        try {
            if (empty($avatarUrl)) {
                return null;
            }

            // Create directory for social avatars
            $avatarDir = 'social-avatars/' . $provider;
            $fullDir = storage_path('app/public/' . $avatarDir);
            
            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }

            // Generate unique filename
            $filename = uniqid($provider . '_') . '.jpg';
            $filePath = $avatarDir . '/' . $filename;
            $fullPath = storage_path('app/public/' . $filePath);

            // Download and save avatar
            $imageData = @file_get_contents($avatarUrl);
            
            if ($imageData === false) {
                Log::warning('Failed to download social avatar', [
                    'provider' => $provider,
                    'url' => $avatarUrl
                ]);
                return null;
            }

            file_put_contents($fullPath, $imageData);

            Log::info('Social avatar downloaded and stored', [
                'provider' => $provider,
                'original_url' => $avatarUrl,
                'stored_path' => $filePath,
                'file_size' => strlen($imageData)
            ]);

            return $filePath;

        } catch (\Exception $e) {
            Log::error('Failed to process social avatar', [
                'provider' => $provider,
                'url' => $avatarUrl,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Enforce unique session (same logic as LoginController)
     */
    private function enforceUniqueSession(User $user)
    {
        if ($user->active_session_id && $user->active_session_id !== session()->getId()) {
            Log::info('Destroying previous session for social login', [
                'user_id' => $user->id,
                'previous_session' => $user->active_session_id,
                'new_session' => session()->getId(),
            ]);
        }

        // Update user session info
        $user->update([
            'active_session_id' => session()->getId(),
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'last_device_info' => json_encode($this->getDeviceInfo()),
        ]);
    }

    /**
     * Get device information
     */
    private function getDeviceInfo()
    {
        $userAgent = request()->userAgent();
        
        // Simple device detection
        $platform = 'Unknown';
        $browser = 'Unknown';
        $isMobile = false;
        
        // Platform detection
        if (stripos($userAgent, 'Windows') !== false) $platform = 'Windows';
        elseif (stripos($userAgent, 'Mac') !== false) $platform = 'Mac';
        elseif (stripos($userAgent, 'Linux') !== false) $platform = 'Linux';
        elseif (stripos($userAgent, 'Android') !== false) $platform = 'Android';
        elseif (stripos($userAgent, 'iOS') !== false) $platform = 'iOS';
        
        // Browser detection
        if (stripos($userAgent, 'Chrome') !== false) $browser = 'Chrome';
        elseif (stripos($userAgent, 'Firefox') !== false) $browser = 'Firefox';
        elseif (stripos($userAgent, 'Safari') !== false) $browser = 'Safari';
        elseif (stripos($userAgent, 'Edge') !== false) $browser = 'Edge';
        
        // Mobile detection
        $isMobile = stripos($userAgent, 'Mobile') !== false || 
                   stripos($userAgent, 'Android') !== false ||
                   stripos($userAgent, 'iPhone') !== false;
        
        return [
            'platform' => $platform,
            'browser' => $browser,
            'version' => '1.0',
            'device' => $isMobile ? 'Mobile' : 'Desktop',
            'is_mobile' => $isMobile,
            'is_tablet' => false,
            'is_desktop' => !$isMobile,
            'robot' => false,
            'user_agent' => $userAgent,
        ];
    }

    /**
     * Check if provider is supported
     */
    private function isProviderSupported($provider)
    {
        return array_key_exists($provider, $this->getSocialProviders());
    }

    /**
     * Check if provider is enabled
     */
    private function isProviderEnabled($provider)
    {
        $providers = $this->getSocialProviders();
        return isset($providers[$provider]) && $providers[$provider]['enabled'];
    }

    /**
     * Get social providers for API/AJAX requests
     */
    public function getProviders()
    {
        return response()->json([
            'providers' => $this->getEnabledProviders(),
            'total' => count($this->getEnabledProviders()),
        ]);
    }

    /**
     * Disconnect social provider
     */
    public function disconnectProvider(Request $request, $provider)
    {
        $user = Auth::user();
        $socialData = $user->social_providers ?? [];

        if (isset($socialData[$provider])) {
            unset($socialData[$provider]);
            
            // If avatar was from this provider and no other providers, remove it
            $shouldRemoveAvatar = $this->shouldRemoveAvatarOnDisconnect($user, $provider);
            
            $updateData = ['social_providers' => $socialData];
            if ($shouldRemoveAvatar) {
                $updateData['avatar'] = null;
                Log::info('Avatar removed after social provider disconnect', [
                    'user_id' => $user->id,
                    'provider' => $provider
                ]);
            }
            
            $user->update($updateData);

            Log::info('Social provider disconnected', [
                'user_id' => $user->id,
                'provider' => $provider,
                'remaining_providers' => count($socialData)
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($provider) . ' account disconnected successfully.',
                'remaining_providers' => count($socialData)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => ucfirst($provider) . ' account is not connected.',
        ], 400);
    }

    /**
     * Check if avatar should be removed when disconnecting provider
     */
    private function shouldRemoveAvatarOnDisconnect($user, $provider)
    {
        // Don't remove if no avatar
        if (empty($user->avatar)) {
            return false;
        }

        // Don't remove if avatar is not from social provider
        if (!$this->isAvatarFromSocialProvider($user->avatar)) {
            return false;
        }

        // Don't remove if avatar is from a different provider that's still connected
        $socialData = $user->social_providers ?? [];
        foreach ($socialData as $providerKey => $data) {
            if ($providerKey !== $provider && !empty($data['avatar'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get user's avatar URL (helper method for views)
     */
    public function getUserAvatarUrl($user = null)
    {
        $user = $user ?: Auth::user();
        
        if (!$user || empty($user->avatar)) {
            return asset('images/default-avatar.svg'); // Default avatar
        }

        // If avatar is a full URL (from social provider), return as is
        if (filter_var($user->avatar, FILTER_VALIDATE_URL)) {
            return $user->avatar;
        }

        // If avatar is a local path, return storage URL
        return asset('storage/' . $user->avatar);
    }

    /**
     * Sync avatar from specific provider (manual sync)
     */
    public function syncAvatar(Request $request, $provider)
    {
        $user = Auth::user();
        $socialData = $user->social_providers ?? [];

        if (!isset($socialData[$provider])) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($provider) . ' account is not connected.',
            ], 400);
        }

        $providerData = $socialData[$provider];
        $socialAvatar = $providerData['avatar'] ?? null;

        if (empty($socialAvatar)) {
            return response()->json([
                'success' => false,
                'message' => 'No avatar available from ' . ucfirst($provider) . '.',
            ], 400);
        }

        $avatarPath = $this->processSocialAvatar($socialAvatar, $provider);

        if ($avatarPath) {
            // Update last sync time
            $socialData[$provider]['last_avatar_sync'] = now()->toISOString();
            
            $user->update([
                'avatar' => $avatarPath,
                'social_providers' => $socialData
            ]);

            Log::info('Avatar manually synced from social provider', [
                'user_id' => $user->id,
                'provider' => $provider
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar synced successfully from ' . ucfirst($provider) . '.',
                'avatar_url' => $this->getUserAvatarUrl($user)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to sync avatar from ' . ucfirst($provider) . '.',
        ], 500);
    }
}