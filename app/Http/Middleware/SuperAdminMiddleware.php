<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure user is authenticated via admin guard
        if (!Auth::guard('admin')->check()) {
            Log::warning('Super admin access attempt without authentication', [
                'ip' => $request->ip(),
                'route' => $request->route()?->getName(),
            ]);
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = Auth::guard('admin')->user();

        // Ensure user is super admin
        if (!$user->isSuperAdmin()) {
            Log::warning('Non-super admin attempted to access super admin route', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'ip' => $request->ip(),
                'route' => $request->route()?->getName(),
            ]);
            return response()->json(['message' => 'Forbidden. Super admin access required.'], 403);
        }

        // Log successful access
        Log::info('Super admin route accessed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'route' => $request->route()?->getName(),
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }
}
