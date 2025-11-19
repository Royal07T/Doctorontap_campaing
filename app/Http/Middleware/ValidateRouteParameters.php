<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateRouteParameters
{
    /**
     * Handle an incoming request.
     * Validate route parameters to prevent injection attacks
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeParams = $request->route() ? $request->route()->parameters() : [];
        
        foreach ($routeParams as $key => $value) {
            // Validate ID parameters (should be integers)
            if (in_array($key, ['id', 'consultationId', 'patientId', 'doctorId', 'nurseId', 'canvasserId', 'vitalSignId', 'paymentId'])) {
                if (!is_numeric($value) || $value < 1) {
                    abort(400, 'Invalid parameter: ' . $key);
                }
            }
            
            // Validate reference parameters (alphanumeric with hyphens)
            if (in_array($key, ['reference'])) {
                if (!preg_match('/^[A-Z0-9\-]+$/', $value)) {
                    abort(400, 'Invalid reference format');
                }
            }
            
            // Check for SQL injection patterns in string parameters
            if (is_string($value)) {
                $dangerous_patterns = [
                    '/union.*select/i',
                    '/select.*from/i',
                    '/insert.*into/i',
                    '/delete.*from/i',
                    '/drop.*table/i',
                    '/update.*set/i',
                    '/exec.*\(/i',
                    '/execute.*\(/i',
                    '/<script/i',
                    '/javascript:/i',
                    '/on\w+\s*=/i', // Event handlers like onclick=
                ];
                
                foreach ($dangerous_patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        \Log::warning('Potential injection attack detected', [
                            'parameter' => $key,
                            'value' => $value,
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'url' => $request->fullUrl()
                        ]);
                        abort(400, 'Invalid parameter format');
                    }
                }
            }
        }
        
        return $next($request);
    }
}

