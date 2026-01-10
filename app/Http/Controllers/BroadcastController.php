<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\BroadcastController as BaseBroadcastController;

class BroadcastController extends BaseBroadcastController
{
    /**
     * Authenticate the request for channel access.
     * 
     * Override to support multiple guards (admin, doctor, patient, nurse, canvasser)
     */
    public function authenticate(Request $request)
    {
        // Check all guards to find the authenticated user
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            if (app()->environment('local')) {
                Log::warning('Broadcasting auth failed: No authenticated user found', [
                    'guards_checked' => ['admin', 'doctor', 'patient', 'nurse', 'canvasser', 'web'],
                ]);
            }
            return response()->json(['message' => 'Unauthenticated.'], 403);
        }
        
        // Temporarily set the authenticated user for the default guard
        // so Laravel's broadcasting system can use it
        $originalUser = Auth::user();
        Auth::setUser($user);
        
        try {
            // Call parent method which will use Auth::user() (now set to our user)
            $response = parent::authenticate($request);
            
            if (app()->environment('local')) {
                Log::info('Broadcasting auth successful', [
                    'user_id' => $user->id,
                    'user_type' => get_class($user),
                    'channel' => $request->input('channel_name'),
                ]);
            }
            
            return $response;
        } finally {
            // Restore original user
            if ($originalUser) {
                Auth::setUser($originalUser);
            } else {
                Auth::logout();
            }
        }
    }
    
    /**
     * Get the authenticated user from any guard
     */
    protected function getAuthenticatedUser()
    {
        $guards = ['admin', 'doctor', 'patient', 'nurse', 'canvasser', 'web'];
        
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }
        
        return null;
    }
}

