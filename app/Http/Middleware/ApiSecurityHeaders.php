<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiSecurityHeaders
{
    /**
     * Handle an incoming request.
     * 
     * Add security headers to API responses
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only add headers to API routes
        if ($request->is('api/*')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            
            // Remove server information
            $response->headers->remove('X-Powered-By');
            
            // Add API version header
            $response->headers->set('X-API-Version', 'v1');
        }

        return $response;
    }
}

