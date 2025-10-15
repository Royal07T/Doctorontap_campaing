<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNurseEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nurse = Auth::guard('nurse')->user();

        if (!$nurse) {
            return redirect()->route('nurse.login');
        }

        if (!$nurse->hasVerifiedEmail()) {
            return redirect()->route('nurse.verification.notice');
        }

        return $next($request);
    }
}
