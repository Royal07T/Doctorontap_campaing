<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionManagement
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check session management if user is authenticated
        if (Auth::check()) {
            // Check session timeout
            if ($this->isSessionExpired($request)) {
                $this->logSessionExpiry($request);
                $this->clearUserSession();
                return $this->redirectToLogin($request);
            }
            
            // Check for concurrent sessions
            if ($this->hasConcurrentSession($request)) {
                $this->logConcurrentSession($request);
                $this->clearUserSession();
                return $this->redirectToLogin($request, 'Another session has been started. Please login again.');
            }
            
            // Update last activity
            $this->updateLastActivity($request);
        }
        
        $response = $next($request);
        
        // Add security headers
        if (Session::isStarted()) {
            $response->headers->set('X-Session-Timeout', config('session.lifetime', 120));
            $response->headers->set('X-Session-Id', Session::getId());
        }
        
        return $response;
    }
    
    /**
     * Check if session has expired
     */
    protected function isSessionExpired(Request $request): bool
    {
        $lastActivity = Session::get('last_activity');
        $lifetime = config('session.lifetime', 120) * 60; // Convert to seconds
        
        if (!$lastActivity) {
            return false; // No session to expire
        }
        
        return (time() - $lastActivity) > $lifetime;
    }
    
    /**
     * Check for concurrent sessions
     */
    protected function hasConcurrentSession(Request $request): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        
        $currentSessionId = Session::getId();
        $storedSessionId = Session::get('user_session_id');
        
        // If no stored session ID, store current one
        if (!$storedSessionId) {
            Session::put('user_session_id', $currentSessionId);
            return false;
        }
        
        // Check if current session matches stored session
        return $currentSessionId !== $storedSessionId;
    }
    
    /**
     * Update last activity timestamp
     */
    protected function updateLastActivity(Request $request): void
    {
        Session::put('last_activity', time());
        
        // Update user's last activity if authenticated
        $user = Auth::user();
        if ($user && method_exists($user, 'updateLastActivity')) {
            $user->updateLastActivity();
        }
    }
    
    /**
     * Clear user session
     */
    protected function clearUserSession(): void
    {
        Auth::logout();
        Session::flush();
    }
    
    /**
     * Redirect to appropriate login page
     */
    protected function redirectToLogin(Request $request, string $message = null): Response
    {
        $message = $message ?? 'Your session has expired. Please login again.';
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'session_expired' => true
            ], 401);
        }
        
        // Determine which login page to redirect to based on current route
        $loginRoute = $this->getLoginRoute($request);
        
        return redirect()->route($loginRoute)
            ->with('error', $message);
    }
    
    /**
     * Get appropriate login route based on current path
     */
    protected function getLoginRoute(Request $request): string
    {
        $path = $request->path();
        
        if (str_starts_with($path, 'admin')) {
            return 'admin.login';
        } elseif (str_starts_with($path, 'doctor')) {
            return 'doctor.login';
        } elseif (str_starts_with($path, 'nurse')) {
            return 'nurse.login';
        } elseif (str_starts_with($path, 'canvasser')) {
            return 'canvasser.login';
        } elseif (str_starts_with($path, 'patient')) {
            return 'patient.login';
        }
        
        return 'consultation.index';
    }
    
    /**
     * Log session expiry
     */
    protected function logSessionExpiry(Request $request): void
    {
        Log::info('Session expired', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'user_id' => Auth::id(),
            'session_id' => Session::getId(),
            'timestamp' => now()
        ]);
    }
    
    /**
     * Log concurrent session detection
     */
    protected function logConcurrentSession(Request $request): void
    {
        Log::warning('Concurrent session detected', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'user_id' => Auth::id(),
            'current_session_id' => Session::getId(),
            'stored_session_id' => Session::get('user_session_id'),
            'timestamp' => now()
        ]);
    }
}
