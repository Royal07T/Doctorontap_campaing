<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CareGiver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CareGiverAuthController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.caregiver-register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:care_givers',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'role' => 'required|string',
            'experience_years' => 'required|integer|min:0',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'profile_photo' => 'nullable|file|image|max:2048',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle File Uploads
        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('caregivers/photos', 'public');
        }

        $cvPath = null;
        if ($request->hasFile('cv_file')) {
            $cvPath = $request->file('cv_file')->store('caregivers/cvs', 'local'); // Store CV privately
        }

        // Create Care Giver
        $caregiver = CareGiver::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'role' => $request->role,
            'experience_years' => $request->experience_years,
            'license_number' => $request->license_number,
            'bio' => $request->bio,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'profile_photo_path' => $profilePhotoPath,
            'cv_path' => $cvPath,
            'password' => Hash::make($request->password),
            'is_active' => false, // Pending approval
            'verification_status' => 'pending',
        ]);

        return redirect()->route('login')->with('success', 'Application submitted! Please wait for approval.');
    }
}
