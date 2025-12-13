<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\PatientMedicalHistory;
use App\Models\Specialty;
use App\Models\Doctor;
use App\Models\Setting;
use App\Models\MenstrualCycle;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\ConsultationConfirmation;
use App\Mail\ConsultationAdminAlert;
use App\Mail\ConsultationDoctorNotification;
use App\Notifications\ConsultationSmsNotification;

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

        // Symptoms with their related specializations and icons
        $symptoms = [
            ['name' => 'Menstruation Flow', 'specialization' => 'Obstetrics & Gynecology (OB/GYN)', 'icon' => 'menstruation'],
            ['name' => 'Rashes', 'specialization' => 'Dermatology', 'icon' => 'rash'],
            ['name' => 'Headache', 'specialization' => 'Neurology', 'icon' => 'headache'],
            ['name' => 'Cough', 'specialization' => 'Internal Medicine', 'icon' => 'cough'],
            ['name' => 'Fever', 'specialization' => 'General Practice (Family Medicine)', 'icon' => 'fever'],
            ['name' => 'Stomach Pain', 'specialization' => 'Gastroenterology', 'icon' => 'stomach'],
            ['name' => 'Back Pain', 'specialization' => 'Orthopaedics', 'icon' => 'back'],
            ['name' => 'Eye Problems', 'specialization' => 'Ophthalmology', 'icon' => 'eye'],
            ['name' => 'Ear Pain', 'specialization' => 'ENT (Otolaryngology)', 'icon' => 'ear'],
            ['name' => 'Joint Pain', 'specialization' => 'Orthopaedics', 'icon' => 'joint'],
            ['name' => 'Skin Issues', 'specialization' => 'Dermatology', 'icon' => 'skin'],
            ['name' => 'Chest Pain', 'specialization' => 'Cardiology', 'icon' => 'chest'],
        ];

        // Get menstrual cycle data for female patients
        $menstrualCycles = [];
        $currentCycle = null;
        $nextPeriodPrediction = null;
        $averageCycleLength = null;
        
        if (strtolower($patient->gender) === 'female') {
            $menstrualCycles = \App\Models\MenstrualCycle::where('patient_id', $patient->id)
                ->orderBy('start_date', 'desc')
                ->limit(6)
                ->get();
            
            // Get current/active cycle
            $currentCycle = \App\Models\MenstrualCycle::where('patient_id', $patient->id)
                ->where(function($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now()->subDays(7));
                })
                ->orderBy('start_date', 'desc')
                ->first();
            
            // Calculate average cycle length
            if ($menstrualCycles->count() >= 2) {
                $cycleLengths = [];
                for ($i = 0; $i < $menstrualCycles->count() - 1; $i++) {
                    $current = $menstrualCycles[$i];
                    $previous = $menstrualCycles[$i + 1];
                    if ($current->start_date && $previous->start_date) {
                        $cycleLengths[] = $current->start_date->diffInDays($previous->start_date);
                    }
                }
                if (!empty($cycleLengths)) {
                    $averageCycleLength = round(array_sum($cycleLengths) / count($cycleLengths));
                }
            }
            
            // Predict next period
            if ($currentCycle && $currentCycle->end_date) {
                $lastPeriodEnd = $currentCycle->end_date;
                $cycleLength = $averageCycleLength ?? 28; // Default 28 days
                $nextPeriodPrediction = $lastPeriodEnd->copy()->addDays($cycleLength);
            } elseif ($menstrualCycles->isNotEmpty() && $menstrualCycles->first()->end_date) {
                $lastPeriodEnd = $menstrualCycles->first()->end_date;
                $cycleLength = $averageCycleLength ?? 28;
                $nextPeriodPrediction = $lastPeriodEnd->copy()->addDays($cycleLength);
            }
        }

        return view('patient.dashboard', compact('patient', 'stats', 'recentConsultations', 'dependents', 'upcomingConsultations', 'specializations', 'symptoms', 'menstrualCycles', 'currentCycle', 'nextPeriodPrediction', 'averageCycleLength'));
    }

    /**
     * Display all available doctors
     */
    public function doctors(Request $request)
    {
        $query = \App\Models\Doctor::where('is_approved', true)
            ->where('is_available', true);

        // Filter by specialization if provided
        if ($request->filled('specialization')) {
            $specialization = urldecode($request->specialization);
            $specializationMap = [
                'General Practice (Family Medicine)' => ['General Practice', 'General Practitioner', 'General Practitional'],
                'General Practitioner' => ['General Practitioner', 'General Practice', 'General Practitional'],
                'General Practice' => ['General Practice', 'General Practitioner', 'General Practitional'],
            ];
            
            $searchTerms = $specializationMap[$specialization] ?? [$specialization];
            
            $query->where(function($q) use ($specialization, $searchTerms) {
                $q->where('specialization', $specialization)
                  ->orWhereRaw('LOWER(TRIM(specialization)) = ?', [strtolower(trim($specialization))]);
                
                if (count($searchTerms) > 1 || $searchTerms[0] !== $specialization) {
                    foreach ($searchTerms as $term) {
                        $q->orWhere('specialization', $term)
                          ->orWhereRaw('LOWER(TRIM(specialization)) = ?', [strtolower(trim($term))]);
                    }
                }
            });
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        $doctors = $query->orderBy('name')->paginate(12);
        $specializations = \App\Models\Doctor::where('is_approved', true)
            ->where('is_available', true)
            ->whereNotNull('specialization')
            ->distinct()
            ->orderBy('specialization')
            ->pluck('specialization');

        return view('patient.doctors', compact('doctors', 'specializations'));
    }

    /**
     * Display doctors by specialization
     */
    public function doctorsBySpecialization($specialization)
    {
        // Decode URL-encoded specialization (e.g., "General+Practice+%28Family+Medicine%29" -> "General Practice (Family Medicine)")
        $specialization = urldecode($specialization);
        
        // Map common variations to database values
        $specializationMap = [
            'General Practice (Family Medicine)' => ['General Practice', 'General Practitioner', 'General Practitional'],
            'General Practitioner' => ['General Practitioner', 'General Practice', 'General Practitional'],
            'General Practice' => ['General Practice', 'General Practitioner', 'General Practitional'],
        ];
        
        // Check if we have a mapping for this specialization
        $searchTerms = $specializationMap[$specialization] ?? [$specialization];
        
        // Query doctors with this specialization (case-insensitive, trimmed)
        // Try exact match first, then case-insensitive match, then mapped variations
        $doctors = \App\Models\Doctor::where(function($query) use ($specialization, $searchTerms) {
                // Exact match
                $query->where('specialization', $specialization)
                      // Case-insensitive match
                      ->orWhereRaw('LOWER(TRIM(specialization)) = ?', [strtolower(trim($specialization))]);
                
                // If we have mapped terms, also search for those
                if (count($searchTerms) > 1 || $searchTerms[0] !== $specialization) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('specialization', $term)
                              ->orWhereRaw('LOWER(TRIM(specialization)) = ?', [strtolower(trim($term))]);
                    }
                }
            })
            ->where('is_approved', true)
            ->where('is_available', true)
            ->orderBy('name')
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
            ->where('is_available', true)
            ->orderBy('name')
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

        // Mark treatment plan as accessed if it's accessible
        if ($consultation->isTreatmentPlanAccessible()) {
            $consultation->markTreatmentPlanAccessed();
        }

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
        
        // Get all consultations (both paid and unpaid)
        $consultations = $patient->consultations()
            ->with(['doctor', 'payment'])
            ->latest()
            ->paginate(15);

        // Calculate total paid from actual payments in database
        // Query payments table directly by joining with consultations for accurate amount
        $totalPaid = DB::table('payments')
            ->join('consultations', 'payments.id', '=', 'consultations.payment_id')
            ->where('consultations.patient_id', $patient->id)
            ->where('consultations.payment_status', 'paid')
            ->sum('payments.amount');

        // Get pending payments (unpaid consultations)
        $pendingConsultations = $patient->consultations()
            ->with('doctor')
            ->where(function($query) {
                $query->where('payment_status', '!=', 'paid')
                      ->orWhereNull('payment_status');
            })
            ->where(function($query) {
                $query->where('status', 'completed')
                      ->orWhere('status', 'pending_payment');
            })
            ->latest()
            ->get();

        $stats = [
            'total_paid' => $totalPaid,
            'paid_consultations' => $patient->consultations()->where('payment_status', 'paid')->count(),
            'pending_payments' => $pendingConsultations->count(),
        ];

        return view('patient.payments', compact('consultations', 'pendingConsultations', 'stats'));
    }

    /**
     * Initiate payment for a consultation
     */
    public function initiatePayment($id)
    {
        $patient = Auth::guard('patient')->user();
        
        $consultation = $patient->consultations()
            ->with('doctor')
            ->findOrFail($id);

        // Check if already paid
        if ($consultation->isPaid()) {
            return redirect()->route('patient.payments')
                ->with('error', 'This consultation has already been paid for.');
        }

        // Check if doctor is assigned
        if (!$consultation->doctor) {
            return redirect()->route('patient.payments')
                ->with('error', 'No doctor assigned to this consultation yet.');
        }

        // Determine fee based on consultation type
        $fee = 0;
        if ($consultation->consultation_type === 'pay_now') {
            $fee = \App\Models\Setting::get('consultation_fee_pay_now', \App\Models\Setting::get('pay_now_consultation_fee', 4500));
        } elseif ($consultation->consultation_type === 'pay_later') {
            $fee = \App\Models\Setting::get('consultation_fee_pay_later', \App\Models\Setting::get('pay_later_consultation_fee', 5000));
        } else {
            // Fallback to doctor's effective fee
            $fee = $consultation->doctor->effective_consultation_fee ?? 0;
        }

        if ($fee <= 0) {
            return redirect()->route('patient.payments')
                ->with('error', 'No payment is required for this consultation.');
        }

        // Redirect to payment request page
        return redirect()->route('payment.request', ['reference' => $consultation->reference]);
    }

    /**
     * Show new consultation form
     */
    public function newConsultation(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        
        // Ensure patient is authenticated
        if (!$patient) {
            return redirect()->route('patient.login')
                ->with('error', 'Please login to access your patient dashboard.');
        }
        
        $doctors = Doctor::available()->ordered()->get();
        $specialties = Specialty::active()->orderBy('name')->get();
        
        // Get consultation fees from settings (check both possible key names)
        $payLaterFee = Setting::get('consultation_fee_pay_later', Setting::get('pay_later_consultation_fee', 5000));
        $payNowFee = Setting::get('consultation_fee_pay_now', Setting::get('pay_now_consultation_fee', 4500));
        
        // Get pre-selected consultation type from query parameter
        $selectedType = $request->get('type', 'pay_later');
        if (!in_array($selectedType, ['pay_now', 'pay_later'])) {
            $selectedType = 'pay_later';
        }
        
        // Get pre-selected doctor from query parameter
        $selectedDoctorId = $request->get('doctor_id');
        
        return view('patient.new-consultation', compact('patient', 'doctors', 'specialties', 'payLaterFee', 'payNowFee', 'selectedType', 'selectedDoctorId'));
    }

    /**
     * Store new consultation
     */
    public function storeConsultation(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        
        try {
            // Sanitize inputs before validation (but preserve checkbox values)
            $allInputs = $request->all();
            $sanitized = [];
            
            foreach ($allInputs as $key => $value) {
                // Skip sanitization for checkboxes and file inputs
                if (in_array($key, ['informed_consent', 'data_privacy', '_token']) || $key === 'medical_documents') {
                    $sanitized[$key] = $value;
                } elseif (is_string($value)) {
                    $sanitized[$key] = $this->sanitizeText($value);
                } elseif (is_array($value)) {
                    $sanitized[$key] = $this->sanitizeArray($value);
                } else {
                    $sanitized[$key] = $value;
                }
            }
            
            $request->merge($sanitized);
            
            $validated = $request->validate([
                'consultation_type' => 'required|in:pay_now,pay_later',
                'problem' => ['required', 'string', 'min:10', 'max:500', 'regex:/^[\p{L}\p{N}\s\.,;:!?()\-\'"]+$/u'],
                'symptoms' => ['nullable', 'string', 'max:1000', 'regex:/^[\p{L}\p{N}\s\.,;:!?()\-\'"]*$/u'],
                'medical_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
                'severity' => 'required|in:mild,moderate,severe',
                'emergency_symptoms' => 'nullable|array',
                'emergency_symptoms.*' => 'required|string|in:chest_pain,difficulty_breathing,severe_bleeding,loss_of_consciousness,severe_allergic_reaction,severe_burns',
                'doctor_id' => 'nullable|integer|exists:doctors,id',
                'consult_mode' => 'required|in:voice,video,chat',
                'informed_consent' => 'required|accepted',
                'data_privacy' => 'required|accepted',
            ], [
                'consultation_type.required' => 'Please select a consultation type.',
                'consultation_type.in' => 'Invalid consultation type selected.',
                'problem.required' => 'Please describe your medical problem.',
                'problem.min' => 'Problem description must be at least 10 characters.',
                'problem.max' => 'Problem description cannot exceed 500 characters.',
                'problem.regex' => 'Problem description contains invalid characters.',
                'symptoms.max' => 'Symptoms description cannot exceed 1000 characters.',
                'symptoms.regex' => 'Symptoms description contains invalid characters.',
                'medical_documents.*.file' => 'Invalid file uploaded.',
                'medical_documents.*.mimes' => 'Only PDF, JPG, PNG, DOC, and DOCX files are allowed.',
                'medical_documents.*.max' => 'Each file must not exceed 5MB.',
                'severity.required' => 'Please indicate the severity of your condition.',
                'severity.in' => 'Invalid severity level selected.',
                'emergency_symptoms.array' => 'Emergency symptoms must be an array.',
                'emergency_symptoms.*.in' => 'Invalid emergency symptom selected.',
                'doctor_id.integer' => 'Invalid doctor selected.',
                'doctor_id.exists' => 'Selected doctor does not exist.',
                'consult_mode.required' => 'Please select a consultation mode.',
                'consult_mode.in' => 'Invalid consultation mode selected.',
                'informed_consent.required' => 'You must accept the informed consent.',
                'informed_consent.accepted' => 'You must accept the informed consent to proceed.',
                'data_privacy.required' => 'You must accept the data privacy policy.',
                'data_privacy.accepted' => 'You must accept the data privacy policy to proceed.',
            ]);

            // Generate unique consultation reference
            $reference = 'CONSULT-' . time() . '-' . Str::random(6);

            // Handle medical document uploads with sanitization
            $uploadedDocuments = [];
            if ($request->hasFile('medical_documents')) {
                foreach ($request->file('medical_documents') as $file) {
                    // Sanitize file name
                    $originalName = $file->getClientOriginalName();
                    $sanitizedOriginalName = $this->sanitizeFileName($originalName);
                    $fileName = time() . '_' . uniqid() . '_' . $sanitizedOriginalName;
                    
                    // Additional file validation
                    $mimeType = $file->getMimeType();
                    $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    
                    if (!in_array($mimeType, $allowedMimes)) {
                        return redirect()->back()
                            ->withErrors(['medical_documents' => 'Invalid file type detected.'])
                            ->withInput();
                    }
                    
                    $filePath = $file->storeAs('medical_documents', $fileName);
                    
                    $uploadedDocuments[] = [
                        'original_name' => $sanitizedOriginalName,
                        'stored_name' => $fileName,
                        'path' => $filePath,
                        'size' => $file->getSize(),
                        'mime_type' => $mimeType,
                    ];
                }
            }

            // Determine if payment is required first
            $requiresPaymentFirst = $validated['consultation_type'] === 'pay_now';

            // Split patient name into first and last name
            $nameParts = explode(' ', $patient->name, 2);
            $firstName = $nameParts[0] ?? $patient->name;
            $lastName = $nameParts[1] ?? '';

            // Create consultation
            $consultation = Consultation::create([
                'reference' => $reference,
                'patient_id' => $patient->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $patient->email,
                'mobile' => $patient->phone,
                'age' => $patient->age,
                'gender' => $patient->gender,
                'problem' => $this->sanitizeText($validated['problem']),
                'symptoms' => isset($validated['symptoms']) ? $this->sanitizeText($validated['symptoms']) : null,
                'medical_documents' => !empty($uploadedDocuments) ? $uploadedDocuments : null,
                'severity' => $validated['severity'],
                'emergency_symptoms' => $validated['emergency_symptoms'] ?? null,
                'consult_mode' => $validated['consult_mode'],
                'doctor_id' => $validated['doctor_id'] ?? null,
                'consultation_type' => $validated['consultation_type'],
                'requires_payment_first' => $requiresPaymentFirst,
                'status' => $requiresPaymentFirst ? 'pending_payment' : 'pending',
                'payment_status' => 'unpaid',
            ]);

            // Update patient aggregates
            $patient->increment('consultations_count');
            $patient->last_consultation_at = now();
            $patient->save();

            // Get doctor email and name if doctor is assigned
            $doctorEmail = null;
            $doctorName = null;
            if ($validated['doctor_id']) {
                $assignedDoctor = Doctor::find($validated['doctor_id']);
                $doctorEmail = $assignedDoctor->email ?? null;
                $doctorName = $assignedDoctor->name ?? null;
            }

            // Prepare data for email notifications
            $emailData = [
                'consultation_reference' => $reference,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => $patient->name,
                'email' => $patient->email,
                'mobile' => $patient->phone,
                'age' => $patient->age,
                'gender' => $patient->gender,
                'problem' => $validated['problem'],
                'symptoms' => $validated['symptoms'] ?? null,
                'severity' => $validated['severity'],
                'consult_mode' => $validated['consult_mode'],
                'consultation_type' => $validated['consultation_type'],
                'has_documents' => !empty($uploadedDocuments),
                'documents_count' => count($uploadedDocuments),
                'doctor_id' => $validated['doctor_id'] ?? null,
                'doctor_name' => $doctorName,
                'doctor_email' => $doctorEmail,
            ];

            // Queue emails for asynchronous sending (non-critical - continue even if they fail)
            $emailsQueued = 0;
            $adminEmail = config('mail.admin_email');

            // Send confirmation email to patient
            try {
                Mail::to($patient->email)->queue(new ConsultationConfirmation($emailData));
                $emailsQueued++;
                \Log::info('Patient confirmation email queued successfully', [
                    'consultation_reference' => $reference,
                    'patient_email' => $patient->email
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to queue patient confirmation email: ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'patient_email' => $patient->email
                ]);
            }

            // Send SMS confirmation to patient
            try {
                $smsNotification = new ConsultationSmsNotification();
                $smsResult = $smsNotification->sendConsultationConfirmation($emailData);
                
                if ($smsResult['success']) {
                    \Log::info('Patient confirmation SMS sent successfully', [
                        'consultation_reference' => $reference,
                        'patient_mobile' => $patient->phone
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send patient confirmation SMS: ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'patient_mobile' => $patient->phone ?? 'N/A'
                ]);
            }

            // Send alert email to admin
            try {
                Mail::to($adminEmail)->queue(new ConsultationAdminAlert($emailData));
                $emailsQueued++;
                \Log::info('Admin alert email queued successfully', [
                    'consultation_reference' => $reference,
                    'admin_email' => $adminEmail
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to queue admin alert email: ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'admin_email' => $adminEmail
                ]);
            }

            // Send notification email to assigned doctor (if doctor is assigned)
            if ($doctorEmail) {
                try {
                    Mail::to($doctorEmail)->queue(new ConsultationDoctorNotification($emailData));
                    $emailsQueued++;
                    \Log::info('Doctor notification email queued successfully', [
                        'consultation_reference' => $reference,
                        'doctor_email' => $doctorEmail
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to queue doctor notification email: ' . $e->getMessage(), [
                        'consultation_reference' => $reference,
                        'doctor_email' => $doctorEmail
                    ]);
                }

                // Send SMS notification to assigned doctor
                try {
                    $assignedDoctor = Doctor::find($validated['doctor_id']);
                    if ($assignedDoctor && $assignedDoctor->phone) {
                        $smsNotification = new ConsultationSmsNotification();
                        $doctorSmsResult = $smsNotification->sendDoctorNewConsultation($assignedDoctor, $emailData);
                        
                        if ($doctorSmsResult['success']) {
                            \Log::info('Doctor notification SMS sent successfully', [
                                'consultation_reference' => $reference,
                                'doctor_id' => $validated['doctor_id'],
                                'doctor_phone' => $assignedDoctor->phone
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to send doctor notification SMS: ' . $e->getMessage(), [
                        'consultation_reference' => $reference,
                        'doctor_id' => $validated['doctor_id'] ?? 'N/A'
                    ]);
                }
            }

            \Log::info('Patient consultation created - emails queued', [
                'consultation_reference' => $reference,
                'total_emails_queued' => $emailsQueued,
                'patient_email' => $patient->email,
                'admin_email' => $adminEmail,
                'doctor_email' => $doctorEmail ?? 'N/A'
            ]);

            // If pay now, redirect to payment page (to be implemented)
            if ($requiresPaymentFirst) {
                return redirect()->route('patient.consultations')
                    ->with('success', 'Consultation created successfully. Please complete payment to proceed.');
            }

            return redirect()->route('patient.consultations')
                ->with('success', 'Consultation created successfully. You will be notified once a doctor is assigned.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Failed to create patient consultation: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['medical_documents', '_token', 'password'])
            ]);
            
            return redirect()->back()
                ->with('error', 'Unable to create consultation: ' . $e->getMessage() . '. Please check all fields and try again.')
                ->withInput();
        }
    }

    /**
     * Sanitize all input data
     */
    private function sanitizeInputs(array $inputs): array
    {
        $sanitized = [];
        
        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeText($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize text input - removes HTML tags, XSS attempts, and normalizes whitespace
     */
    private function sanitizeText(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }
        
        // Remove HTML tags and PHP tags
        $text = strip_tags($text);
        
        // Remove null bytes and other control characters (except newlines and tabs)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Normalize whitespace (multiple spaces to single space)
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Trim whitespace
        $text = trim($text);
        
        // Escape special characters for database storage
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
        
        return $text;
    }

    /**
     * Sanitize array input
     */
    private function sanitizeArray(array $array): array
    {
        $sanitized = [];
        
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeText($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize file name to prevent directory traversal and other attacks
     */
    private function sanitizeFileName(string $fileName): string
    {
        // Remove path components
        $fileName = basename($fileName);
        
        // Remove any remaining directory separators
        $fileName = str_replace(['/', '\\', '..'], '', $fileName);
        
        // Remove special characters except alphanumeric, dots, hyphens, and underscores
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        
        // Limit length
        $fileName = substr($fileName, 0, 255);
        
        return $fileName;
    }

    /**
     * Store or update menstrual cycle
     */
    public function storeMenstrualCycle(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        
        // Only allow for female patients
        if (strtolower($patient->gender) !== 'female') {
            return response()->json(['error' => 'This feature is only available for female patients.'], 403);
        }
        
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period_length' => 'nullable|integer|min:1|max:10',
            'flow_intensity' => 'nullable|in:light,moderate,heavy',
            'symptoms' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Sanitize inputs
        $validated['notes'] = isset($validated['notes']) ? $this->sanitizeText($validated['notes']) : null;
        
        // Calculate period length if not provided
        if (!isset($validated['period_length']) && isset($validated['start_date']) && isset($validated['end_date'])) {
            $start = \Carbon\Carbon::parse($validated['start_date']);
            $end = \Carbon\Carbon::parse($validated['end_date']);
            $validated['period_length'] = $start->diffInDays($end) + 1;
        }
        
        // Check if there's an active cycle that should be ended
        $activeCycle = MenstrualCycle::where('patient_id', $patient->id)
            ->whereNull('end_date')
            ->where('start_date', '<', $validated['start_date'])
            ->first();
        
        if ($activeCycle) {
            // End the previous cycle
            $activeCycle->update([
                'end_date' => \Carbon\Carbon::parse($validated['start_date'])->subDay(),
                'period_length' => $activeCycle->calculatePeriodLength(),
                'cycle_length' => $activeCycle->calculateCycleLength(),
            ]);
        }
        
        // Create new cycle
        $cycle = MenstrualCycle::create([
            'patient_id' => $patient->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'period_length' => $validated['period_length'] ?? null,
            'flow_intensity' => $validated['flow_intensity'] ?? null,
            'symptoms' => $validated['symptoms'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);
        
        // Calculate cycle length
        $cycle->cycle_length = $cycle->calculateCycleLength();
        $cycle->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Menstrual cycle recorded successfully.',
            'cycle' => $cycle->load('patient'),
        ]);
    }

    /**
     * Update menstrual cycle
     */
    public function updateMenstrualCycle(Request $request, $id)
    {
        $patient = Auth::guard('patient')->user();
        
        $cycle = MenstrualCycle::where('patient_id', $patient->id)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period_length' => 'nullable|integer|min:1|max:10',
            'flow_intensity' => 'nullable|in:light,moderate,heavy',
            'symptoms' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Sanitize inputs
        if (isset($validated['notes'])) {
            $validated['notes'] = $this->sanitizeText($validated['notes']);
        }
        
        // Calculate period length if not provided
        if (!isset($validated['period_length']) && isset($validated['start_date']) && isset($validated['end_date'])) {
            $start = \Carbon\Carbon::parse($validated['start_date']);
            $end = \Carbon\Carbon::parse($validated['end_date']);
            $validated['period_length'] = $start->diffInDays($end) + 1;
        }
        
        $cycle->update($validated);
        
        // Recalculate cycle length
        $cycle->cycle_length = $cycle->calculateCycleLength();
        $cycle->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Menstrual cycle updated successfully.',
            'cycle' => $cycle->load('patient'),
        ]);
    }
}
