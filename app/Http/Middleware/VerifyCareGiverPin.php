<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class VerifyCareGiverPin
{
    /**
     * Handle an incoming request.
     * 
     * Requires PIN verification before accessing caregiving module.
     * PIN verification state is stored in session for security.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure caregiver is authenticated
        if (!Auth::guard('care_giver')->check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                    'redirect' => route('care_giver.login')
                ], 401);
            }
            
            return redirect()->route('care_giver.login')
                ->with('error', 'Please login to access the caregiving module.');
        }

        $caregiver = Auth::guard('care_giver')->user();

        // Check if caregiver is active
        if (!$caregiver->is_active) {
            Auth::guard('care_giver')->logout();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated.',
                    'redirect' => route('care_giver.login')
                ], 403);
            }
            
            return redirect()->route('care_giver.login')
                ->with('error', 'Your account has been deactivated.');
        }

        // Check if PIN is set
        if (!$caregiver->hasPin()) {
            // PIN not set - allow access but log warning
            // Admin should set PIN during account creation
            Log::warning("Caregiver accessing module without PIN set", [
                'caregiver_id' => $caregiver->id,
                'caregiver_email' => $caregiver->email,
                'route' => $request->route()?->getName(),
                'ip_address' => $request->ip(),
            ]);
            
            // Allow access but flag for admin attention
            return $next($request);
        }

        // Check if PIN is already verified in this session
        $pinVerifiedKey = 'caregiver_pin_verified_' . $caregiver->id;
        $pinVerified = Session::get($pinVerifiedKey, false);

        if ($pinVerified) {
            // PIN already verified in this session
            return $next($request);
        }

        // PIN not verified - redirect to verification page
        // Skip redirect if already on verification route to prevent loop
        if ($request->routeIs('care_giver.pin.verify') || $request->routeIs('care_giver.pin.verify.post')) {
            return $next($request);
        }

        // Store intended URL for redirect after PIN verification
        Session::put('caregiver_intended_url', $request->fullUrl());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'PIN verification required.',
                'redirect' => route('care_giver.pin.verify'),
                'requires_pin' => true,
            ], 403);
        }

        return redirect()->route('care_giver.pin.verify')
            ->with('info', 'Please verify your PIN to continue.');
    }
}
