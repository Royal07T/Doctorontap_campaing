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
            return redirect()->route('nurse.login')
                ->with('error', 'Please login to access the nurse area.');
        }

        // Check if nurse is active
        $nurse = Auth::guard('nurse')->user();
        if (!$nurse->is_active) {
            Auth::guard('nurse')->logout();
            return redirect()->route('nurse.login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}

