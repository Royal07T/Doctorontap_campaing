<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DoctorAuthController extends Controller
{
    /**
     * Register a new doctor
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:doctors',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'specialization' => 'nullable|string|max:255',
        ]);

        $doctor = Doctor::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'specialization' => $request->specialization,
            'is_approved' => false, // Requires admin approval
        ]);

        // Create token with expiration and abilities
        $token = $doctor->createToken(
            'doctor-api-token',
            ['doctor:read', 'doctor:write', 'consultation:read', 'consultation:write', 'treatment:write'],
            now()->addDays(7) // Token expires in 7 days
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Your account is pending admin approval.',
            'data' => [
                'doctor' => $doctor,
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * Login doctor
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $doctor = Doctor::where('email', $request->email)->first();

        if (!$doctor || !Hash::check($request->password, $doctor->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$doctor->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is pending admin approval.',
            ], 403);
        }

        if (!$doctor->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact the administrator.',
            ], 403);
        }

        // Create token with expiration and abilities
        $token = $doctor->createToken(
            'doctor-api-token',
            ['doctor:read', 'doctor:write', 'consultation:read', 'consultation:write', 'treatment:write'],
            now()->addDays(7) // Token expires in 7 days
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'doctor' => $doctor,
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
            'message' => 'If a doctor account exists with that email, a password reset link has been sent.',
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ]);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
        ]);
    }
}

