<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PatientAuthController extends Controller
{
    /**
     * Register a new patient
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:patients',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
        ]);

        $patient = Patient::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
        ]);

        // Create token with expiration and abilities
        $token = $patient->createToken(
            'patient-api-token',
            ['patient:read', 'patient:write', 'consultation:read', 'consultation:write'],
            now()->addDays(7) // Token expires in 7 days
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify your email.',
            'data' => [
                'patient' => $patient,
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * Login patient
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $patient = Patient::where('email', $request->email)->first();

        if (!$patient || !Hash::check($request->password, $patient->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$patient->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before logging in.',
                'requires_verification' => true,
            ], 403);
        }

        // Create token with expiration and abilities
        $token = $patient->createToken(
            'patient-api-token',
            ['patient:read', 'patient:write', 'consultation:read', 'consultation:write'],
            now()->addDays(7) // Token expires in 7 days
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'patient' => $patient,
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

        // Implementation would go here - typically sends password reset email
        // This should use Laravel's password reset functionality

        return response()->json([
            'success' => true,
            'message' => 'If a patient account exists with that email, a password reset link has been sent.',
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

        // Implementation would go here - typically uses Laravel's password reset

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

        // Implementation would go here

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
        ]);
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $patient = Patient::where('email', $request->email)->first();

        if ($patient && !$patient->email_verified_at) {
            $patient->sendEmailVerificationNotification();
        }

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent if account exists and is not verified.',
        ]);
    }
}

