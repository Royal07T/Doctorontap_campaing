<?php

namespace App\Http\Controllers\Canvasser;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ConsultationConfirmation;
use App\Mail\ConsultationAdminAlert;
use App\Mail\ConsultationDoctorNotification;
use App\Mail\CanvasserConsultationConfirmation;

class DashboardController extends Controller
{
    /**
     * Display canvasser dashboard
     */
    public function index()
    {
        $canvasser = Auth::guard('canvasser')->user();
        
        // Get statistics
        $stats = [
            'total_patients' => Patient::where('canvasser_id', $canvasser->id)->count(),
            'consulted_patients' => Patient::where('canvasser_id', $canvasser->id)
                                           ->where('has_consulted', true)->count(),
            'total_amount' => Patient::where('canvasser_id', $canvasser->id)
                                     ->sum('total_amount_paid'),
            'total_consultations' => Consultation::where('canvasser_id', $canvasser->id)->count(),
            'pending_consultations' => Consultation::where('canvasser_id', $canvasser->id)
                                                  ->where('status', 'pending')->count(),
            'completed_consultations' => Consultation::where('canvasser_id', $canvasser->id)
                                                    ->where('status', 'completed')->count(),
        ];

        // Get recent patients
        $recentPatients = Patient::where('canvasser_id', $canvasser->id)
                                 ->latest()
                                 ->limit(10)
                                 ->get();

        return view('canvasser.dashboard', compact('stats', 'recentPatients'));
    }

    /**
     * Store a new patient
     */
    public function storePatient(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:patients,email',
                'phone' => 'required|string|max:20',
                'gender' => 'required|in:male,female,other',
                'age' => 'required|integer|min:1|max:120',
            ], [
                'email.unique' => 'This email address is already registered. Please use a different email or contact support if you believe this is an error.',
            ]);

            $canvasser = Auth::guard('canvasser')->user();

            $patient = Patient::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'gender' => $validated['gender'],
                'age' => $validated['age'],
                'canvasser_id' => $canvasser->id,
            ]);

            // Send email verification
            $patient->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Patient registered successfully! A verification email has been sent to their email address.',
                'patient' => $patient
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View all patients registered by this canvasser
     */
    public function patients(Request $request)
    {
        $canvasser = Auth::guard('canvasser')->user();
        
        $query = Patient::where('canvasser_id', $canvasser->id);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Filter by consultation status
        if ($request->filled('status')) {
            if ($request->status === 'consulted') {
                $query->where('has_consulted', true);
            } elseif ($request->status === 'not_consulted') {
                $query->where('has_consulted', false);
            }
        }
        
        $patients = $query->latest()->paginate(15);
        
        return view('canvasser.patients', compact('patients'));
    }

    /**
     * Show consultation creation form for a specific patient
     */
    public function createConsultation($patientId)
    {
        $canvasser = Auth::guard('canvasser')->user();
        $patient = Patient::where('id', $patientId)
                         ->where('canvasser_id', $canvasser->id)
                         ->firstOrFail();
        
        $doctors = Doctor::available()->ordered()->with('reviews')->get();
        
        return view('canvasser.create-consultation', compact('patient', 'doctors'));
    }

    /**
     * Store consultation for a patient
     */
    public function storeConsultation(Request $request, $patientId)
    {
        $canvasser = Auth::guard('canvasser')->user();
        $patient = Patient::where('id', $patientId)
                         ->where('canvasser_id', $canvasser->id)
                         ->firstOrFail();

        // Validate the form data
        $validated = $request->validate([
            'problem' => 'required|string|max:500',
            'medical_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'severity' => 'required|in:mild,moderate,severe',
            'emergency_symptoms' => 'nullable|array',
            'doctor' => 'nullable|string|max:255',
            'consult_mode' => 'required|in:voice,video,chat',
        ]);

        // Generate unique consultation reference
        $reference = 'CONSULT-' . time() . '-' . Str::random(6);

        // Handle medical document uploads
        $uploadedDocuments = [];
        if ($request->hasFile('medical_documents')) {
            foreach ($request->file('medical_documents') as $file) {
                $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('medical_documents', $fileName, 'public');
                
                $uploadedDocuments[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => $fileName,
                    'path' => $filePath,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        // Get doctor details from ID if a doctor was selected
        $doctorEmail = null;
        $doctorId = null;
        
        if (!empty($validated['doctor'])) {
            $doctor = Doctor::find($validated['doctor']);
            if ($doctor) {
                $validated['doctor_name'] = $doctor->name;
                $validated['doctor_id'] = $validated['doctor'];
                $validated['doctor'] = $doctor->name;
                $validated['doctor_fee'] = $doctor->consultation_fee;
                $doctorEmail = $doctor->email;
                $doctorId = $doctor->id;
            }
        }

        // Create consultation record
        $consultation = Consultation::create([
            'reference' => $reference,
            'first_name' => explode(' ', $patient->name)[0] ?? $patient->name,
            'last_name' => implode(' ', array_slice(explode(' ', $patient->name), 1)) ?? '',
            'email' => $patient->email,
            'mobile' => $patient->phone,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'problem' => $validated['problem'],
            'medical_documents' => !empty($uploadedDocuments) ? $uploadedDocuments : null,
            'severity' => $validated['severity'],
            'emergency_symptoms' => $validated['emergency_symptoms'] ?? null,
            'consult_mode' => $validated['consult_mode'],
            'doctor_id' => $doctorId,
            'canvasser_id' => $canvasser->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Update patient aggregates
        $patient->increment('consultations_count');
        $patient->last_consultation_at = now();
        $patient->save();

        // Add reference and documents to validated data for emails
        $validated['consultation_reference'] = $reference;
        $validated['has_documents'] = !empty($uploadedDocuments);
        $validated['documents_count'] = count($uploadedDocuments);
        $validated['first_name'] = explode(' ', $patient->name)[0] ?? $patient->name;
        $validated['last_name'] = implode(' ', array_slice(explode(' ', $patient->name), 1)) ?? '';
        $validated['email'] = $patient->email;
        $validated['mobile'] = $patient->phone;
        $validated['age'] = $patient->age;
        $validated['gender'] = $patient->gender;

        // Send specialized confirmation email to the patient (booked by canvasser)
        Mail::to($patient->email)->send(new CanvasserConsultationConfirmation($validated, $canvasser));

        // Send alert email to admin
        Mail::to(env('ADMIN_EMAIL', 'inquiries@doctorontap.com.ng'))->send(new ConsultationAdminAlert($validated));

        // Send notification email to the assigned doctor
        if ($doctorEmail) {
            Mail::to($doctorEmail)->send(new ConsultationDoctorNotification($validated));
        }

        return redirect()->route('canvasser.patients')
            ->with('success', 'Consultation created successfully for ' . $patient->name . '! Reference: ' . $reference . '. Patient has been notified via email.');
    }

    /**
     * View consultations for a specific patient
     */
    public function patientConsultations($patientId)
    {
        $canvasser = Auth::guard('canvasser')->user();
        $patient = Patient::where('id', $patientId)
                         ->where('canvasser_id', $canvasser->id)
                         ->firstOrFail();
        
        $consultations = Consultation::where('canvasser_id', $canvasser->id)
                                   ->where('email', $patient->email)
                                   ->with('doctor')
                                   ->latest()
                                   ->paginate(10);
        
        return view('canvasser.patient-consultations', compact('patient', 'consultations'));
    }
}

