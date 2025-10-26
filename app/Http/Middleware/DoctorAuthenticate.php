<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DoctorAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('doctor')->check()) {
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login to access the doctor area.',
                    'redirect' => route('doctor.login')
                ], 401);
            }
            
            return redirect()->route('doctor.login')
                ->with('error', 'Please login to access the doctor area.');
        }

        // Check if doctor is approved
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor->is_approved) {
            Auth::guard('doctor')->logout();
            
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is pending approval. Please wait for admin approval.',
                    'redirect' => route('doctor.login')
                ], 403);
            }
            
            return redirect()->route('doctor.login')
                ->with('error', 'Your account is pending approval. Please wait for admin approval.');
        }

        // Check if doctor is available/active
        if (!$doctor->is_available) {
            Auth::guard('doctor')->logout();
            
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact the administrator.',
                    'redirect' => route('doctor.login')
                ], 403);
            }
            
            return redirect()->route('doctor.login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}

