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

            Log::info('Social login successful', [
                'provider' => $provider,
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return redirect()->intended(route('welcome'))->with('success', 'Successfully logged in with ' . ucfirst($provider) . '!');

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
     * Find or create user from social login
     */
    private function findOrCreateUser($socialUser, $provider)
    {
        // First, try to find user by email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Update social provider info if not already set
            $socialData = $user->social_providers ?? [];
            if (!isset($socialData[$provider])) {
                $socialData[$provider] = [
                    'id' => $socialUser->getId(),
                    'name' => $socialUser->getName(),
                    'avatar' => $socialUser->getAvatar(),
                    'connected_at' => now()->toISOString(),
                ];
                $user->social_providers = $socialData;
                $user->save();
            }

            return $user;
        }

        // Create new user
        $user = User::create([
            'name' => $socialUser->getName() ?: 'Social User',
            'email' => $socialUser->getEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make(str()->random(32)), // Random password for social users
            'social_providers' => [
                $provider => [
                    'id' => $socialUser->getId(),
                    'name' => $socialUser->getName(),
                    'avatar' => $socialUser->getAvatar(),
                    'connected_at' => now()->toISOString(),
                ]
            ],
        ]);

        Log::info('New user created via social login', [
            'provider' => $provider,
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return $user;
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
            $user->social_providers = $socialData;
            $user->save();

            Log::info('Social provider disconnected', [
                'user_id' => $user->id,
                'provider' => $provider,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($provider) . ' account disconnected successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => ucfirst($provider) . ' account is not connected.',
        ], 400);
    }
}