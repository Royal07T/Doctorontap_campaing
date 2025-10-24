<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientVerificationController extends Controller
{
    /**
     * Verify patient email with token
     */
    public function verify(Request $request, $token)
    {
        $email = $request->query('email');
        
        if (!$email) {
            return view('patient.verification-failed', [
                'message' => 'Invalid verification link. Email parameter is missing.'
            ]);
        }

        $patient = Patient::where('email', $email)
                         ->where('email_verification_token', $token)
                         ->first();

        if (!$patient) {
            return view('patient.verification-failed', [
                'message' => 'Invalid verification link. The token may have expired or is invalid.'
            ]);
        }

        // Check if already verified
        if ($patient->isEmailVerified()) {
            return view('patient.verification-success', [
                'message' => 'Your email has already been verified!',
                'patient' => $patient
            ]);
        }

        // Verify the email
        if ($patient->verifyEmail($token)) {
            return view('patient.verification-success', [
                'message' => 'Your email has been successfully verified! You can now access all our services.',
                'patient' => $patient
            ]);
        }

        return view('patient.verification-failed', [
            'message' => 'Verification failed. Please try again or contact support.'
        ]);
    }
}