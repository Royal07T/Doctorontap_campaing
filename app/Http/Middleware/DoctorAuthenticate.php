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
            return redirect()->route('doctor.login')
                ->with('error', 'Please login to access the doctor area.');
        }

        // Check if doctor is available/active
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor->is_available) {
            Auth::guard('doctor')->logout();
            return redirect()->route('doctor.login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}

