<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerCareAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('customer_care')->check()) {
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login to access the customer care area.',
                    'redirect' => route('customer-care.login')
                ], 401);
            }
            
            return redirect()->route('customer-care.login')
                ->with('error', 'Please login to access the customer care area.');
        }

        // Check if customer care is active
        $customerCare = Auth::guard('customer_care')->user();
        if (!$customerCare->is_active) {
            Auth::guard('customer_care')->logout();
            
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact the administrator.',
                    'redirect' => route('customer-care.login')
                ], 403);
            }
            
            return redirect()->route('customer-care.login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}
