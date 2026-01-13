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
use App\Notifications\ConsultationSmsNotification;

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
                'first_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
                'last_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
                'email' => 'required|email:rfc|max:255|unique:patients,email',
                'phone' => ['required', 'string', 'regex:/^(\+234|0)[0-9]{10}$/'],
                'gender' => 'required|in:male,female,other',
                'age' => 'required|integer|min:1|max:120',
            ], [
                // Custom error messages
                'first_name.required' => 'First name is required.',
                'first_name.min' => 'First name must be at least 2 characters.',
                'first_name.regex' => 'First name can only contain letters, spaces, hyphens, and apostrophes.',
                'last_name.required' => 'Last name is required.',
                'last_name.min' => 'Last name must be at least 2 characters.',
                'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, and apostrophes.',
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email address is already registered. Please use a different email or contact support if you believe this is an error.',
                'phone.required' => 'Phone number is required.',
                'phone.regex' => 'Please enter a valid Nigerian phone number (e.g., +2348012345678 or 08012345678).',
                'gender.required' => 'Please select gender.',
                'gender.in' => 'Gender must be either male or female.',
                'age.required' => 'Age is required.',
                'age.integer' => 'Age must be a valid number.',
                'age.min' => 'Age must be at least 1.',
                'age.max' => 'Age cannot exceed 120.',
            ]);

            $canvasser = Auth::guard('canvasser')->user();

            // Check if a patient with this email already exists (including soft-deleted)
            $patient = Patient::withTrashed()->where('email', $validated['email'])->first();
            
            if ($patient) {
                // Patient exists - restore if soft-deleted and update
                if ($patient->trashed()) {
                    $patient->restore();
                    \Log::info('Canvasser restored soft-deleted patient', [
                        'patient_id' => $patient->id,
                        'email' => $validated['email'],
                        'canvasser_id' => $canvasser->id
                    ]);
                }
                
                // Update the patient record
                $patient->update([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'phone' => $validated['phone'],
                    'gender' => $validated['gender'],
                    'age' => $validated['age'],
                    'canvasser_id' => $canvasser->id,
                ]);
                
                $message = 'Patient record updated successfully! You can now create consultations for this patient.';
            } else {
                // Create new patient
                $patient = Patient::create([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'gender' => $validated['gender'],
                    'age' => $validated['age'],
                    'canvasser_id' => $canvasser->id,
                ]);
                
                $message = 'Patient registered successfully! You can now create consultations for this patient.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
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
        
        // Canvassers can see ALL approved doctors with all specialties
        $doctors = Doctor::approved()
            ->orderByRaw('CASE WHEN is_available = 1 THEN 0 ELSE 1 END')
            ->ordered()
            ->with('reviews')
            ->get();
        
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

        // Handle medical document uploads - HIPAA: Store in private storage
        $uploadedDocuments = [];
        if ($request->hasFile('medical_documents')) {
            foreach ($request->file('medical_documents') as $file) {
                $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                // Store in private storage (storage/app/private/medical_documents)
                $filePath = $file->storeAs('medical_documents', $fileName);
                
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
            'patient_id' => $patient->id,
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

        // Create notifications for patient and doctor
        try {
            // Notification for patient - always send if patient exists
            if ($patient && $patient->id) {
                $doctorName = $validated['doctor_name'] ?? null;
                $patientMessage = $doctorId && $doctorName
                    ? "You have successfully booked a consultation with Dr. {$doctorName}. Reference: {$reference}"
                    : "Your consultation request (Ref: {$reference}) has been submitted successfully. A doctor will be assigned shortly.";
                
                \App\Models\Notification::create([
                    'user_type' => 'patient',
                    'user_id' => $patient->id,
                    'title' => $doctorId ? 'Consultation Booked' : 'Consultation Created',
                    'message' => $patientMessage,
                    'type' => 'success',
                    'action_url' => patient_url('consultations/' . $consultation->id),
                    'data' => [
                        'consultation_id' => $consultation->id,
                        'consultation_reference' => $reference,
                        'doctor_id' => $doctorId,
                        'doctor_name' => $doctorName,
                        'type' => $doctorId ? 'consultation_booked' : 'consultation_created'
                    ]
                ]);
                
                \Log::info('Patient notification created for canvasser consultation', [
                    'consultation_id' => $consultation->id,
                    'patient_id' => $patient->id,
                    'reference' => $reference,
                    'doctor_id' => $doctorId
                ]);
            }

            // Notification for doctor - always send if doctor is assigned
            if ($doctorId) {
                $assignedDoctor = \App\Models\Doctor::find($doctorId);
                if ($assignedDoctor) {
                    $doctorMessage = "A new consultation has been booked with you. Patient: {$patient->name}. Reference: {$reference}";
                    
                    \App\Models\Notification::create([
                        'user_type' => 'doctor',
                        'user_id' => $doctorId,
                        'title' => 'New Consultation Booked',
                        'message' => $doctorMessage,
                        'type' => 'info',
                        'action_url' => doctor_url('consultations/' . $consultation->id),
                        'data' => [
                            'consultation_id' => $consultation->id,
                            'consultation_reference' => $reference,
                            'patient_id' => $patient->id ?? null,
                            'patient_name' => $patient->name,
                            'type' => 'new_consultation'
                        ]
                    ]);
                    
                    \Log::info('Doctor notification created for canvasser consultation', [
                        'consultation_id' => $consultation->id,
                        'doctor_id' => $doctorId,
                        'reference' => $reference,
                        'patient_id' => $patient->id ?? null
                    ]);
                } else {
                    \Log::warning('Doctor not found for canvasser notification', [
                        'consultation_id' => $consultation->id,
                        'doctor_id' => $doctorId
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create canvasser consultation notifications', [
                'consultation_id' => $consultation->id,
                'patient_id' => $patient->id ?? null,
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

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

        // Send SMS confirmation to the patient
        try {
            $smsNotification = new ConsultationSmsNotification();
            $smsResult = $smsNotification->sendConsultationConfirmation($validated);
            
            if ($smsResult['success']) {
                \Log::info('Patient confirmation SMS sent (via canvasser)', [
                    'consultation_reference' => $reference,
                    'patient_mobile' => $validated['mobile'],
                    'canvasser_id' => $canvasser->id
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send patient confirmation SMS (via canvasser): ' . $e->getMessage(), [
                'consultation_reference' => $reference,
                'patient_mobile' => $validated['mobile'] ?? 'N/A'
            ]);
        }

        // Send alert email to admin
        Mail::to(config('mail.admin_email'))->send(new ConsultationAdminAlert($validated));

        // Send notification email to the assigned doctor
        if ($doctorEmail) {
            Mail::to($doctorEmail)->send(new ConsultationDoctorNotification($validated));
        }

        // Send SMS notification to the assigned doctor
        if ($doctorId) {
            try {
                $smsNotification = new ConsultationSmsNotification();
                $assignedDoctor = Doctor::find($doctorId);
                
                if ($assignedDoctor) {
                    $smsResult = $smsNotification->sendDoctorNewConsultation($assignedDoctor, $validated);
                    
                    if ($smsResult['success']) {
                        \Log::info('Doctor notification SMS sent (via canvasser)', [
                            'consultation_reference' => $reference,
                            'doctor_id' => $doctorId,
                            'doctor_phone' => $assignedDoctor->phone,
                            'canvasser_id' => $canvasser->id
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send doctor notification SMS (via canvasser): ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'doctor_id' => $doctorId
                ]);
            }
        }

        // ============================================
        // SEND WHATSAPP NOTIFICATIONS (More Reliable!)
        // ============================================
        
        // Send WhatsApp notification to PATIENT (if enabled)
        if (config('services.termii.whatsapp_enabled')) {
            try {
                $whatsapp = new \App\Notifications\ConsultationWhatsAppNotification();
                
                $patientResult = $whatsapp->sendConsultationConfirmationTemplate(
                    $validated,
                    'patient_booking_confirmation' // Template ID from Termii dashboard
                );
                
                if ($patientResult['success']) {
                    \Log::info('Patient WhatsApp notification sent (via canvasser)', [
                        'consultation_reference' => $reference,
                        'patient_phone' => $validated['mobile'],
                        'canvasser_id' => $canvasser->id
                    ]);
                } else {
                    \Log::warning('Patient WhatsApp notification failed (via canvasser)', [
                        'consultation_reference' => $reference,
                        'error' => $patientResult['message'] ?? 'Unknown error',
                        'canvasser_id' => $canvasser->id
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Patient WhatsApp notification error (via canvasser): ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'phone' => $validated['mobile'] ?? 'N/A',
                    'canvasser_id' => $canvasser->id
                ]);
            }
        }
        
        // Send WhatsApp notification to DOCTOR (if doctor assigned and WhatsApp enabled)
        if (config('services.termii.whatsapp_enabled') && $doctorId) {
            try {
                $assignedDoctor = Doctor::find($doctorId);
                
                if ($assignedDoctor && $assignedDoctor->phone) {
                    $whatsapp = new \App\Notifications\ConsultationWhatsAppNotification();
                    
                    $doctorResult = $whatsapp->sendDoctorNewConsultationTemplate(
                        $assignedDoctor,
                        $validated,
                        'doctor_new_consultation' // Template ID from Termii dashboard
                    );
                    
                    if ($doctorResult['success']) {
                        \Log::info('Doctor WhatsApp notification sent (via canvasser)', [
                            'consultation_reference' => $reference,
                            'doctor_id' => $doctorId,
                            'doctor_phone' => $assignedDoctor->phone,
                            'canvasser_id' => $canvasser->id
                        ]);
                    } else {
                        \Log::warning('Doctor WhatsApp notification failed (via canvasser)', [
                            'consultation_reference' => $reference,
                            'doctor_id' => $doctorId,
                            'error' => $doctorResult['message'] ?? 'Unknown error',
                            'canvasser_id' => $canvasser->id
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Doctor WhatsApp notification error (via canvasser): ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'doctor_id' => $doctorId,
                    'canvasser_id' => $canvasser->id
                ]);
            }
        }

        return redirect()->route('canvasser.patients')
            ->with('success', 'Consultation created successfully for ' . $patient->name . '! Reference: ' . $reference . '. Patient and doctor have been notified via email and SMS.');
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

