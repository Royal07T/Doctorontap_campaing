<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Patient;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::guard('patient')->check()) {
            return redirect()->route('patient.dashboard');
        }
        
        return view('patient.login');
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

        // Check if patient exists and is active
        $patient = Patient::where('email', $credentials['email'])->first();
        
        if ($patient && !$patient->email_verified_at) {
            return back()->withErrors([
                'email' => 'Please verify your email address before logging in.',
            ])->withInput($request->only('email'))->with('verification_required', true)->with('verification_email', $credentials['email']);
        }

        if (Auth::guard('patient')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('patient.dashboard'))
                ->with('success', 'Welcome back, ' . $patient->name . '!');
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
        Auth::guard('patient')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('patient.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show dashboard
     */
    public function dashboard()
    {
        $patient = Auth::guard('patient')->user();
        
        return view('patient.dashboard', compact('patient'));
    }
}
