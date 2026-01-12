<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CareGiverAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('care_giver')->check()) {
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login to access the caregiving module.',
                    'redirect' => route('care_giver.login')
                ], 401);
            }
            
            return redirect()->route('care_giver.login')
                ->with('error', 'Please login to access the caregiving module.');
        }

        // Check if caregiver is active
        $caregiver = Auth::guard('care_giver')->user();
        if (!$caregiver->is_active) {
            Auth::guard('care_giver')->logout();
            
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact the administrator.',
                    'redirect' => route('care_giver.login')
                ], 403);
            }
            
            return redirect()->route('care_giver.login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}
