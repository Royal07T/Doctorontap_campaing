<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use App\Mail\ConsultationStatusChange;

class DashboardController extends Controller
{
    /**
     * Display doctor dashboard
     */
    public function index()
    {
        $doctor = Auth::guard('doctor')->user();
        
        // Get doctor payment percentage from settings
        $doctorPercentage = \App\Models\Setting::get('doctor_payment_percentage', 70);
        
        // Get all paid consultations
        $paidConsultations = Consultation::where('doctor_id', $doctor->id)
                                         ->where('payment_status', 'paid')
                                         ->get();
        
        // Calculate total earnings from paid consultations
        // Earnings = Sum of (effective consultation fee * doctor percentage) for all paid consultations
        $totalEarnings = $paidConsultations->sum(function($consultation) use ($doctor, $doctorPercentage) {
            $consultationFee = $doctor->effective_consultation_fee ?? 0;
            return ($consultationFee * $doctorPercentage) / 100;
        });
        
        $stats = [
            'total_consultations' => Consultation::where('doctor_id', $doctor->id)->count(),
            'pending_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                    ->where('status', 'pending')->count(),
            'scheduled_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                     ->where('status', 'scheduled')->count(),
            'completed_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                     ->where('status', 'completed')->count(),
            'paid_consultations' => $paidConsultations->count(),
            'pending_payments' => Consultation::where('doctor_id', $doctor->id)
                                              ->where('payment_status', 'pending')->count(),
            'total_earnings' => $totalEarnings, // Current earnings from paid consultations
        ];

        // Get recent consultations with payment information
        $recentConsultations = Consultation::where('doctor_id', $doctor->id)
                                           ->with(['payment', 'booking'])
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
        
        $query = Consultation::where('doctor_id', $doctor->id)->with(['payment', 'booking']);
        
        // Filter by consultation status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Search by patient name, email, or reference
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                
                // If search contains a space, also try searching first and last name separately
                if (strpos($search, ' ') !== false) {
                    $parts = explode(' ', trim($search), 2);
                    if (count($parts) == 2) {
                        $q->orWhere(function($subQ) use ($parts) {
                            $subQ->where('first_name', 'like', "%{$parts[0]}%")
                                 ->where('last_name', 'like', "%{$parts[1]}%");
                        });
                        // Also try reversed in case user typed "last first"
                        $q->orWhere(function($subQ) use ($parts) {
                            $subQ->where('first_name', 'like', "%{$parts[1]}%")
                                 ->where('last_name', 'like', "%{$parts[0]}%");
                        });
                    }
                }
            });
        }
        
        // Calculate statistics
        $stats = [
            'total' => Consultation::where('doctor_id', $doctor->id)->count(),
            'paid' => Consultation::where('doctor_id', $doctor->id)->where('payment_status', 'paid')->count(),
            'unpaid' => Consultation::where('doctor_id', $doctor->id)
                ->where('status', 'completed')
                ->where('payment_status', '!=', 'paid')->count(),
            'pending' => Consultation::where('doctor_id', $doctor->id)->where('status', 'pending')->count(),
            'completed' => Consultation::where('doctor_id', $doctor->id)->where('status', 'completed')->count(),
        ];
        
        $consultations = $query->latest()->paginate(20);
        
        return view('doctor.consultations', compact('consultations', 'stats'));
    }

    /**
     * Update consultation status
     */
    public function updateConsultationStatus(Request $request, $id)
    {
        try {
            \Log::info('Consultation status update request', [
                'id' => $id,
                'request_data' => $request->all(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);
            
            $doctor = Auth::guard('doctor')->user();
            
            if (!$doctor) {
                \Log::error('No authenticated doctor found');
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            $validated = $request->validate([
                'status' => 'required|in:pending,scheduled,completed,cancelled',
                'notes' => 'nullable|string|max:1000',
            ]);
            
            \Log::info('Validation passed', ['validated_data' => $validated]);
            
            // Store old status for notification
            $oldStatus = $consultation->status;
            $newStatus = $validated['status'];
            
            $consultation->update([
                'status' => $newStatus,
                'doctor_notes' => $validated['notes'] ?? $consultation->doctor_notes,
                'consultation_completed_at' => $newStatus === 'completed' ? now() : $consultation->consultation_completed_at,
            ]);
            
            // Send notification to admin if status changed
            if ($oldStatus !== $newStatus) {
                $adminEmail = config('mail.admin_email');
                
                Mail::to($adminEmail)->send(new ConsultationStatusChange(
                    $consultation,
                    $doctor,
                    $oldStatus,
                    $newStatus
                ));
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Consultation status updated successfully! Admin has been notified.',
                'status' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Consultation status update failed', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View single consultation details
     */
    public function viewConsultation(Request $request, $id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->with(['doctor', 'payment', 'canvasser', 'nurse', 'booking'])
                                       ->firstOrFail();
            
            // If request wants JSON (AJAX), return JSON
            if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'consultation' => [
                    'id' => $consultation->id,
                    'reference' => $consultation->reference,
                    'patient_name' => $consultation->first_name . ' ' . $consultation->last_name,
                    'email' => $consultation->email,
                    'mobile' => $consultation->mobile,
                    'age' => $consultation->age,
                    'gender' => ucfirst($consultation->gender),
                    'symptoms' => $consultation->problem,
                    'status' => ucfirst($consultation->status),
                        'payment_status' => ucfirst($consultation->payment_status ?? 'unpaid'),
                    'created_at' => $consultation->created_at->format('M d, Y h:i A'),
                    'medical_documents' => $consultation->medical_documents,
                    'doctor_notes' => $consultation->doctor_notes,
                    'diagnosis' => $consultation->diagnosis,
                    'treatment_plan' => $consultation->treatment_plan,
                    'prescribed_medications' => $consultation->prescribed_medications,
                    'follow_up_instructions' => $consultation->follow_up_instructions,
                    'lifestyle_recommendations' => $consultation->lifestyle_recommendations,
                    'referrals' => $consultation->referrals,
                    'next_appointment_date' => $consultation->next_appointment_date ? $consultation->next_appointment_date->format('Y-m-d') : null,
                    'additional_notes' => $consultation->additional_notes,
                    'has_treatment_plan' => $consultation->hasTreatmentPlan(),
                    'treatment_plan_accessible' => $consultation->isTreatmentPlanAccessible(),
                    'requires_payment' => $consultation->requiresPaymentForTreatmentPlan(),
                    'canvasser' => $consultation->canvasser ? $consultation->canvasser->name : 'N/A',
                    'nurse' => $consultation->nurse ? $consultation->nurse->name : 'Not Assigned',
                ]
            ]);
            }
            
            // Otherwise return HTML view
            return view('doctor.consultation-details', compact('consultation'));
            
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load consultation: ' . $e->getMessage()
            ], 500);
            }
            
            return redirect()->route('doctor.consultations')
                ->with('error', 'Consultation not found or you do not have access to it.');
        }
    }

    /**
     * Create or update treatment plan
     */
    public function updateTreatmentPlan(Request $request, $id)
    {
        \Illuminate\Support\Facades\Log::info('updateTreatmentPlan called', [
            'consultation_id' => $id,
            'doctor_id' => Auth::guard('doctor')->id(),
            'request_data_keys' => array_keys($request->all())
        ]);
        
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::with(['patient', 'booking'])
                                       ->where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            \Illuminate\Support\Facades\Log::info('Consultation found for treatment plan update', [
                'consultation_id' => $consultation->id,
                'reference' => $consultation->reference,
                'email' => $consultation->email,
                'patient_id' => $consultation->patient_id,
                'patient_email' => $consultation->patient->email ?? 'N/A',
                'booking_id' => $consultation->booking_id,
                'payer_email' => $consultation->booking->payer_email ?? 'N/A',
                'payment_status' => $consultation->payment_status,
                'is_paid' => $consultation->isPaid(),
                'treatment_plan_created' => $consultation->treatment_plan_created
            ]);
            
            // Check if it's an update or create
            $isUpdate = $consultation->treatment_plan_created;
            
            $validated = $request->validate([
                // Medical Format Fields
                'presenting_complaint' => 'required|string|max:2000',
                'history_of_complaint' => 'required|string|max:5000',
                'past_medical_history' => 'nullable|string|max:2000',
                'family_history' => 'nullable|string|max:2000',
                'drug_history' => 'nullable|string|max:2000',
                'social_history' => 'nullable|string|max:2000',
                'diagnosis' => 'required|string|max:2000',
                'investigation' => 'nullable|string|max:5000',
                'treatment_plan' => 'required|string|max:5000',
                // Additional fields
                'prescribed_medications' => 'nullable|array',
                'prescribed_medications.*.name' => 'required_with:prescribed_medications|string|max:255',
                'prescribed_medications.*.dosage' => 'required_with:prescribed_medications|string|max:255',
                'prescribed_medications.*.frequency' => 'required_with:prescribed_medications|string|max:255',
                'prescribed_medications.*.duration' => 'required_with:prescribed_medications|string|max:255',
                'follow_up_instructions' => 'nullable|string|max:2000',
                'lifestyle_recommendations' => 'nullable|string|max:2000',
                'referrals' => 'nullable|array',
                'referrals.*.specialist' => 'required_with:referrals|string|max:255',
                'referrals.*.reason' => 'required_with:referrals|string|max:500',
                'referrals.*.urgency' => 'required_with:referrals|in:routine,urgent,emergency',
                'next_appointment_date' => 'nullable|date|after:today',
                'additional_notes' => 'nullable|string|max:2000',
            ]);
            
            // Update consultation with treatment plan
            $updateData = [
                // Medical Format Fields
                'presenting_complaint' => $validated['presenting_complaint'],
                'history_of_complaint' => $validated['history_of_complaint'],
                'past_medical_history' => $validated['past_medical_history'] ?? null,
                'family_history' => $validated['family_history'] ?? null,
                'drug_history' => $validated['drug_history'] ?? null,
                'social_history' => $validated['social_history'] ?? null,
                'diagnosis' => $validated['diagnosis'],
                'investigation' => $validated['investigation'] ?? null,
                'treatment_plan' => $validated['treatment_plan'],
                // Additional fields
                'prescribed_medications' => $validated['prescribed_medications'] ?? null,
                'follow_up_instructions' => $validated['follow_up_instructions'] ?? null,
                'lifestyle_recommendations' => $validated['lifestyle_recommendations'] ?? null,
                'referrals' => $validated['referrals'] ?? null,
                'next_appointment_date' => $validated['next_appointment_date'] ?? null,
                'additional_notes' => $validated['additional_notes'] ?? null,
                'treatment_plan_created' => true,
            ];
            
            // Only set these on first create, not on update
            if (!$isUpdate) {
                $updateData['treatment_plan_created_at'] = now();
                $updateData['status'] = 'completed';
                $updateData['consultation_completed_at'] = now();
            }
            
            $consultation->update($updateData);

            // Sync to patient medical history
            $historyService = app(\App\Services\PatientMedicalHistoryService::class);
            $historyService->syncConsultationToHistory($consultation);
            
            \Illuminate\Support\Facades\Log::info($isUpdate ? 'Treatment plan updated' : 'Treatment plan created', [
                'consultation_id' => $consultation->id,
                'doctor_id' => $doctor->id,
                'is_update' => $isUpdate
            ]);

            // Send payment request email when treatment plan is created/updated and payment hasn't been made
            \Illuminate\Support\Facades\Log::info('Checking if payment request email should be sent', [
                'consultation_id' => $consultation->id,
                'is_paid' => $consultation->isPaid(),
                'payment_status' => $consultation->payment_status
            ]);
            
            if (!$consultation->isPaid()) {
                \Illuminate\Support\Facades\Log::info('Consultation is not paid, proceeding to send payment request email', [
                    'consultation_id' => $consultation->id
                ]);
                
                try {
                    // Determine recipient email: check multiple sources
                    $recipientEmail = null;
                    
                    // 1. First try consultation email field
                    if (!empty($consultation->email)) {
                        $recipientEmail = $consultation->email;
                        \Illuminate\Support\Facades\Log::info('Using consultation email for payment request', [
                            'consultation_id' => $consultation->id,
                            'email' => $recipientEmail
                        ]);
                    }
                    // 2. Try patient relationship email
                    elseif ($consultation->patient && !empty($consultation->patient->email)) {
                        $recipientEmail = $consultation->patient->email;
                        \Illuminate\Support\Facades\Log::info('Using patient email for payment request', [
                            'consultation_id' => $consultation->id,
                            'patient_id' => $consultation->patient_id,
                            'email' => $recipientEmail
                        ]);
                    }
                    // 3. Try booking payer email (for multi-patient bookings)
                    elseif ($consultation->booking && !empty($consultation->booking->payer_email)) {
                        $recipientEmail = $consultation->booking->payer_email;
                        \Illuminate\Support\Facades\Log::info('Using payer email for payment request', [
                            'consultation_id' => $consultation->id,
                            'booking_id' => $consultation->booking_id,
                            'payer_email' => $recipientEmail
                        ]);
                    }
                    
                    if ($recipientEmail) {
                        \Illuminate\Support\Facades\Log::info('Attempting to send payment request email', [
                            'consultation_id' => $consultation->id,
                            'email' => $recipientEmail,
                            'mail_driver' => config('mail.default'),
                            'mail_host' => config('mail.mailers.smtp.host')
                        ]);
                        
                        \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\PaymentRequest($consultation));
                        
                        \Illuminate\Support\Facades\Log::info('Payment request email sent successfully after treatment plan ' . ($isUpdate ? 'update' : 'creation'), [
                            'consultation_id' => $consultation->id,
                            'reference' => $consultation->reference,
                            'email' => $recipientEmail,
                            'is_update' => $isUpdate
                        ]);
                    } else {
                        \Illuminate\Support\Facades\Log::warning('No email available to send payment request', [
                            'consultation_id' => $consultation->id,
                            'reference' => $consultation->reference,
                            'consultation_email' => $consultation->email ?? 'null',
                            'patient_id' => $consultation->patient_id,
                            'patient_email' => $consultation->patient->email ?? 'null',
                            'booking_id' => $consultation->booking_id,
                            'payer_email' => $consultation->booking->payer_email ?? 'null'
                        ]);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send payment request email after treatment plan ' . ($isUpdate ? 'update' : 'creation'), [
                        'consultation_id' => $consultation->id,
                        'email' => $consultation->email ?? 'N/A',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    // Don't fail the request if email fails
                }
            } else {
                \Illuminate\Support\Facades\Log::info('Skipping payment request email - consultation already paid', [
                    'consultation_id' => $consultation->id,
                    'payment_status' => $consultation->payment_status,
                    'is_paid' => $consultation->isPaid()
                ]);
            }
            
            \Illuminate\Support\Facades\Log::info('Treatment plan update completed successfully', [
                'consultation_id' => $consultation->id,
                'is_update' => $isUpdate
            ]);
            
            return response()->json([
                'success' => true,
                'message' => $isUpdate 
                    ? 'Treatment plan updated successfully! Changes saved to patient medical history.' 
                    : 'Treatment plan created successfully! Patient will need to pay to access it.',
                'treatment_plan_created' => true,
                'is_update' => $isUpdate
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Validation failed for treatment plan update', [
                'consultation_id' => $id,
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to save treatment plan', [
                'consultation_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save treatment plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-save treatment plan (draft mode)
     */
    public function autoSaveTreatmentPlan(Request $request, $id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            // Save whatever data is provided (no validation for drafts)
            $data = $request->only([
                'presenting_complaint',
                'history_of_complaint',
                'past_medical_history',
                'family_history',
                'drug_history',
                'social_history',
                'diagnosis',
                'investigation',
                'treatment_plan',
                'prescribed_medications',
                'follow_up_instructions',
                'lifestyle_recommendations',
                'referrals',
                'next_appointment_date',
                'additional_notes',
            ]);
            
            $consultation->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Draft saved',
                'timestamp' => now()->format('H:i:s')
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to auto-save treatment plan', [
                'consultation_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Auto-save failed'
            ], 500);
        }
    }

    /**
     * Get patient's previous medical history for pre-filling
     */
    public function getPatientHistory($id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            $historyService = app(\App\Services\PatientMedicalHistoryService::class);
            $previousHistory = $historyService->getPreviousHistoryForConsultation($consultation);
            
            return response()->json([
                'success' => true,
                'has_history' => $previousHistory !== null,
                'history' => $previousHistory ? [
                    'past_medical_history' => $previousHistory->past_medical_history,
                    'family_history' => $previousHistory->family_history,
                    'drug_history' => $previousHistory->drug_history,
                    'social_history' => $previousHistory->social_history,
                    'last_consultation_date' => $previousHistory->consultation_date->format('Y-m-d'),
                ] : null
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load patient history'
            ], 500);
        }
    }

    /**
     * Display bank account management page
     */
    public function bankAccounts()
    {
        $doctor = Auth::guard('doctor')->user();
        $bankAccounts = $doctor->bankAccounts()->latest()->get();
        $defaultAccount = $doctor->defaultBankAccount;
        $banks = \App\Models\Bank::getActiveBanks();

        return view('doctor.bank-accounts', compact('bankAccounts', 'defaultAccount', 'banks'));
    }

    /**
     * Verify bank account with KoraPay (AJAX endpoint)
     */
    public function verifyBankAccount(Request $request)
    {
        try {
            $validated = $request->validate([
                'bank_code' => 'required|string|max:10',
                'account_number' => 'required|string|max:20',
            ]);

            $payoutService = app(\App\Services\KoraPayPayoutService::class);
            $verification = $payoutService->verifyBankAccount(
                $validated['bank_code'],
                $validated['account_number']
            );

            if ($verification['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $verification['data'],
                    'message' => $verification['message'] ?? 'Bank account verified successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $verification['message'] ?? 'Bank account verification failed'
                ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $errorMessages)
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Bank account verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new bank account
     */
    public function storeBankAccount(Request $request)
    {
        try {
            $doctor = Auth::guard('doctor')->user();

            $validated = $request->validate([
                'bank_id' => 'required|exists:banks,id',
                'account_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:20',
                'account_type' => 'nullable|string|max:50',
                'swift_code' => 'nullable|string|max:20',
                'notes' => 'nullable|string|max:500',
            ]);

            // Get bank details
            $bank = \App\Models\Bank::findOrFail($validated['bank_id']);

            // Check if this bank account already exists for this doctor (including soft-deleted)
            $existingAccount = \App\Models\DoctorBankAccount::withTrashed()
                ->where('doctor_id', $doctor->id)
                ->where('account_number', $validated['account_number'])
                ->where('bank_code', $bank->code)
                ->first();

            if ($existingAccount) {
                if ($existingAccount->trashed()) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'This bank account was previously added and deleted. Please contact support if you need to restore it.');
                }
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'This bank account has already been added to your account. Please use a different account number or edit the existing account.');
            }

            // Verify account with KoraPay before saving
            $payoutService = app(\App\Services\KoraPayPayoutService::class);
            $verification = $payoutService->verifyBankAccount(
                $bank->code,
                $validated['account_number']
            );

            if (!$verification['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Bank account verification failed: ' . $verification['message'] . '. Please check your account number and try again.');
            }

            // Extract account name from verification if available
            $verifiedAccountName = $verification['data']['account_name'] ?? $validated['account_name'];

            // Check if this is the first bank account (excluding soft-deleted)
            $isFirstAccount = !$doctor->bankAccounts()->exists();
            
            // Use database transaction to ensure atomicity
            \DB::beginTransaction();
            try {
                // Always unset any existing default accounts first (including soft-deleted)
                // Since we've removed the problematic unique constraint, we can safely update all defaults
                \DB::table('doctor_bank_accounts')
                    ->where('doctor_id', $doctor->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);

                $bankAccount = $doctor->bankAccounts()->create([
                    'bank_name' => $bank->name,
                    'bank_code' => $bank->code,
                    'account_name' => $verifiedAccountName,
                    'account_number' => $validated['account_number'],
                    'account_type' => $validated['account_type'] ?? null,
                    'swift_code' => $validated['swift_code'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'is_default' => $isFirstAccount,
                    'is_verified' => true, // Auto-verified since KoraPay verified it
                    'verified_at' => now(),
                ]);

                \DB::commit();
            } catch (QueryException $e) {
                \DB::rollBack();
                
                // Handle specific database constraint violations
                if ($e->getCode() === '23000') {
                    $errorMessage = $e->getMessage();
                    
                    // Check for unique constraint violations
                    if (str_contains($errorMessage, 'unique_default_bank_per_doctor')) {
                        \Log::error('Bank account unique constraint violation', [
                            'doctor_id' => $doctor->id,
                            'error' => $errorMessage
                        ]);
                        
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Unable to add bank account. There was a conflict with your existing accounts. Please try again or contact support if the issue persists.');
                    }
                    
                    // Generic duplicate entry error
                    if (str_contains($errorMessage, 'Duplicate entry')) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'This bank account already exists in your account. Please use a different account number or edit the existing account.');
                    }
                }
                
                // Re-throw if it's not a constraint violation we can handle
                throw $e;
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }

            return redirect()->back()->with('success', 'Bank account added and verified successfully! You can now receive payments.');

        } catch (QueryException $e) {
            \Log::error('Failed to add bank account - Database error', [
                'doctor_id' => Auth::guard('doctor')->id(),
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            // Handle database constraint violations with friendly messages
            if ($e->getCode() === '23000') {
                $errorMessage = $e->getMessage();
                
                if (str_contains($errorMessage, 'unique_default_bank_per_doctor')) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Unable to add bank account. There was a conflict with your existing accounts. Please try again or contact support if the issue persists.');
                }
                
                if (str_contains($errorMessage, 'Duplicate entry')) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'This bank account already exists in your account. Please use a different account number or edit the existing account.');
                }
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to add bank account at this time. Please try again later or contact support if the problem continues.');
                
        } catch (\Exception $e) {
            \Log::error('Failed to add bank account', [
                'doctor_id' => Auth::guard('doctor')->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to add bank account at this time. Please try again later or contact support if the problem continues.');
        }
    }

    /**
     * Update bank account
     */
    public function updateBankAccount(Request $request, $id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();

            $bankAccount = $doctor->bankAccounts()->findOrFail($id);

            $validated = $request->validate([
                'bank_name' => 'required|string|max:255',
                'account_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:20',
                'account_type' => 'nullable|string|max:50',
                'bank_code' => 'nullable|string|max:10',
                'swift_code' => 'nullable|string|max:20',
                'notes' => 'nullable|string|max:500',
            ]);

            $bankAccount->update($validated);

            return redirect()->back()->with('success', 'Bank account updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to update bank account', [
                'doctor_id' => Auth::guard('doctor')->id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to update bank account: ' . $e->getMessage());
        }
    }

    /**
     * Set bank account as default
     */
    public function setDefaultBankAccount($id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            $bankAccount = $doctor->bankAccounts()->findOrFail($id);
            
            $bankAccount->setAsDefault();

            return redirect()->back()->with('success', 'Default bank account updated!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to set default account: ' . $e->getMessage());
        }
    }

    /**
     * Delete bank account
     */
    public function deleteBankAccount($id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            $bankAccount = $doctor->bankAccounts()->findOrFail($id);

            // Don't allow deletion of default account if there are others
            if ($bankAccount->is_default && $doctor->bankAccounts()->count() > 1) {
                return redirect()->back()->with('error', 'Cannot delete default account. Set another account as default first.');
            }

            $bankAccount->delete();

            return redirect()->back()->with('success', 'Bank account deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete bank account: ' . $e->getMessage());
        }
    }

    /**
     * Display payment history
     */
    public function paymentHistory()
    {
        $doctor = Auth::guard('doctor')->user();
        
        $payments = $doctor->payments()
            ->with(['bankAccount', 'paidBy'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total_paid' => $doctor->payments()->where('status', 'completed')->sum('doctor_amount'),
            'pending_amount' => $doctor->pending_earnings,
            'paid_consultations' => $doctor->paid_consultations_count,
            'unpaid_consultations' => $doctor->unpaid_consultations_count,
        ];

        return view('doctor.payment-history', compact('payments', 'stats'));
    }
}

