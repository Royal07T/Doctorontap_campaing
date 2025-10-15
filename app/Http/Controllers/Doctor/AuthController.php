<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::guard('doctor')->check()) {
            return redirect()->route('doctor.dashboard');
        }
        
        return view('doctor.login');
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

        // Check if doctor exists and is approved/active
        $doctor = Doctor::where('email', $credentials['email'])->first();
        
        if ($doctor && !$doctor->is_approved) {
            return back()->withErrors([
                'email' => 'Your account is pending admin approval. You will receive an email when approved.',
            ])->withInput($request->only('email'));
        }
        
        if ($doctor && !$doctor->is_available) {
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact the administrator.',
            ])->withInput($request->only('email'));
        }

        if (Auth::guard('doctor')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Update last login timestamp
            $doctor = Auth::guard('doctor')->user();
            $doctor->last_login_at = now();
            $doctor->save();
            
            // Check if email is verified
            if (!$doctor->hasVerifiedEmail()) {
                return redirect()->route('doctor.verification.notice')
                    ->with('warning', 'Please verify your email address to access your dashboard.');
            }
            
            return redirect()->intended(route('doctor.dashboard'))
                ->with('success', 'Welcome back, Dr. ' . $doctor->name . '!');
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
        Auth::guard('doctor')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('doctor.login')
            ->with('success', 'You have been logged out successfully.');
    }
}

