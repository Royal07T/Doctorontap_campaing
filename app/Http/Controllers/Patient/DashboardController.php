<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\PatientMedicalHistory;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display patient dashboard
     */
    public function index()
    {
        $patient = Auth::guard('patient')->user();
        
        // Statistics
        $stats = [
            'total_consultations' => $patient->consultations()->count(),
            'completed_consultations' => $patient->consultations()->where('status', 'completed')->count(),
            'pending_consultations' => $patient->consultations()->where('status', 'pending')->count(),
            'total_paid' => $patient->consultations()
                ->where('payment_status', 'paid')
                ->with('payment')
                ->get()
                ->sum(function($consultation) {
                    return $consultation->payment ? $consultation->payment->amount : 0;
                }),
            'unpaid_consultations' => $patient->consultations()
                ->where('status', 'completed')
                ->where('payment_status', '!=', 'paid')
                ->count(),
        ];

        // Recent consultations
        $recentConsultations = $patient->consultations()
            ->with(['doctor', 'payment'])
            ->latest()
            ->limit(5)
            ->get();

        // Dependents (if patient is a guardian)
        $dependents = $patient->dependents()->get();

        // Upcoming/pending consultations
        $upcomingConsultations = $patient->consultations()
            ->whereIn('status', ['pending', 'scheduled'])
            ->latest()
            ->limit(3)
            ->get();

        // Get all active specialties from database for carousel
        $specializations = Specialty::active()
            ->orderBy('name')
            ->pluck('name');
        
        // If no specialties found in database, fallback to doctor specializations
        if ($specializations->isEmpty()) {
            $specializations = \App\Models\Doctor::whereNotNull('specialization')
                ->where('specialization', '!=', '')
                ->where('is_approved', true)
                ->distinct()
                ->orderBy('specialization')
                ->pluck('specialization');
        }

        // Symptoms with their related specializations (mapped to database specialties)
        $symptoms = [
            ['name' => 'Menstruation Flow', 'specialization' => 'Obstetrics & Gynecology (OB/GYN)', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['name' => 'Rashes', 'specialization' => 'Dermatology', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
            ['name' => 'Headache', 'specialization' => 'Neurology', 'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
            ['name' => 'Cough', 'specialization' => 'Internal Medicine', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['name' => 'Fever', 'specialization' => 'General Practice (Family Medicine)', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['name' => 'Stomach Pain', 'specialization' => 'Gastroenterology', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['name' => 'Back Pain', 'specialization' => 'Orthopaedics', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['name' => 'Eye Problems', 'specialization' => 'Ophthalmology', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ['name' => 'Ear Pain', 'specialization' => 'ENT (Otolaryngology)', 'icon' => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'],
            ['name' => 'Joint Pain', 'specialization' => 'Orthopaedics', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['name' => 'Skin Issues', 'specialization' => 'Dermatology', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
            ['name' => 'Chest Pain', 'specialization' => 'Cardiology', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
        ];

        return view('patient.dashboard', compact('patient', 'stats', 'recentConsultations', 'dependents', 'upcomingConsultations', 'specializations', 'symptoms'));
    }

    /**
     * Display doctors by specialization
     */
    public function doctorsBySpecialization($specialization)
    {
        $doctors = \App\Models\Doctor::where('specialization', $specialization)
            ->where('is_approved', true)
            ->get();

        return view('patient.doctors-by-specialization', compact('doctors', 'specialization'));
    }

    /**
     * Display doctors by symptom
     */
    public function doctorsBySymptom($symptom)
    {
        // Map symptoms to specializations (using database specialty names)
        $symptomMap = [
            'menstruation-flow' => 'Obstetrics & Gynecology (OB/GYN)',
            'rashes' => 'Dermatology',
            'headache' => 'Neurology',
            'cough' => 'Internal Medicine',
            'fever' => 'General Practice (Family Medicine)',
            'stomach-pain' => 'Gastroenterology',
            'back-pain' => 'Orthopaedics',
            'eye-problems' => 'Ophthalmology',
            'ear-pain' => 'ENT (Otolaryngology)',
            'joint-pain' => 'Orthopaedics',
            'skin-issues' => 'Dermatology',
            'chest-pain' => 'Cardiology',
        ];
        
        // Normalize the symptom slug
        $symptom = strtolower(str_replace(' ', '-', $symptom));

        $specialization = $symptomMap[$symptom] ?? null;
        
        if (!$specialization) {
            abort(404, 'Symptom not found');
        }

        $doctors = \App\Models\Doctor::where('specialization', $specialization)
            ->where('is_approved', true)
            ->get();

        $symptomName = ucwords(str_replace('-', ' ', $symptom));

        return view('patient.doctors-by-specialization', compact('doctors', 'specialization', 'symptomName'));
    }

    /**
     * Display all consultations
     */
    public function consultations(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        
        $query = $patient->consultations()->with(['doctor', 'payment']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by reference or doctor name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('doctor', function($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $consultations = $query->latest()->paginate(15);

        // Statistics
        $stats = [
            'total' => $patient->consultations()->count(),
            'completed' => $patient->consultations()->where('status', 'completed')->count(),
            'pending' => $patient->consultations()->where('status', 'pending')->count(),
            'paid' => $patient->consultations()->where('payment_status', 'paid')->count(),
            'unpaid' => $patient->consultations()
                ->where('status', 'completed')
                ->where('payment_status', '!=', 'paid')
                ->count(),
        ];

        return view('patient.consultations', compact('consultations', 'stats'));
    }

    /**
     * View single consultation
     */
    public function viewConsultation($id)
    {
        $patient = Auth::guard('patient')->user();
        
        $consultation = $patient->consultations()
            ->with(['doctor', 'payment'])
            ->findOrFail($id);

        return view('patient.consultation-details', compact('consultation'));
    }

    /**
     * Display medical records
     */
    public function medicalRecords()
    {
        $patient = Auth::guard('patient')->user();
        
        $medicalHistories = $patient->medicalHistories()
            ->with('consultation.doctor')
            ->latest('consultation_date')
            ->paginate(10);

        $latestVitals = $patient->latestVitalSigns;

        $stats = [
            'total_records' => $patient->medicalHistories()->count(),
            'total_vital_signs' => $patient->vitalSigns()->count(),
            'last_consultation' => $patient->last_consultation_at,
        ];

        return view('patient.medical-records', compact('medicalHistories', 'latestVitals', 'stats'));
    }

    /**
     * Display profile/settings
     */
    public function profile()
    {
        $patient = Auth::guard('patient')->user();
        
        return view('patient.profile', compact('patient'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $patient = Auth::guard('patient')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
        ]);

        $patient->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Display dependents (children/family members)
     */
    public function dependents()
    {
        $patient = Auth::guard('patient')->user();
        
        $dependents = $patient->dependents()->with('consultations')->get();

        return view('patient.dependents', compact('dependents'));
    }

    /**
     * Display payments history
     */
    public function payments()
    {
        $patient = Auth::guard('patient')->user();
        
        $consultations = $patient->consultations()
            ->with(['doctor', 'payment'])
            ->whereNotNull('payment_id')
            ->latest()
            ->paginate(15);

        $stats = [
            'total_paid' => $patient->total_amount_paid,
            'paid_consultations' => $patient->consultations()->where('payment_status', 'paid')->count(),
            'pending_payments' => $patient->consultations()
                ->where('status', 'completed')
                ->where('payment_status', '!=', 'paid')
                ->count(),
        ];

        return view('patient.payments', compact('consultations', 'stats'));
    }
}
