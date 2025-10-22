<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\AdminUser;

class ForgotPasswordController extends Controller
{
    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        return view('admin.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admin_users,email',
        ]);

        // Create a custom password reset token and send email manually
        $admin = AdminUser::where('email', $request->email)->first();
        $token = Str::random(64);
        
        // Store the token in the password reset tokens table
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $admin->email],
            [
                'email' => $admin->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Send the reset email with our custom route
        $resetUrl = route('admin.password.reset', ['token' => $token, 'email' => $admin->email]);
        
        \Illuminate\Support\Facades\Mail::send('emails.password-reset', [
            'user' => $admin,
            'token' => $token,
            'resetUrl' => $resetUrl,
        ], function ($message) use ($admin) {
            $message->to($admin->email)
                   ->subject('Reset Password Notification');
        });

        return back()->with(['status' => 'We have emailed your password reset link.']);
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(Request $request, $token)
    {
        return view('admin.reset-password', [
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

        // Find the admin and update their password
        $admin = AdminUser::where('email', $request->email)->first();
        if (!$admin) {
            return back()->withErrors(['email' => ['User not found.']]);
        }

        $admin->update([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ]);

        // Delete the used token
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('admin.login')->with('status', 'Your password has been reset successfully.');
    }
}

