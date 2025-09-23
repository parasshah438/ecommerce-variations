<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SingleSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = session()->getId();
            
            // Check if current session is valid
            if (!$this->isValidSession($user, $currentSessionId)) {
                $this->logInvalidSession($user, $currentSessionId, $request);
                
                // Logout and redirect to login with message
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'You have been logged out due to login from another device.',
                        'redirect' => route('login')
                    ], 401);
                }
                
                return redirect()->route('login')
                    ->with('warning', 'You have been logged out because you logged in from another device.');
            }
        }
        
        return $next($request);
    }
    
    /**
     * Check if the current session is valid for the user.
     *
     * @param  \App\Models\User  $user
     * @param  string  $currentSessionId
     * @return bool
     */
    protected function isValidSession($user, $currentSessionId)
    {
        // If user doesn't have an active session recorded, allow it
        if (!$user->active_session_id) {
            return true;
        }
        
        // Check if current session matches the stored active session
        return $user->active_session_id === $currentSessionId;
    }
    
    /**
     * Log invalid session attempt.
     *
     * @param  \App\Models\User  $user
     * @param  string  $currentSessionId
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function logInvalidSession($user, $currentSessionId, $request)
    {
        Log::warning('Invalid session detected - User logged out', [
            'user_id' => $user->id,
            'email' => $user->email,
            'current_session' => $currentSessionId,
            'valid_session' => $user->active_session_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
        ]);
    }
}
