<?php

namespace App\Http\Controllers\Canvasser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Canvasser;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::guard('canvasser')->check()) {
            return redirect()->route('canvasser.dashboard');
        }
        
        return view('canvasser.login');
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

        // Check if canvasser exists and is active
        $canvasser = Canvasser::where('email', $credentials['email'])->first();
        
        if ($canvasser && !$canvasser->is_active) {
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact the administrator.',
            ])->withInput($request->only('email'));
        }

        if (Auth::guard('canvasser')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Update last login timestamp
            $canvasser = Auth::guard('canvasser')->user();
            $canvasser->last_login_at = now();
            $canvasser->save();
            
            // Check if email is verified
            if (!$canvasser->hasVerifiedEmail()) {
                return redirect()->route('canvasser.verification.notice')
                    ->with('warning', 'Please verify your email address to access your dashboard.');
            }
            
            return redirect()->intended(route('canvasser.dashboard'))
                ->with('success', 'Welcome back, ' . $canvasser->name . '!');
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
        Auth::guard('canvasser')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('canvasser.login')
            ->with('success', 'You have been logged out successfully.');
    }
}

