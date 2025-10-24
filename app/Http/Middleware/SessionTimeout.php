<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, int $timeout = 120): Response
    {
        $lastActivity = Session::get('last_activity');
        $currentTime = time();
        
        // If this is the first request, set last activity
        if (!$lastActivity) {
            Session::put('last_activity', $currentTime);
            return $next($request);
        }
        
        // Check if session has timed out
        if (($currentTime - $lastActivity) > ($timeout * 60)) {
            Log::info('Session timeout detected', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'timeout_duration' => $timeout,
                'last_activity' => $lastActivity,
                'current_time' => $currentTime,
                'timestamp' => now()
            ]);
            
            Session::flush();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session timeout. Please login again.',
                    'session_timeout' => true
                ], 401);
            }
            
            return redirect()->back()
                ->with('error', 'Your session has timed out. Please login again.');
        }
        
        // Update last activity
        Session::put('last_activity', $currentTime);
        
        $response = $next($request);
        
        // Add session timeout headers
        $response->headers->set('X-Session-Timeout', $timeout);
        $response->headers->set('X-Session-Remaining', $timeout - (($currentTime - $lastActivity) / 60));
        
        return $response;
    }
}
