<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            // Generate login URL based on current request host (for development compatibility)
            $loginUrl = $this->getAdminLoginUrl($request);
            
            // If it's an AJAX request, return JSON response instead of redirect
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login to access the admin area.',
                    'redirect' => $loginUrl
                ], 401);
            }
            
            return redirect($loginUrl)
                ->with('error', 'Please login to access the admin area.');
        }

        return $next($request);
    }
    
    /**
     * Get admin login URL based on current request context
     */
    protected function getAdminLoginUrl(Request $request): string
    {
        return admin_route('admin.login');
    }
}
