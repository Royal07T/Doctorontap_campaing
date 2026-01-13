<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthCheck
{
    /**
     * Handle an incoming request.
     * 
     * Ensures API requests have valid authentication token
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if request has Authorization header
        if (!$request->bearerToken() && !$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required. Please provide a valid API token.',
                'error' => 'unauthenticated'
            ], 401)->withHeaders([
                'WWW-Authenticate' => 'Bearer',
            ]);
        }

        return $next($request);
    }
}

