<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DomainRouting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $domainType): Response
    {
        // Skip domain checking if multi-domain is disabled
        if (!config('domains.enabled')) {
            return $next($request);
        }

        $expectedDomain = config("domains.domains.{$domainType}");
        $currentHost = $request->getHost();

        // Allow access if on the expected domain
        if ($expectedDomain && $currentHost === $expectedDomain) {
            return $next($request);
        }

        // In production, redirect to the correct domain
        if (app()->environment('production')) {
            $scheme = $request->getScheme();
            $url = "{$scheme}://{$expectedDomain}{$request->getRequestUri()}";
            return redirect($url, 301);
        }

        // In development, just allow access but log a warning
        if (app()->environment(['local', 'development'])) {
            \Log::warning("Domain mismatch: Expected '{$expectedDomain}' but got '{$currentHost}'");
            return $next($request);
        }

        // Default: deny access
        abort(403, 'Access denied: Invalid domain for this route.');
    }
}



