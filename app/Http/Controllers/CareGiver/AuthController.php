<?php

namespace App\Http\Controllers\CareGiver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CareGiver;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::guard('care_giver')->check()) {
            return redirect()->route('care_giver.dashboard');
        }
        
        return view('care-giver.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Check if care giver exists and is active
        $careGiver = CareGiver::where('email', $credentials['email'])->first();
        
        if ($careGiver && !$careGiver->is_active) {
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact an administrator.',
            ])->withInput($request->only('email'));
        }

        if ($careGiver && !$careGiver->email_verified_at) {
            return back()->withErrors([
                'email' => 'Please verify your email address before logging in.',
            ])->withInput($request->only('email'))->with('verification_required', true)->with('verification_email', $credentials['email']);
        }

        if (Auth::guard('care_giver')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Update last login timestamp
            $careGiver = Auth::guard('care_giver')->user();
            $careGiver->update(['last_login_at' => now()]);
            
            return redirect()->intended(route('care_giver.dashboard'))
                ->with('success', 'Welcome back, ' . $careGiver->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::guard('care_giver')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('care_giver.login')
            ->with('success', 'You have been logged out successfully.');
    }
}

