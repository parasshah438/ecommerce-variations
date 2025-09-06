<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\User;

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
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'login_field';
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
        // You can add any post-login logic here
        return redirect()->intended($this->redirectPath());
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
        throw ValidationException::withMessages([
            'login_field' => [trans('auth.failed')],
        ]);
    }
}
