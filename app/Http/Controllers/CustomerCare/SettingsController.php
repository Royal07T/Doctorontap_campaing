<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $user = auth()->guard('customer_care')->user();
        return view('customer-care.settings', compact('user'));
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::guard('customer_care')->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Audit log
        Log::info('Customer Care password changed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => 'password_changed',
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
