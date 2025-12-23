<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Patient;

class ForgotPasswordController extends Controller
{
    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        return view('patient.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if patient exists
        $patient = Patient::where('email', $request->email)->first();
        
        if (!$patient) {
            // Don't reveal if email exists or not for security
            return back()->with(['status' => 'If that email address exists in our system, we have sent you a password reset link.']);
        }

        // Check if email is verified
        if (!$patient->hasVerifiedEmail()) {
            // Send verification email instead of password reset
            $patient->sendEmailVerificationNotification();
            
            return back()->with([
                'status' => 'Your email address is not verified. We have sent you a verification email. Please verify your email first, then you can reset your password.',
                'verification_sent' => true
            ]);
        }

        // Email is verified, proceed with password reset
        $token = Str::random(64);
        
        // Store the token in the password reset tokens table
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $patient->email],
            [
                'email' => $patient->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Send the reset email with our custom route
        $resetUrl = route('patient.password.reset', ['token' => $token, 'email' => $patient->email]);
        
        \Illuminate\Support\Facades\Mail::send('emails.password-reset', [
            'user' => $patient,
            'token' => $token,
            'resetUrl' => $resetUrl,
        ], function ($message) use ($patient) {
            $message->to($patient->email)
                   ->subject('Reset Password Notification');
        });

        return back()->with(['status' => 'We have emailed your password reset link.']);
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(Request $request, $token)
    {
        return view('patient.reset-password', [
            'token' => $token,
            'email' => $request->email
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

        // Find the password reset token
        $passwordReset = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return back()->withErrors(['email' => ['This password reset token is invalid.']]);
        }

        // Check if the token matches
        if (!Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors(['email' => ['This password reset token is invalid.']]);
        }

        // Check if the token has expired (60 minutes)
        if (now()->diffInMinutes($passwordReset->created_at) > 60) {
            return back()->withErrors(['email' => ['This password reset token has expired.']]);
        }

        // Find the patient and update their password
        $patient = Patient::where('email', $request->email)->first();
        if (!$patient) {
            return back()->withErrors(['email' => ['User not found.']]);
        }

        $patient->update([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ]);

        // Delete the used token
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('patient.login')->with('status', 'Your password has been reset successfully.');
    }
}
