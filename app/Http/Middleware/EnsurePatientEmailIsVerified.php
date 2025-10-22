<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatientEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $patient = Auth::guard('patient')->user();

        if (!$patient) {
            return redirect()->route('patient.login');
        }

        if (!$patient->hasVerifiedEmail()) {
            return redirect()->route('patient.verification.notice');
        }

        return $next($request);
    }
}
