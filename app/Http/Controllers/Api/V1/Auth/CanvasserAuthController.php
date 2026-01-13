<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Canvasser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CanvasserAuthController extends Controller
{
    /**
     * Login canvasser
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $canvasser = Canvasser::where('email', $request->email)->first();

        if (!$canvasser || !Hash::check($request->password, $canvasser->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$canvasser->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        // Create token with expiration and abilities
        $token = $canvasser->createToken(
            'canvasser-api-token',
            ['canvasser:read', 'canvasser:write', 'patient:read', 'patient:write', 'consultation:write'],
            now()->addDays(7) // Token expires in 7 days
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'canvasser' => $canvasser,
                'token' => $token,
            ]
        ]);
    }
}

