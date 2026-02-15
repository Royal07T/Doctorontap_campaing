<?php

namespace App\Http\Controllers;

use App\Services\PusherBeamsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PusherBeamsController extends Controller
{
    protected PusherBeamsService $beamsService;

    public function __construct(PusherBeamsService $beamsService)
    {
        $this->beamsService = $beamsService;
    }

    /**
     * Generate Pusher Beams authentication token for the authenticated user
     * This token allows the user's device to be associated with their user ID
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateToken(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        if (!$this->beamsService->isEnabled()) {
            return response()->json([
                'error' => 'Pusher Beams is not enabled',
            ], 503);
        }

        try {
            $userType = $this->getUserType($user);
            $userId = "{$userType}_{$user->id}";
            
            $token = $this->beamsService->generateToken($userId);

            if ($token && isset($token['token'])) {
                return response()->json([
                    'success' => true,
                    'token' => $token['token'],
                ]);
            }

            return response()->json([
                'error' => 'Failed to generate token',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Failed to generate Pusher Beams token', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return response()->json([
                'error' => 'Failed to generate token',
            ], 500);
        }
    }

    /**
     * Get authenticated user from any guard
     */
    private function getAuthenticatedUser()
    {
        // Check all possible guards in order of likelihood
        $guards = ['admin', 'doctor', 'patient', 'nurse', 'canvasser', 'customer_care', 'care_giver', 'web'];
        
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }
        
        // Fallback to default guard
        return Auth::user();
    }

    /**
     * Get user type based on model class
     */
    private function getUserType($user): string
    {
        $class = get_class($user);
        $typeMap = [
            \App\Models\Patient::class => 'patient',
            \App\Models\Doctor::class => 'doctor',
            \App\Models\AdminUser::class => 'admin',
            \App\Models\Nurse::class => 'nurse',
            \App\Models\Canvasser::class => 'canvasser',
            \App\Models\CustomerCare::class => 'customer_care',
        ];

        return $typeMap[$class] ?? 'patient';
    }

    /**
     * Authenticate Pusher Beams user ID request
     * This endpoint is called by Pusher Beams SDK when setting user ID
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auth(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        if (!$this->beamsService->isEnabled()) {
            return response()->json([
                'error' => 'Pusher Beams is not enabled',
            ], 503);
        }

        try {
            // Verify the token from the request
            $token = $request->bearerToken() ?? $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }

            if (!$token) {
                return response()->json([
                    'error' => 'Token required',
                ], 400);
            }

            // Verify token matches the user
            $userType = $this->getUserType($user);
            $expectedUserId = "{$userType}_{$user->id}";
            
            // Generate token to verify
            $expectedToken = $this->beamsService->generateToken($expectedUserId);
            
            if (!$expectedToken || !isset($expectedToken['token'])) {
                return response()->json([
                    'error' => 'Failed to verify token',
                ], 500);
            }

            // In a real implementation, you might want to verify the token more securely
            // For now, we'll trust that if the user is authenticated, they can set their user ID
            // Pusher Beams will validate the token format

            return response()->json([
                'token' => $expectedToken['token'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to authenticate Pusher Beams request', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return response()->json([
                'error' => 'Authentication failed',
            ], 500);
        }
    }
}

