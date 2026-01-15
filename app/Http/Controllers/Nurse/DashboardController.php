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
use App\Http\Requests\VitalSignsRequest;
use App\Helpers\SecurityHelper;

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
        
        // Sanitize search input
        $search = SecurityHelper::sanitizeString($request->search);
        
        // Additional security check for potential SQL injection
        if (SecurityHelper::containsSqlInjection($search)) {
            SecurityHelper::logSecurityIncident('sql_injection_attempt_in_search', [
                'search_term' => $request->search,
                'nurse_id' => $nurse->id,
            ]);
            return view('nurse.patients', compact('patients'))
                ->with('error', 'Invalid search query');
        }
        
        $query = Patient::query();
        
        // Search functionality (Eloquent protects against SQL injection)
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
            // Validate ID parameter
            $patientId = SecurityHelper::sanitizeInteger($id);
            if (!$patientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid patient ID'
                ], 400);
            }
            
            $nurse = Auth::guard('nurse')->user();
            
            // RBAC: Nurse can only view patients they've attended (recorded vital signs for)
            $patient = Patient::with(['vitalSigns' => function($query) use ($nurse) {
                $query->where('nurse_id', $nurse->id)->latest();
            }, 'vitalSigns.nurse'])->findOrFail($patientId);
            
            // Verify nurse has actually attended this patient
            if (!$patient->vitalSigns->count()) {
                \Log::warning('Nurse attempted to access patient they have not attended', [
                    'nurse_id' => $nurse->id,
                    'nurse_email' => $nurse->email,
                    'patient_id' => $id,
                    'ip_address' => request()->ip(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You have not attended this patient'
                ], 403);
            }

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
    public function storeVitalSigns(VitalSignsRequest $request)
    {
        try {
            // Use Form Request for validation and sanitization
            $validated = $request->validated();

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
            // Validate ID parameter
            $vitalId = SecurityHelper::sanitizeInteger($vitalSignId);
            if (!$vitalId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid vital sign ID'
                ], 400);
            }
            
            $nurse = Auth::guard('nurse')->user();
            
            // RBAC: Nurse can only send emails for vital signs they recorded
            $vitalSign = VitalSign::with('patient')
                ->where('id', $vitalId)
                ->where('nurse_id', $nurse->id)
                ->firstOrFail();

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

            // Send email with PDF attachment (prefer user email as source of truth)
            $patientEmail = $patient->user?->email ?? $patient->email;
            Mail::to($patientEmail)->send(new VitalSignsReport(
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

