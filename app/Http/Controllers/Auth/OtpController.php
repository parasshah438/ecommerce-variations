<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show OTP login form
     */
    public function showOtpForm(Request $request)
    {
        $email = $request->get('email');
        return view('auth.otp.login', compact('email'));
    }

    /**
     * Send OTP to email
     */
    public function sendOtp(Request $request)
    {
        // Get the input - could be email or mobile_number
        $identifier = $request->input('email');
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $validator = Validator::make(
                ['email' => $identifier],
                ['email' => 'required|email']
            );
            $channelType = 'email';

            $user = \App\Models\User::where('email', $identifier)->first();
            if (!$user) {
                return back()
                ->withErrors(['identifier' => "This {$channelType} is not registered with us."])
                ->withInput();
            }
            
        } elseif (is_numeric($identifier)) {
            $validator = Validator::make(
                ['mobile_number' => $identifier],
                ['mobile_number' => 'required']
            );
            $channelType = 'mobile';

            $normalizedMobile = $this->normalizeMobileNumber($identifier);
            $user = \App\Models\User::where('mobile_number', $normalizedMobile)->first();

            if (!$user) {
                return back()
                ->withErrors(['identifier' => "This {$channelType} is not registered with us."])
                ->withInput();
            }

        } else {
            // No valid input provided
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide either email or mobile number.'
                ], 422);
            }

           
            
            return redirect()->back()
                ->with('error', 'Please provide either email or mobile number.')
                ->withInput();
        }

        if ($validator->fails()) {
            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // For regular form submissions, redirect back with errors
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $testMode = config('app.debug', false); // Use test mode in debug environment

        try {
            $result = $this->otpService->sendOtp($identifier, $channelType, $testMode);

            if ($result['success']) {
                // Store identifier in session for verification
                Session::put('otp_email', $identifier);
                Session::put('otp_sent_at', now());

                $message = $result['message'];
                if ($testMode && isset($result['test_otp'])) {
                    $message .= " (Test OTP: " . $result['test_otp'] . ")";
                }

                // Check if it's an AJAX request
                if ($request->expectsJson() || $request->ajax()) {
                    $response = [
                        'success' => true,
                        'message' => $result['message'],
                        'expires_in_minutes' => $result['expires_in_minutes'],
                        'expires_at' => $result['expires_at']->format('Y-m-d H:i:s')
                    ];

                    // Include test OTP in debug mode
                    if ($testMode && isset($result['test_otp'])) {
                        $response['test_otp'] = $result['test_otp'];
                    }

                    return response()->json($response);
                }

                // For regular form submissions, redirect to verification page
                return redirect()->route('otp.verify.form')
                    ->with('success', $message)
                    ->with('otp_sent', true);
            }

            // Log the actual error for debugging
            Log::warning('OTP send failed in controller', [
                'identifier' => $identifier,
                'channel_type' => $channelType,
                'result' => $result
            ]);

            $errorMessage = $result['message'];
            if ($result['type'] === 'cooldown' && isset($result['remaining_seconds'])) {
                $minutes = floor($result['remaining_seconds'] / 60);
                $seconds = $result['remaining_seconds'] % 60;
                $errorMessage .= " (Wait: {$minutes}m {$seconds}s)";
            }

            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'type' => $result['type'] ?? 'error',
                    'remaining_seconds' => $result['remaining_seconds'] ?? null
                ], 400);
            }

            // For regular form submissions, redirect back with error
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();

        } catch (\Exception $e) {
            Log::error('OTP sending failed in controller', [
                'identifier' => $identifier,
                'channel_type' => $channelType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $errorMessage = 'Something went wrong. Please try again later.';
            if (config('app.debug')) {
                $errorMessage .= ' Error: ' . $e->getMessage();
            }

            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again later.',
                    'debug_error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            // For regular form submissions, redirect back with error
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    /**
     * Verify OTP and login user
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a valid 6-digit OTP.',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // For regular form submissions, redirect back with errors
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $email = Session::get('otp_email');
        if (!$email) {
            $errorMessage = 'Session expired. Please request a new OTP.';
            
            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 400);
            }
            
            // For regular form submissions, redirect to login with error
            return redirect()->route('otp.login')
                ->with('error', $errorMessage);
        }

        try {
            $result = $this->otpService->verifyOtp($email, $request->otp);

            if ($result['success']) {
                // Find and login user
                $user = User::where('email', $email)->first();
                if ($user) {
                    Auth::login($user);
                    
                    // Clear session
                    Session::forget(['otp_email', 'otp_sent_at']);
                    
                    // Log successful login
                    Log::info('User logged in via OTP', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'ip' => $request->ip()
                    ]);

                    // Check if it's an AJAX request
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Login successful! Redirecting...',
                            'redirect_url' => route('dashboard')
                        ]);
                    }
                    
                    // For regular form submissions, redirect to dashboard
                    return redirect()->route('dashboard')
                        ->with('success', 'Login successful! Welcome back.');
                }

                $errorMessage = 'User account not found.';
                
                // Check if it's an AJAX request
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 404);
                }
                
                // For regular form submissions, redirect back with error
                return redirect()->back()
                    ->with('error', $errorMessage)
                    ->withInput();
            }

            $errorMessage = $result['message'];
            if (isset($result['attempts_remaining'])) {
                $errorMessage .= " ({$result['attempts_remaining']} attempts remaining)";
            }

            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'attempts_remaining' => $result['attempts_remaining'] ?? null
                ], 400);
            }
            
            // For regular form submissions, redirect back with error
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();

        } catch (\Exception $e) {
            Log::error('OTP verification failed in controller', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            $errorMessage = 'Something went wrong. Please try again later.';
            if (config('app.debug')) {
                $errorMessage .= ' Error: ' . $e->getMessage();
            }

            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again later.',
                    'debug_error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            
            // For regular form submissions, redirect back with error
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $email = Session::get('otp_email');
        if (!$email) {
            $errorMessage = 'Session expired. Please start over.';
            
            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 400);
            }
            
            // For regular form submissions, redirect to login
            return redirect()->route('otp.login')
                ->with('error', $errorMessage);
        }

        $testMode = config('app.debug', false);

        try {
            $result = $this->otpService->resendOtp($email, 'email', $testMode);

            if ($result['success']) {
                Session::put('otp_sent_at', now());

                $message = 'OTP resent successfully';
                if ($testMode && isset($result['test_otp'])) {
                    $message .= " (Test OTP: " . $result['test_otp'] . ")";
                }

                // Check if it's an AJAX request
                if ($request->expectsJson() || $request->ajax()) {
                    $response = [
                        'success' => true,
                        'message' => 'OTP resent successfully',
                        'expires_in_minutes' => $result['expires_in_minutes'],
                        'expires_at' => $result['expires_at']->format('Y-m-d H:i:s')
                    ];

                    // Include test OTP in debug mode
                    if ($testMode && isset($result['test_otp'])) {
                        $response['test_otp'] = $result['test_otp'];
                    }

                    return response()->json($response);
                }
                
                // For regular form submissions, redirect back with success
                return redirect()->back()
                    ->with('success', $message);
            }

            $errorMessage = $result['message'];
            if ($result['type'] === 'cooldown' && isset($result['remaining_seconds'])) {
                $minutes = floor($result['remaining_seconds'] / 60);
                $seconds = $result['remaining_seconds'] % 60;
                $errorMessage .= " (Wait: {$minutes}m {$seconds}s)";
            }

            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'type' => $result['type'] ?? 'error',
                    'remaining_seconds' => $result['remaining_seconds'] ?? null
                ], 400);
            }
            
            // For regular form submissions, redirect back with error
            return redirect()->back()
                ->with('error', $errorMessage);

        } catch (\Exception $e) {
            Log::error('OTP resend failed in controller', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            $errorMessage = 'Failed to resend OTP. Please try again later.';
            if (config('app.debug')) {
                $errorMessage .= ' Error: ' . $e->getMessage();
            }

            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to resend OTP. Please try again later.',
                    'debug_error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            
            // For regular form submissions, redirect back with error
            return redirect()->back()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Get OTP status for current session
     */
    public function getOtpStatus(Request $request)
    {
        $email = Session::get('otp_email');
        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'No active OTP session'
            ], 400);
        }

        try {
            $status = $this->otpService->getOtpStatus($email);
            return response()->json([
                'success' => true,
                'status' => $status,
                'email' => $email
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get OTP status'
            ], 500);
        }
    }

    /**
     * Cancel OTP session
     */
    public function cancelOtp(Request $request)
    {
        $email = Session::get('otp_email');
        
        if ($email) {
            // Expire current OTP
            $this->otpService->expireOtp($email);
        }

        // Clear session
        Session::forget(['otp_email', 'otp_sent_at']);

        return response()->json([
            'success' => true,
            'message' => 'OTP session cancelled'
        ]);
    }

    /**
     * Show OTP verification form (after email is submitted)
     */
    public function showVerifyForm()
    {
        $email = Session::get('otp_email');
        if (!$email) {
            return redirect()->route('otp.login')->with('error', 'Session expired. Please start over.');
        }

        $status = $this->otpService->getOtpStatus($email);
        
        return view('auth.otp.verify', [
            'email' => $email,
            'status' => $status
        ]);
    }

    /**
     * Handle GET request to /otp/send (redirect to login with message)
     */
    public function redirectToLogin()
    {
        return redirect()->route('otp.login')->with('info', 'Please enter your email address to receive an OTP.');
    }

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
}
