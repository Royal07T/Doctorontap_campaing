<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerformanceHeaders
{
    /**
     * Handle an incoming request and add performance headers
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only add headers for HTML responses
        if ($response->headers->get('Content-Type') && 
            str_contains($response->headers->get('Content-Type'), 'text/html')) {
            
            // Enable gzip compression if not already enabled
            if (function_exists('gzencode') && 
                !$response->headers->has('Content-Encoding') &&
                $request->header('Accept-Encoding') &&
                str_contains($request->header('Accept-Encoding'), 'gzip')) {
                
                $content = $response->getContent();
                if (strlen($content) > 860) { // Only compress if worth it
                    $compressed = gzencode($content, 6); // Compression level 6 is a good balance
                    if ($compressed !== false) {
                        $response->setContent($compressed);
                        $response->headers->set('Content-Encoding', 'gzip');
                        $response->headers->set('Vary', 'Accept-Encoding');
                    }
                }
            }
        }

        // Cache static assets aggressively
        if ($this->isStaticAsset($request)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        // Add security and performance headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        return $response;
    }

    /**
     * Check if request is for a static asset
     */
    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();
        return preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot|webp)$/i', $path);
    }
}

