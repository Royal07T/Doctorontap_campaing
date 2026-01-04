<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerCareEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customerCare = Auth::guard('customer_care')->user();

        if (!$customerCare) {
            return redirect()->route('customer-care.login');
        }

        if (!$customerCare->hasVerifiedEmail()) {
            return redirect()->route('customer-care.verification.notice');
        }

        return $next($request);
    }
}
