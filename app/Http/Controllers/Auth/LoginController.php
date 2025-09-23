<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * The guard name for rate limiting.
     *
     * @var string
     */
    protected $guardName = 'web';

    /**
     * The maximum number of attempts to allow.
     *
     * @var int
     */
    protected $maxAttempts = 3;

    /**
     * The number of minutes to throttle for.
     *
     * @var int
     */
    protected $decayMinutes = 2;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Show the application's login form with social providers.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showLoginForm()
    {
        $socialController = new SocialLoginController();
        $socialProviders = $socialController->getEnabledProviders();
        
        return view('auth.login', compact('socialProviders'));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Check if the user has too many login attempts
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // Attempt to log the user in
        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            $this->clearLoginAttempts($request);

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'login_field';
    }

    /**
     * Determine if the user has too many failed login attempts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasTooManyLoginAttempts(Request $request)
    {
        return RateLimiter::tooManyAttempts(
            $this->throttleKey($request), $this->maxAttempts
        );
    }

    /**
     * Increment the login attempts for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function incrementLoginAttempts(Request $request)
    {
        RateLimiter::hit(
            $this->throttleKey($request), $this->decayMinutes * 60
        );
    }

    /**
     * Clear the login locks for the given user credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function clearLoginAttempts(Request $request)
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    /**
     * Fire an event when a lockout occurs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function fireLockoutEvent(Request $request)
    {
        // You can dispatch a lockout event here if needed
        Log::warning('User locked out due to too many login attempts', [
            'ip' => $request->ip(),
            'login_field' => $request->input('login_field'),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = RateLimiter::availableIn(
            $this->throttleKey($request)
        );

        throw ValidationException::withMessages([
            'login_field' => [
                sprintf(
                    'Too many login attempts. Please try again in %d seconds.',
                    $seconds
                )
            ],
        ])->status(429);
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return strtolower($request->input('login_field')) . '|' . $request->ip();
    }

    /**
     * Get the remaining attempts for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int
     */
    public function getRemainingAttempts(Request $request)
    {
        $attempts = RateLimiter::attempts($this->throttleKey($request));
        return max(0, $this->maxAttempts - $attempts);
    }

    /**
     * Get the lockout time remaining in seconds.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int
     */
    public function getLockoutTimeRemaining(Request $request)
    {
        if (!$this->hasTooManyLoginAttempts($request)) {
            return 0;
        }

        return RateLimiter::availableIn($this->throttleKey($request));
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login_field' => 'required|string',
            'password' => 'required|string',
        ], [
            'login_field.required' => 'Email or mobile number is required.',
        ]);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $loginField = $request->input('login_field');
        
        // Check if the input is an email or mobile number
        if ($this->isEmail($loginField)) {
            return [
                'email' => $loginField,
                'password' => $request->input('password'),
            ];
        } else {
            // Handle mobile number - normalize the format
            $normalizedMobile = $this->normalizeMobileNumber($loginField);
            return [
                'mobile_number' => $normalizedMobile,
                'password' => $request->input('password'),
            ];
        }
    }

    /**
     * Check if the given string is an email.
     *
     * @param  string  $value
     * @return bool
     */
    protected function isEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Normalize mobile number format for database lookup.
     *
     * @param  string  $mobile
     * @return string
     */
    protected function normalizeMobileNumber($mobile)
    {
        // Remove all non-digit characters except +
        $mobile = preg_replace('/[^\d+]/', '', $mobile);
        
        // If it doesn't start with +, assume it's an Indian number and add +91
        if (substr($mobile, 0, 1) !== '+') {
            // Check if it's a 10-digit Indian number
            if (strlen($mobile) === 10 && substr($mobile, 0, 1) === '9') {
                $mobile = '+91' . $mobile;
            } elseif (strlen($mobile) === 12 && substr($mobile, 0, 2) === '91') {
                // If it starts with 91 but no +, add the +
                $mobile = '+' . $mobile;
            } elseif (strlen($mobile) === 13 && substr($mobile, 0, 3) === '919') {
                // If it's 919xxxxxxxxx format, add +
                $mobile = '+' . $mobile;
            }
        }
        
        return $mobile;
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Enforce single device login
        $this->enforceUniqueSession($user, $request);
        
        // Log session activity
        $this->logSessionActivity($user, $request);
        
        // Set session data for tracking
        session([
            'device_info' => $this->getDeviceInfo($request),
            'login_time' => now(),
            'user_id' => $user->id,
        ]);
        
        return redirect()->intended($this->redirectPath());
    }

    /**
     * Enforce unique session by invalidating previous sessions.
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function enforceUniqueSession($user, Request $request)
    {
        // Get previous session ID
        $previousSessionId = $user->active_session_id;
        
        if ($previousSessionId) {
            // Destroy previous session
            $this->destroySession($previousSessionId);
            
            // Log security event
            Log::info('User session replaced - Single device login enforced', [
                'user_id' => $user->id,
                'email' => $user->email,
                'previous_session' => $previousSessionId,
                'new_session' => session()->getId(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
        
        // Update user with new session information
        $user->update([
            'active_session_id' => session()->getId(),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_device_info' => $this->getDeviceInfo($request),
        ]);
    }

    /**
     * Destroy a specific session.
     *
     * @param  string  $sessionId
     * @return void
     */
    protected function destroySession($sessionId)
    {
        try {
            $sessionHandler = app('session.store')->getHandler();
            $sessionHandler->destroy($sessionId);
        } catch (\Exception $e) {
            Log::warning('Failed to destroy session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get device information from request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getDeviceInfo(Request $request)
    {
        $userAgent = $request->userAgent();
        
        return [
            'ip' => $request->ip(),
            'user_agent' => $userAgent,
            'device' => $this->detectDevice($userAgent),
            'browser' => $this->detectBrowser($userAgent),
            'platform' => $this->detectPlatform($userAgent),
        ];
    }

    /**
     * Detect device type from user agent.
     *
     * @param  string  $userAgent
     * @return string
     */
    protected function detectDevice($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            if (preg_match('/iPad/', $userAgent)) {
                return 'Tablet';
            }
            return 'Mobile';
        }
        return 'Desktop';
    }

    /**
     * Detect browser from user agent.
     *
     * @param  string  $userAgent
     * @return string
     */
    protected function detectBrowser($userAgent)
    {
        if (preg_match('/Chrome/', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/Safari/', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Edge/', $userAgent)) {
            return 'Edge';
        }
        return 'Unknown';
    }

    /**
     * Detect platform from user agent.
     *
     * @param  string  $userAgent
     * @return string
     */
    protected function detectPlatform($userAgent)
    {
        if (preg_match('/Windows/', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/Mac/', $userAgent)) {
            return 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/iPhone|iPad/', $userAgent)) {
            return 'iOS';
        }
        return 'Unknown';
    }

    /**
     * Log session activity for security monitoring.
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function logSessionActivity($user, Request $request)
    {
        $deviceInfo = $this->getDeviceInfo($request);
        
        Log::info('User logged in', [
            'user_id' => $user->id,
            'email' => $user->email,
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'device_type' => $deviceInfo['device'],
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'user_agent' => $request->userAgent(),
            'login_time' => now(),
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $loginField = $request->input('login_field');
        $password = $request->input('password');
        
        // If it's an email, try normal login
        if ($this->isEmail($loginField)) {
            return $this->guard()->attempt([
                'email' => $loginField,
                'password' => $password
            ], $request->filled('remember'));
        }
        
        // For mobile numbers, try multiple formats
        $mobileFormats = $this->getMobileFormats($loginField);
        
        foreach ($mobileFormats as $format) {
            if ($this->guard()->attempt([
                'mobile_number' => $format,
                'password' => $password
            ], $request->filled('remember'))) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get all possible mobile number formats for the given input.
     *
     * @param  string  $mobile
     * @return array
     */
    protected function getMobileFormats($mobile)
    {
        // Remove all non-digit characters except +
        $cleanMobile = preg_replace('/[^\d+]/', '', $mobile);
        
        $formats = [];
        
        // Add the original input
        $formats[] = $mobile;
        $formats[] = $cleanMobile;
        
        // If it doesn't start with +
        if (substr($cleanMobile, 0, 1) !== '+') {
            // 10-digit number (9845986798)
            if (strlen($cleanMobile) === 10) {
                $formats[] = '+91' . $cleanMobile;
                $formats[] = '91' . $cleanMobile;
            }
            // 12-digit number starting with 91 (919845986798)
            elseif (strlen($cleanMobile) === 12 && substr($cleanMobile, 0, 2) === '91') {
                $formats[] = '+' . $cleanMobile;
                $formats[] = '+91' . substr($cleanMobile, 2);
            }
            // 13-digit number starting with 919 (919845986798)
            elseif (strlen($cleanMobile) === 13 && substr($cleanMobile, 0, 3) === '919') {
                $formats[] = '+' . $cleanMobile;
            }
        }
        // If it starts with +
        else {
            // Remove + and try without it
            $withoutPlus = substr($cleanMobile, 1);
            $formats[] = $withoutPlus;
            
            // If it's +919845986798, also try 9845986798
            if (strlen($withoutPlus) === 12 && substr($withoutPlus, 0, 2) === '91') {
                $formats[] = substr($withoutPlus, 2);
            }
        }
        
        // Remove duplicates and return
        return array_unique($formats);
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $remainingAttempts = $this->getRemainingAttempts($request);
        
        $message = trans('auth.failed');
        
        if ($remainingAttempts > 0) {
            $message .= sprintf(' You have %d attempt(s) remaining.', $remainingAttempts);
        }

        throw ValidationException::withMessages([
            'login_field' => [$message],
        ]);
    }
}
