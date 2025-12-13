<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\PatientMedicalHistory;
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

        // Get doctor specializations for carousel
        $specializations = \App\Models\Doctor::whereNotNull('specialization')
            ->where('specialization', '!=', '')
            ->distinct()
            ->pluck('specialization')
            ->take(10);

        return view('patient.dashboard', compact('patient', 'stats', 'recentConsultations', 'dependents', 'upcomingConsultations', 'specializations'));
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
