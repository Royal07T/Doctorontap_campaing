<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class NurseAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('nurse')->check()) {
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login to access the nurse area.',
                    'redirect' => route('nurse.login')
                ], 401);
            }
            
            return redirect()->route('nurse.login')
                ->with('error', 'Please login to access the nurse area.');
        }

        // Check if nurse is active
        $nurse = Auth::guard('nurse')->user();
        if (!$nurse->is_active) {
            Auth::guard('nurse')->logout();
            
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact the administrator.',
                    'redirect' => route('nurse.login')
                ], 403);
            }
            
            return redirect()->route('nurse.login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}

