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
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login to access the canvasser area.',
                    'redirect' => route('canvasser.login')
                ], 401);
            }
            
            return redirect()->route('canvasser.login')
                ->with('error', 'Please login to access the canvasser area.');
        }

        // Check if canvasser is active
        $canvasser = Auth::guard('canvasser')->user();
        if (!$canvasser->is_active) {
            Auth::guard('canvasser')->logout();
            
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact the administrator.',
                    'redirect' => route('canvasser.login')
                ], 403);
            }
            
            return redirect()->route('canvasser.login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}

