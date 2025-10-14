<?php

namespace App\Http\Controllers\Doctor;

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
        $user = Auth::guard('doctor')->user();
        
        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('doctor.dashboard');
        }
        
        return view('doctor.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified
     */
    public function verify(Request $request, $id, $hash)
    {
        $doctor = \App\Models\Doctor::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($doctor->getEmailForVerification()))) {
            return redirect()->route('doctor.verification.notice')
                ->with('error', 'Invalid verification link.');
        }

        if ($doctor->hasVerifiedEmail()) {
            return redirect()->route('doctor.login')
                ->with('success', 'Your email is already verified. Please login.');
        }

        if ($doctor->markEmailAsVerified()) {
            event(new Verified($doctor));
        }

        return redirect()->route('doctor.login')
            ->with('success', 'Your email has been verified! You can now login.');
    }

    /**
     * Resend the email verification notification
     */
    public function resend(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        
        if ($doctor->hasVerifiedEmail()) {
            return redirect()->route('doctor.dashboard');
        }

        $doctor->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent! Please check your email.');
    }
}

