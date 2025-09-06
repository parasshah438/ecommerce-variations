<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Jobs\SendWelcomeEmail;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        // Smart queue handling for shared hosting
        try {
            if (config('queue.default') === 'database') {
                // Try to dispatch to queue
                SendWelcomeEmail::dispatch($user);
            } else {
                // For sync driver, send immediately
                SendWelcomeEmail::dispatchSync($user);
            }
        } catch (\Exception $e) {
            // If queue fails, log and continue (don't block registration)
            \Log::error('Failed to queue welcome email for user: ' . $user->email . '. Error: ' . $e->getMessage());
        }

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
                    ? new JsonResponse([], 201)
                    : redirect($this->redirectPath())->with('success', 'Welcome! A confirmation email has been sent to your email address.');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'country_code' => ['required', 'string', 'max:5'],
            'mobile_number' => ['required', 'string', 'max:20', 'unique:users'],
            'terms' => ['required', 'accepted'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'country_code' => $data['country_code'],
            'mobile_number' => $data['mobile_number'],
        ]);
    }

    /**
     * Check if email already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        
        if (empty($email)) {
            return response()->json([
                'available' => true,
                'message' => ''
            ]);
        }

        $exists = User::where('email', $email)->exists();
        
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'This email address is already registered.' : 'Email is available.'
        ]);
    }

    /**
     * Check if mobile number already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkMobile(Request $request)
    {
        $mobile = $request->input('mobile_number');
        
        if (empty($mobile)) {
            return response()->json([
                'available' => true,
                'message' => ''
            ]);
        }

        // Normalize mobile number to match database format
        $normalizedMobile = $this->normalizeMobileForCheck($mobile);
        
        // Check all possible formats
        $mobileFormats = $this->getMobileFormatsForCheck($normalizedMobile);
        
        $exists = User::whereIn('mobile_number', $mobileFormats)->exists();
        
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'This mobile number is already registered.' : 'Mobile number is available.'
        ]);
    }

    /**
     * Normalize mobile number for checking.
     *
     * @param  string  $mobile
     * @return string
     */
    protected function normalizeMobileForCheck($mobile)
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
     * Get all possible mobile number formats for checking.
     *
     * @param  string  $mobile
     * @return array
     */
    protected function getMobileFormatsForCheck($mobile)
    {
        // Remove all non-digit characters except +
        $cleanMobile = preg_replace('/[^\d+]/', '', $mobile);
        
        $formats = [];
        
        // Add the normalized format
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
}
