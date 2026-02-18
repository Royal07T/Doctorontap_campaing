<?php

namespace App\Http\Controllers\CareGiver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show profile page
     */
    public function index()
    {
        $caregiver = Auth::guard('care_giver')->user();

        return view('care-giver.profile.index', compact('caregiver'));
    }

    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        $caregiver = Auth::guard('care_giver')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:care_givers,email,' . $caregiver->id,
        ]);

        $caregiver->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $caregiver = Auth::guard('care_giver')->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', Password::min(8)],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $caregiver->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password
        $caregiver->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    /**
     * Update PIN
     */
    public function updatePin(Request $request)
    {
        $caregiver = Auth::guard('care_giver')->user();

        $request->validate([
            'current_pin' => 'required|string|size:6',
            'pin' => 'required|string|size:6|confirmed',
        ]);

        // Verify current PIN
        if (!$caregiver->verifyPin($request->current_pin)) {
            return back()->withErrors(['current_pin' => 'Current PIN is incorrect.']);
        }

        // Update PIN
        $caregiver->setPin($request->pin);

        return back()->with('success', 'PIN updated successfully!');
    }
}
