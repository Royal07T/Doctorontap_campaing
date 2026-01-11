<?php

namespace App\Http\Controllers\CareGiver;

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
        $user = Auth::guard('care_giver')->user();
        
        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('care_giver.dashboard');
        }
        
        return view('care-giver.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified
     */
    public function verify(Request $request, $id, $hash)
    {
        $careGiver = \App\Models\CareGiver::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($careGiver->getEmailForVerification()))) {
            return redirect()->route('care_giver.login')
                ->with('error', 'Invalid verification link.');
        }

        if ($careGiver->hasVerifiedEmail()) {
            return redirect()->route('care_giver.login')
                ->with('success', 'Your email is already verified. Please login.');
        }

        if ($careGiver->markEmailAsVerified()) {
            event(new Verified($careGiver));
        }

        return redirect()->route('care_giver.login')
            ->with('success', 'Your email has been verified! You can now login.');
    }

    /**
     * Resend the email verification notification
     */
    public function resend(Request $request)
    {
        $careGiver = Auth::guard('care_giver')->user();
        
        if ($careGiver->hasVerifiedEmail()) {
            return redirect()->route('care_giver.dashboard');
        }

        $careGiver->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent! Please check your email.');
    }
}

