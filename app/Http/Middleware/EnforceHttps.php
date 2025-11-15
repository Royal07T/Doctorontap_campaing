<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce HTTPS in production
        if (!app()->environment('local', 'testing') && !$request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}

