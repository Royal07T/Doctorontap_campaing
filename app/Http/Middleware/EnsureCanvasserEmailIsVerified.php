<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanvasserEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $canvasser = Auth::guard('canvasser')->user();

        if (!$canvasser) {
            return redirect()->route('canvasser.login');
        }

        if (!$canvasser->hasVerifiedEmail()) {
            return redirect()->route('canvasser.verification.notice');
        }

        return $next($request);
    }
}
