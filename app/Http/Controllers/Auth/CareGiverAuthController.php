<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CareGiver;
use App\Models\Location;
use App\Models\State;
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
        $states = State::orderBy('name')->get(['id', 'name']);
        return view('auth.caregiver-register', compact('states'));
    }

    /**
     * Get cities (locations) by state
     */
    public function getCitiesByState($stateId)
    {
        $cities = Location::where('state_id', $stateId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
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
            'state_id' => 'required|integer|exists:states,id',
            'city_id' => 'required|integer|exists:locations,id',
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

        $state = State::find($request->state_id);
        $city = Location::where('id', $request->city_id)
            ->where('state_id', $request->state_id)
            ->first();

        if (!$city) {
            return back()->withErrors(['city_id' => 'Please select a valid city for the selected state.'])->withInput();
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
            'city' => $city->name,
            'state' => $state ? $state->name : null,
            'profile_photo_path' => $profilePhotoPath,
            'cv_path' => $cvPath,
            'password' => Hash::make($request->password),
            'is_active' => false, // Pending approval
            'verification_status' => 'pending',
        ]);

        return redirect()->route('care_giver.login')->with('success', 'Application submitted! Please wait for approval.');
    }
}
