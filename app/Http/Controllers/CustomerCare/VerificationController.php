<?php

namespace App\Http\Controllers\CustomerCare;

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
        $user = Auth::guard('customer_care')->user();
        
        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('customer-care.dashboard');
        }
        
        return view('customer-care.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified
     */
    public function verify(Request $request, $id, $hash)
    {
        $customerCare = \App\Models\CustomerCare::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($customerCare->getEmailForVerification()))) {
            return redirect()->route('customer-care.verification.notice')
                ->with('error', 'Invalid verification link.');
        }

        if ($customerCare->hasVerifiedEmail()) {
            return redirect()->route('customer-care.login')
                ->with('success', 'Your email is already verified. Please login.');
        }

        if ($customerCare->markEmailAsVerified()) {
            event(new Verified($customerCare));
        }

        return redirect()->route('customer-care.login')
            ->with('success', 'Your email has been verified! You can now login.');
    }

    /**
     * Resend the email verification notification
     */
    public function resend(Request $request)
    {
        $customerCare = Auth::guard('customer_care')->user();
        
        if ($customerCare->hasVerifiedEmail()) {
            return redirect()->route('customer-care.dashboard');
        }

        $customerCare->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent! Please check your email.');
    }
}
