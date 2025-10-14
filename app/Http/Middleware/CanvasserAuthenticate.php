<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CanvasserAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('canvasser')->check()) {
            return redirect()->route('canvasser.login')
                ->with('error', 'Please login to access the canvasser area.');
        }

        // Check if canvasser is active
        $canvasser = Auth::guard('canvasser')->user();
        if (!$canvasser->is_active) {
            Auth::guard('canvasser')->logout();
            return redirect()->route('canvasser.login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}

