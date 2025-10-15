<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display doctor dashboard
     */
    public function index()
    {
        $doctor = Auth::guard('doctor')->user();
        
        $stats = [
            'total_consultations' => Consultation::where('doctor_id', $doctor->id)->count(),
            'pending_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                    ->where('status', 'pending')->count(),
            'scheduled_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                     ->where('status', 'scheduled')->count(),
            'completed_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                     ->where('status', 'completed')->count(),
        ];

        // Get recent consultations
        $recentConsultations = Consultation::where('doctor_id', $doctor->id)
                                           ->latest()
                                           ->limit(10)
                                           ->get();

        return view('doctor.dashboard', compact('stats', 'recentConsultations'));
    }

    /**
     * Display all consultations for the doctor
     */
    public function consultations(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $query = Consultation::where('doctor_id', $doctor->id);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Search by patient name, email, or reference
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }
        
        $consultations = $query->latest()->paginate(15);
        
        return view('doctor.consultations', compact('consultations'));
    }

    /**
     * Update consultation status
     */
    public function updateConsultationStatus(Request $request, $id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            $validated = $request->validate([
                'status' => 'required|in:pending,scheduled,completed,cancelled',
                'notes' => 'nullable|string|max:1000',
            ]);
            
            $consultation->update([
                'status' => $validated['status'],
                'doctor_notes' => $validated['notes'] ?? $consultation->doctor_notes,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Consultation status updated successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View single consultation details
     */
    public function viewConsultation($id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->with(['doctor', 'payment', 'canvasser', 'nurse'])
                                       ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'consultation' => [
                    'id' => $consultation->id,
                    'reference_number' => $consultation->reference_number,
                    'patient_name' => $consultation->first_name . ' ' . $consultation->last_name,
                    'email' => $consultation->email,
                    'mobile' => $consultation->mobile,
                    'age' => $consultation->age,
                    'gender' => ucfirst($consultation->gender),
                    'symptoms' => $consultation->problem,
                    'status' => ucfirst($consultation->status),
                    'payment_status' => $consultation->payment ? ucfirst($consultation->payment->status) : 'Pending',
                    'created_at' => $consultation->created_at->format('M d, Y h:i A'),
                    'medical_documents' => $consultation->medical_documents,
                    'doctor_notes' => $consultation->doctor_notes,
                    'canvasser' => $consultation->canvasser ? $consultation->canvasser->name : 'N/A',
                    'nurse' => $consultation->nurse ? $consultation->nurse->name : 'Not Assigned',
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load consultation: ' . $e->getMessage()
            ], 500);
        }
    }
}

