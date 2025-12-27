<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\Location;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        $specialties = Specialty::active()->orderBy('name')->get();
        $states = State::orderBy('name')->get();
        return view('doctor.register', compact('specialties', 'states'));
    }
    
    /**
     * Get cities by state
     */
    public function getCitiesByState($stateId)
    {
        $cities = Location::where('state_id', $stateId)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($cities);
    }

    /**
     * Handle the registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|min:2|max:255|regex:/^[a-zA-Z\s]+$/',
            'last_name' => 'required|string|min:2|max:255|regex:/^[a-zA-Z\s]+$/',
            'gender' => 'required|in:male,female',
            'phone' => 'required|string|min:10|max:20|regex:/^[0-9+\s\-\(\)]+$/',
            'email' => 'required|email|max:255|unique:doctors,email',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'specialization' => 'required|string|exists:specialties,name',
            'experience' => 'required|string|min:3|max:255',
            'consultation_fee' => 'required|numeric|min:500|max:1000000',
            'place_of_work' => 'required|string|min:3|max:255',
            'role' => 'required|in:clinical,non-clinical',
            'state' => 'required|exists:states,id',
            'location' => 'required|string|exists:locations,name',
            'mdcn_license_current' => 'required|in:yes,no,processing',
            'languages' => 'required|string|min:3|max:255',
            'days_of_availability' => 'required|string|min:10|max:1000',
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            // Custom error messages
            'first_name.required' => 'Please enter your first name.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'first_name.regex' => 'First name should only contain letters.',
            'last_name.required' => 'Please enter your last name.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'last_name.regex' => 'Last name should only contain letters.',
            'gender.required' => 'Please select your gender.',
            'phone.required' => 'Please enter your phone number.',
            'phone.min' => 'Phone number must be at least 10 digits.',
            'phone.regex' => 'Please enter a valid phone number.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered. Please use another email or login.',
            'password.required' => 'Please create a password.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'specialization.required' => 'Please select your medical specialty.',
            'specialization.exists' => 'Please select a valid medical specialty.',
            'experience.required' => 'Please enter your years of experience.',
            'experience.min' => 'Experience must be at least 3 characters.',
            'consultation_fee.required' => 'Please enter your consultation fee.',
            'consultation_fee.min' => 'Consultation fee must be at least ₦500.',
            'consultation_fee.max' => 'Consultation fee cannot exceed ₦1,000,000.',
            'place_of_work.required' => 'Please enter your current place of work.',
            'place_of_work.min' => 'Place of work must be at least 3 characters.',
            'role.required' => 'Please select your role.',
            'location.required' => 'Please select your location.',
            'location.exists' => 'Please select a valid location.',
            'mdcn_license_current.required' => 'Please select your MDCN license status.',
            'languages.required' => 'Please enter languages you speak.',
            'languages.min' => 'Languages must be at least 3 characters.',
            'days_of_availability.required' => 'Please enter your days of availability.',
            'days_of_availability.min' => 'Days of availability must be at least 10 characters.',
            'certificate.required' => 'MDCN certificate upload is required for registration.',
            'certificate.mimes' => 'Certificate must be a PDF, JPG, JPEG, or PNG file.',
            'certificate.max' => 'Certificate file size must not exceed 5MB.',
        ]);

        // Handle certificate upload - Store securely in private storage
        $certificatePath = null;
        $certificateData = null;
        $certificateMimeType = null;
        $certificateOriginalName = null;
        
        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            
            // Store file securely in private storage (not public)
            $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
            $certificatePath = $file->storeAs('doctor-certificates', $fileName); // Uses 'local' disk by default (private)
            
            // Store file content in database as base64 (for quick viewing without disk access)
            $certificateData = base64_encode(file_get_contents($file->getRealPath()));
            $certificateMimeType = $file->getMimeType();
            $certificateOriginalName = $file->getClientOriginalName();
        }

        // Create the doctor account
        $doctor = Doctor::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'gender' => $validated['gender'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'specialization' => $validated['specialization'],
            'experience' => $validated['experience'],
            'consultation_fee' => $validated['consultation_fee'],
            'place_of_work' => $validated['place_of_work'],
            'role' => $validated['role'],
            'location' => $validated['location'],
            'mdcn_license_current' => $validated['mdcn_license_current'] === 'yes',
            'languages' => $validated['languages'],
            'days_of_availability' => $validated['days_of_availability'],
            'certificate_path' => $certificatePath,
            'certificate_data' => $certificateData,
            'certificate_mime_type' => $certificateMimeType,
            'certificate_original_name' => $certificateOriginalName,
            'mdcn_certificate_verified' => false, // Requires admin verification
            'is_available' => false, // Not available until approved
            'is_approved' => false, // Needs admin approval
            'use_default_fee' => false, // Doctor sets their own fee initially
        ]);

        // Send email verification notification
        $doctor->sendEmailVerificationNotification();

        return redirect()->route('doctor.registration.success')
            ->with('success', 'Registration successful! Please check your email to verify your account. Your application will be reviewed by our admin team.');
    }

    /**
     * Show success page
     */
    public function success()
    {
        return view('doctor.registration-success');
    }
}
