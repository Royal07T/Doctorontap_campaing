<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\VitalSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VitalSignsReport;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    /**
     * Display nurse dashboard
     */
    public function index()
    {
        $nurse = Auth::guard('nurse')->user();
        
        $stats = [
            'total_patients_attended' => VitalSign::where('nurse_id', $nurse->id)
                                                  ->distinct('patient_id')
                                                  ->count('patient_id'),
            'total_vital_records' => VitalSign::where('nurse_id', $nurse->id)->count(),
            'patients_today' => VitalSign::where('nurse_id', $nurse->id)
                                         ->whereDate('created_at', today())
                                         ->distinct('patient_id')
                                         ->count('patient_id'),
            'total_consultations' => Consultation::where('nurse_id', $nurse->id)->count(),
        ];

        return view('nurse.dashboard', compact('stats'));
    }

    /**
     * Search for patients
     */
public function searchPatients(Request $request)
    {
        $nurse = Auth::guard('nurse')->user();
        
        // Only show results if there's a search query
        if (!$request->filled('search')) {
            // Return empty collection if no search term
            $patients = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            return view('nurse.patients', compact('patients'));
        }
        
        $search = $request->search;
        $query = Patient::query();
        
        // Search functionality
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
        
        $patients = $query->with('latestVitalSigns')->latest()->paginate(15);
        
        return view('nurse.patients', compact('patients'));
    }

    /**
     * View patient details with vital signs history
     */
    public function viewPatient($id)
    {
        try {
            $patient = Patient::with(['vitalSigns' => function($query) {
                $query->latest();
            }, 'vitalSigns.nurse'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'patient' => [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'email' => $patient->email,
                    'phone' => $patient->phone,
                    'gender' => ucfirst($patient->gender),
                    'has_consulted' => $patient->has_consulted,
                    'total_amount_paid' => number_format($patient->total_amount_paid, 2),
                    'vital_signs' => $patient->vitalSigns->map(function($vital) {
                        return [
                            'id' => $vital->id,
                            'blood_pressure' => $vital->blood_pressure,
                            'oxygen_saturation' => $vital->oxygen_saturation,
                            'temperature' => $vital->temperature,
                            'blood_sugar' => $vital->blood_sugar,
                            'height' => $vital->height,
                            'weight' => $vital->weight,
                            'heart_rate' => $vital->heart_rate,
                            'respiratory_rate' => $vital->respiratory_rate,
                            'bmi' => $vital->bmi,
                            'notes' => $vital->notes,
                            'recorded_by' => $vital->nurse ? $vital->nurse->name : 'Unknown',
                            'recorded_at' => $vital->created_at->format('M d, Y h:i A'),
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load patient details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store vital signs for a patient (NO automatic email)
     */
    public function storeVitalSigns(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'blood_pressure' => 'nullable|string|max:20',
                'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
                'temperature' => 'nullable|numeric|min:30|max:45',
                'blood_sugar' => 'nullable|numeric|min:0|max:1000',
                'height' => 'nullable|numeric|min:0|max:300',
                'weight' => 'nullable|numeric|min:0|max:500',
                'heart_rate' => 'nullable|integer|min:0|max:300',
                'respiratory_rate' => 'nullable|integer|min:0|max:100',
                'notes' => 'nullable|string|max:1000',
            ]);

            $nurse = Auth::guard('nurse')->user();

            // Create vital signs record (NO email sent)
            $vitalSign = VitalSign::create([
                'patient_id' => $validated['patient_id'],
                'nurse_id' => $nurse->id,
                'blood_pressure' => $validated['blood_pressure'] ?? null,
                'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
                'temperature' => $validated['temperature'] ?? null,
                'blood_sugar' => $validated['blood_sugar'] ?? null,
                'height' => $validated['height'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'heart_rate' => $validated['heart_rate'] ?? null,
                'respiratory_rate' => $validated['respiratory_rate'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'email_sent' => false,
                'is_walk_in' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vital signs recorded successfully!',
                'vital_sign' => [
                    'id' => $vitalSign->id,
                    'patient_email' => $vitalSign->patient->email
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to record vital signs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to record vital signs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send vital signs report via email (triggered manually by nurse)
     */
    public function sendVitalSignsEmail($vitalSignId)
    {
        try {
            $nurse = Auth::guard('nurse')->user();
            $vitalSign = VitalSign::with('patient')->findOrFail($vitalSignId);

            // Check if already sent
            if ($vitalSign->email_sent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email has already been sent for this vital signs record.'
                ], 400);
            }

            $patient = $vitalSign->patient;

            // Generate PDF report
            $pdf = Pdf::loadView('pdfs.vital-signs-report', [
                'patient' => $patient,
                'vitalSign' => $vitalSign,
                'nurse' => $nurse
            ]);

            $pdfContent = $pdf->output();

            // Send email with PDF attachment
            Mail::to($patient->email)->send(new VitalSignsReport(
                $patient,
                $vitalSign,
                $nurse,
                $pdfContent
            ));

            // Update vital signs record
            $vitalSign->update([
                'email_sent' => true,
                'email_sent_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report sent to ' . $patient->email . ' successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send vital signs email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

}

