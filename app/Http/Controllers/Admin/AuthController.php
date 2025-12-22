<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminUser;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.login');
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

        // Check if admin exists and is active
        $admin = AdminUser::where('email', $credentials['email'])->first();
        
        if ($admin && !$admin->is_active) {
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact the administrator.',
            ])->withInput($request->only('email'));
        }

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Update last login timestamp
            $admin = Auth::guard('admin')->user();
            $admin->last_login_at = now();
            $admin->save();
            
            // Check if email is verified, if not redirect to verification page
            if (!$admin->hasVerifiedEmail()) {
                return redirect()->route('admin.verification.notice')
                    ->with('warning', 'Please verify your email address to access all features.');
            }
            
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Welcome back, ' . $admin->name . '!');
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
        Auth::guard('admin')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
