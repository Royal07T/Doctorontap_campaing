<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // HIPAA compliance: 15-minute session timeout
        $timeout = config('session.lifetime', 15) * 60; // Convert minutes to seconds

        if (Auth::check()) {
            $lastActivity = session('last_activity_time', time());
            
            // Check if session has expired
            if (time() - $lastActivity > $timeout) {
                // Log the timeout
                \Log::info('Session timeout', [
                    'user_type' => auth()->guard()->name ?? 'unknown',
                    'user_id' => auth()->id(),
                    'last_activity' => date('Y-m-d H:i:s', $lastActivity),
                    'timeout_duration' => $timeout,
                ]);

                // Logout the user
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('message', 'Your session has expired due to inactivity. Please log in again.');
            }
            
            // Update last activity time
            session(['last_activity_time' => time()]);
        }

        return $next($request);
    }
}
