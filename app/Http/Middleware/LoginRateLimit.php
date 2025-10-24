<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoginRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        // Stricter rate limiting for login attempts
        $maxAttempts = 5; // 5 attempts per 15 minutes
        $decayMinutes = 15;
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            // Log suspicious login activity
            Log::warning('Login rate limit exceeded - potential brute force attack', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'email' => $request->input('email'),
                'url' => $request->fullUrl(),
                'attempts' => RateLimiter::attempts($key),
                'retry_after' => $seconds,
                'timestamp' => now()
            ]);
            
            return response()->json([
                'message' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
                'retry_after' => $seconds
            ], 429);
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        // Log failed login attempts
        if ($response->getStatusCode() === 401 || $response->getStatusCode() === 422) {
            Log::info('Failed login attempt', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'user_agent' => $request->userAgent(),
                'attempts' => RateLimiter::attempts($key),
                'timestamp' => now()
            ]);
        }
        
        return $response;
    }
    
    /**
     * Resolve the request signature for login rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $ip = $request->ip();
        $email = $request->input('email');
        
        // Rate limit by IP and email combination
        return "login_rate_limit:ip:{$ip}:email:" . ($email ? md5($email) : 'unknown');
    }
}
