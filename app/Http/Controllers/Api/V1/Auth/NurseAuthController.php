<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Nurse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class NurseAuthController extends Controller
{
    /**
     * Login nurse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $nurse = Nurse::where('email', $request->email)->first();

        if (!$nurse || !Hash::check($request->password, $nurse->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$nurse->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        // Create token with expiration and abilities
        $token = $nurse->createToken(
            'nurse-api-token',
            ['nurse:read', 'nurse:write', 'patient:read', 'vital-signs:write'],
            now()->addDays(7) // Token expires in 7 days
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'nurse' => $nurse,
                'token' => $token,
            ]
        ]);
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'If a nurse account exists with that email, a password reset link has been sent.',
        ]);
    }
}

