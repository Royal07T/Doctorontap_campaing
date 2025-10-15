<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDoctorEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $doctor = Auth::guard('doctor')->user();

        if (!$doctor) {
            return redirect()->route('doctor.login');
        }

        if (!$doctor->hasVerifiedEmail()) {
            return redirect()->route('doctor.verification.notice');
        }

        return $next($request);
    }
}
