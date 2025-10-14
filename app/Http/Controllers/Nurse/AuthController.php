<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Nurse;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::guard('nurse')->check()) {
            return redirect()->route('nurse.dashboard');
        }
        
        return view('nurse.login');
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

        // Check if nurse exists and is active
        $nurse = Nurse::where('email', $credentials['email'])->first();
        
        if ($nurse && !$nurse->is_active) {
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact the administrator.',
            ])->withInput($request->only('email'));
        }

        if (Auth::guard('nurse')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Update last login timestamp
            $nurse = Auth::guard('nurse')->user();
            $nurse->last_login_at = now();
            $nurse->save();
            
            // Check if email is verified
            if (!$nurse->hasVerifiedEmail()) {
                return redirect()->route('nurse.verification.notice')
                    ->with('warning', 'Please verify your email address to access your dashboard.');
            }
            
            return redirect()->intended(route('nurse.dashboard'))
                ->with('success', 'Welcome back, ' . $nurse->name . '!');
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
        Auth::guard('nurse')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('nurse.login')
            ->with('success', 'You have been logged out successfully.');
    }
}

