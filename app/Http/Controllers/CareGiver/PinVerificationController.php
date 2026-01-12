<?php

namespace App\Http\Controllers\CareGiver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PinVerificationController extends Controller
{
    /**
     * Show PIN verification form
     */
    public function show()
    {
        $caregiver = Auth::guard('care_giver')->user();
        
        // If PIN not set, redirect to dashboard with warning
        if (!$caregiver->hasPin()) {
            return redirect()->route('care_giver.dashboard')
                ->with('warning', 'PIN not set. Please contact administrator to set your PIN.');
        }
        
        return view('care-giver.pin-verify');
    }

    /**
     * Verify PIN
     */
    public function verify(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|min:4|max:6',
        ]);

        $caregiver = Auth::guard('care_giver')->user();

        // Verify PIN
        if (!$caregiver->verifyPin($request->pin)) {
            // Log failed attempt
            Log::warning("Caregiver PIN verification failed", [
                'caregiver_id' => $caregiver->id,
                'caregiver_email' => $caregiver->email,
                'ip_address' => $request->ip(),
                'attempts' => Session::get('pin_attempts_' . $caregiver->id, 0) + 1,
            ]);

            // Track attempts (prevent brute force)
            $attemptsKey = 'pin_attempts_' . $caregiver->id;
            $attempts = Session::get($attemptsKey, 0) + 1;
            Session::put($attemptsKey, $attempts);

            // Lock after 5 failed attempts
            if ($attempts >= 5) {
                Session::put('pin_locked_' . $caregiver->id, now()->addMinutes(15));
                Log::alert("Caregiver PIN locked due to multiple failed attempts", [
                    'caregiver_id' => $caregiver->id,
                    'caregiver_email' => $caregiver->email,
                    'ip_address' => $request->ip(),
                    'attempts' => $attempts,
                ]);

                return back()->withErrors([
                    'pin' => 'Too many failed attempts. Please try again in 15 minutes.',
                ]);
            }

            return back()->withErrors([
                'pin' => 'Invalid PIN. Please try again.',
            ])->withInput();
        }

        // PIN verified - store in session
        $pinVerifiedKey = 'caregiver_pin_verified_' . $caregiver->id;
        Session::put($pinVerifiedKey, true);
        
        // Clear attempt counter
        Session::forget('pin_attempts_' . $caregiver->id);

        // Log successful verification
        Log::info("Caregiver PIN verified successfully", [
            'caregiver_id' => $caregiver->id,
            'caregiver_email' => $caregiver->email,
            'ip_address' => $request->ip(),
        ]);

        // Redirect to intended URL or dashboard
        $intendedUrl = Session::pull('caregiver_intended_url', route('care_giver.dashboard'));

        return redirect($intendedUrl)
            ->with('success', 'PIN verified successfully.');
    }
}
