<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    /**
     * Show the email verification notice
     */
    public function notice()
    {
        $user = Auth::guard('admin')->user();
        
        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified
     */
    public function verify(Request $request, $id, $hash)
    {
        $admin = \App\Models\AdminUser::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($admin->getEmailForVerification()))) {
            return redirect()->route('admin.verification.notice')
                ->with('error', 'Invalid verification link.');
        }

        if ($admin->hasVerifiedEmail()) {
            return redirect()->route('admin.login')
                ->with('success', 'Your email is already verified. Please login.');
        }

        if ($admin->markEmailAsVerified()) {
            event(new Verified($admin));
        }

        return redirect()->route('admin.login')
            ->with('success', 'Your email has been verified! You can now login.');
    }

    /**
     * Resend the email verification notification
     */
    public function resend(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return redirect()->route('admin.login')
                ->with('error', 'Please login to resend verification email.');
        }

        if ($admin->hasVerifiedEmail()) {
            return redirect()->route('admin.dashboard');
        }

        $admin->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent! Please check your email.');
    }
}
