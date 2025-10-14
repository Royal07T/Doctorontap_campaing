<?php

namespace App\Http\Controllers\Nurse;

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
        $user = Auth::guard('nurse')->user();
        
        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('nurse.dashboard');
        }
        
        return view('nurse.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified
     */
    public function verify(Request $request, $id, $hash)
    {
        $nurse = \App\Models\Nurse::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($nurse->getEmailForVerification()))) {
            return redirect()->route('nurse.verification.notice')
                ->with('error', 'Invalid verification link.');
        }

        if ($nurse->hasVerifiedEmail()) {
            return redirect()->route('nurse.login')
                ->with('success', 'Your email is already verified. Please login.');
        }

        if ($nurse->markEmailAsVerified()) {
            event(new Verified($nurse));
        }

        return redirect()->route('nurse.login')
            ->with('success', 'Your email has been verified! You can now login.');
    }

    /**
     * Resend the email verification notification
     */
    public function resend(Request $request)
    {
        $nurse = Auth::guard('nurse')->user();
        
        if ($nurse->hasVerifiedEmail()) {
            return redirect()->route('nurse.dashboard');
        }

        $nurse->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent! Please check your email.');
    }
}

