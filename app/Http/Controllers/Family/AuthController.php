<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('family')->check()) {
            return redirect()->route('family.dashboard');
        }

        return view('family.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('family')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $member = Auth::guard('family')->user();

            if (!$member->is_active) {
                Auth::guard('family')->logout();
                return back()->withErrors(['email' => 'Your account has been deactivated.']);
            }

            return redirect()->intended(route('family.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('family')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('family.login');
    }
}
