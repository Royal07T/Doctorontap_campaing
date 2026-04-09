<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\CareGiver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CareGiverAuthController extends Controller
{
    /**
     * Login caregiver.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $caregiver = CareGiver::where('email', $request->email)->first();

        if (!$caregiver || !Hash::check($request->password, $caregiver->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$caregiver->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before logging in.',
                'requires_verification' => true,
            ], 403);
        }

        // Revoke previous tokens
        $caregiver->tokens()->delete();

        $token = $caregiver->createToken(
            'caregiver-api-token',
            ['caregiver:read', 'caregiver:write', 'vitals:write', 'observations:write'],
            now()->addDays(7)
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'caregiver' => [
                    'id' => $caregiver->id,
                    'first_name' => $caregiver->first_name,
                    'last_name' => $caregiver->last_name,
                    'email' => $caregiver->email,
                    'phone' => $caregiver->phone,
                    'specialization' => $caregiver->specialization ?? null,
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout (revoke current token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated caregiver profile.
     */
    public function profile(Request $request)
    {
        $caregiver = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $caregiver->id,
                'first_name' => $caregiver->first_name,
                'last_name' => $caregiver->last_name,
                'email' => $caregiver->email,
                'phone' => $caregiver->phone,
                'specialization' => $caregiver->specialization ?? null,
                'photo_url' => $caregiver->photo_url,
                'patients_count' => $caregiver->assignedPatients()->count(),
            ],
        ]);
    }

    /**
     * Update caregiver profile.
     */
    public function updateProfile(Request $request)
    {
        $caregiver = $request->user();

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
        ]);

        $caregiver->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
            'data' => $caregiver->fresh(),
        ]);
    }
}
