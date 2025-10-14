<?php

namespace App\Http\Controllers\Canvasser;

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
        $user = Auth::guard('canvasser')->user();
        
        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('canvasser.dashboard');
        }
        
        return view('canvasser.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified
     */
    public function verify(Request $request, $id, $hash)
    {
        $canvasser = \App\Models\Canvasser::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($canvasser->getEmailForVerification()))) {
            return redirect()->route('canvasser.verification.notice')
                ->with('error', 'Invalid verification link.');
        }

        if ($canvasser->hasVerifiedEmail()) {
            return redirect()->route('canvasser.login')
                ->with('success', 'Your email is already verified. Please login.');
        }

        if ($canvasser->markEmailAsVerified()) {
            event(new Verified($canvasser));
        }

        return redirect()->route('canvasser.login')
            ->with('success', 'Your email has been verified! You can now login.');
    }

    /**
     * Resend the email verification notification
     */
    public function resend(Request $request)
    {
        $canvasser = Auth::guard('canvasser')->user();
        
        if ($canvasser->hasVerifiedEmail()) {
            return redirect()->route('canvasser.dashboard');
        }

        $canvasser->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent! Please check your email.');
    }
}

