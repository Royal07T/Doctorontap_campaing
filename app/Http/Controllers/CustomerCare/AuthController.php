<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomerCare;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::guard('customer_care')->check()) {
            return redirect()->route('customer-care.dashboard');
        }
        
        return view('customer-care.login');
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

        // Check if customer care exists and is active
        $customerCare = CustomerCare::where('email', $credentials['email'])->first();
        
        if ($customerCare && !$customerCare->is_active) {
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact the administrator.',
            ])->withInput($request->only('email'));
        }

        if (Auth::guard('customer_care')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Update last login timestamp
            $customerCare = Auth::guard('customer_care')->user();
            $customerCare->last_login_at = now();
            $customerCare->save();
            
            // Check if email is verified
            if (!$customerCare->hasVerifiedEmail()) {
                return redirect()->route('customer-care.verification.notice')
                    ->with('warning', 'Please verify your email address to access your dashboard.');
            }
            
            return redirect()->intended(route('customer-care.dashboard'))
                ->with('success', 'Welcome back, ' . $customerCare->name . '!');
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
        Auth::guard('customer_care')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer-care.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
