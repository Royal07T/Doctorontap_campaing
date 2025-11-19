<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sanitize all input data
        $input = $request->all();
        
        array_walk_recursive($input, function(&$value) {
            if (is_string($value)) {
                // Remove null bytes
                $value = str_replace(chr(0), '', $value);
                
                // Trim whitespace
                $value = trim($value);
                
                // Remove invisible characters
                $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
                
                // Optional: Strip tags for non-HTML fields (be careful with fields that need HTML)
                // For HTML fields, we'll use Laravel's built-in escaping in views
            }
        });
        
        // Replace the request input with sanitized data
        $request->merge($input);
        
        // Validate and sanitize route parameters
        $routeParams = $request->route() ? $request->route()->parameters() : [];
        
        foreach ($routeParams as $key => $value) {
            if (is_string($value)) {
                // Remove null bytes from route parameters
                $value = str_replace(chr(0), '', $value);
                
                // Remove invisible characters
                $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
                
                // Update route parameter
                if ($request->route()) {
                    $request->route()->setParameter($key, $value);
                }
            }
        }
        
        return $next($request);
    }
}

