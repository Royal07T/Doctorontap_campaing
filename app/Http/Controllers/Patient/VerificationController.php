<?php

namespace App\Http\Controllers\Patient;

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
        $user = Auth::guard('patient')->user();
        
        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('patient.dashboard');
        }
        
        return view('patient.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified
     */
    public function verify(Request $request, $id, $hash)
    {
        $patient = \App\Models\Patient::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($patient->getEmailForVerification()))) {
            return redirect()->route('patient.verification.notice')
                ->with('error', 'Invalid verification link.');
        }

        if ($patient->hasVerifiedEmail()) {
            return redirect()->route('patient.login')
                ->with('success', 'Your email is already verified. Please login.');
        }

        if ($patient->markEmailAsVerified()) {
            event(new Verified($patient));
        }

        return redirect()->route('patient.login')
            ->with('success', 'Your email has been verified! You can now login.');
    }

    /**
     * Resend the email verification notification
     */
    public function resend(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        
        if ($patient->hasVerifiedEmail()) {
            return redirect()->route('patient.dashboard');
        }

        $patient->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent! Please check your email.');
    }
}
