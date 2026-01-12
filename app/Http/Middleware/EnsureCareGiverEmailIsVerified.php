<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCareGiverEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $caregiver = Auth::guard('care_giver')->user();

        if (!$caregiver) {
            return redirect()->route('care_giver.login');
        }

        if (!$caregiver->hasVerifiedEmail()) {
            return redirect()->route('care_giver.verification.notice');
        }

        return $next($request);
    }
}
