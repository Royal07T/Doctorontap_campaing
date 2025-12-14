<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'paid_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                ->where('payment_status', 'paid')->count(),
            'pending_payments' => Consultation::where('doctor_id', $doctor->id)
                                              ->where('payment_status', 'pending')->count(),
            'total_earnings' => Consultation::where('doctor_id', $doctor->id)
                                            ->where('payment_status', 'paid')
                                            ->with('payment')
                                            ->get()
                                            ->sum(function($consultation) {
                                                return $consultation->payment ? $consultation->payment->amount : 0;
                                            }),
        ];

        // Get recent consultations with payment information
        $recentConsultations = Consultation::where('doctor_id', $doctor->id)
                                           ->with('payment')
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
        
        $query = Consultation::where('doctor_id', $doctor->id)->with('payment');
        
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
            
            $newStatus = $validated['status'];
            
            $consultation->update([
                'status' => $newStatus,
                'doctor_notes' => $validated['notes'] ?? $consultation->doctor_notes,
                'consultation_completed_at' => $newStatus === 'completed' ? now() : $consultation->consultation_completed_at,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Consultation status updated successfully!',
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
    public function viewConsultation($id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->with(['doctor', 'payment', 'canvasser', 'nurse'])
                                       ->firstOrFail();
            
            // If it's an AJAX request, return JSON
            // Check for Accept header, X-Requested-With header, or wantsJson
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json' || request()->header('X-Requested-With') === 'XMLHttpRequest') {
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
                        // Treatment Plan Fields
                        'presenting_complaint' => $consultation->presenting_complaint,
                        'history_of_complaint' => $consultation->history_of_complaint,
                        'past_medical_history' => $consultation->past_medical_history,
                        'family_history' => $consultation->family_history,
                        'drug_history' => $consultation->drug_history,
                        'social_history' => $consultation->social_history,
                        'diagnosis' => $consultation->diagnosis,
                        'investigation' => $consultation->investigation,
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
            
            // For direct browser requests, return the consultation details view
            return view('doctor.consultation-details', compact('consultation'));
            
        } catch (\Exception $e) {
            // Always return JSON for API-like requests (check headers)
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json' || request()->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load consultation: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('doctor.consultations')
                ->with('error', 'Failed to load consultation: ' . $e->getMessage());
        }
    }

    /**
     * Create or update treatment plan
     */
    public function updateTreatmentPlan(Request $request, $id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
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

            // Only send notification email on first create, not on update
            if (!$isUpdate) {
                try {
                    Mail::to($consultation->email)->queue(new \App\Mail\TreatmentPlanReadyNotification($consultation));
                    \Illuminate\Support\Facades\Log::info('Treatment plan ready email queued successfully', [
                        'consultation_id' => $consultation->id,
                        'email' => $consultation->email
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to queue treatment plan ready email', [
                        'consultation_id' => $consultation->id,
                        'email' => $consultation->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => $isUpdate 
                    ? 'Treatment plan updated successfully! Changes saved to patient medical history.' 
                    : 'Treatment plan created successfully! Patient will need to pay to access it.',
                'treatment_plan_created' => true,
                'is_update' => $isUpdate
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to save treatment plan', [
                'consultation_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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

        return view('doctor.bank-accounts', compact('bankAccounts', 'defaultAccount'));
    }

    /**
     * Store a new bank account
     */
    public function storeBankAccount(Request $request)
    {
        try {
            $doctor = Auth::guard('doctor')->user();

            $validated = $request->validate([
                'bank_name' => 'required|string|max:255',
                'account_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:20',
                'account_type' => 'nullable|string|max:50',
                'bank_code' => 'nullable|string|max:10',
                'swift_code' => 'nullable|string|max:20',
                'notes' => 'nullable|string|max:500',
            ]);

            // Check if this is the first bank account
            $isFirstAccount = !$doctor->bankAccounts()->exists();

            $bankAccount = $doctor->bankAccounts()->create([
                ...$validated,
                'is_default' => $isFirstAccount, // First account becomes default
                'is_verified' => false,
            ]);

            return redirect()->back()->with('success', 'Bank account added successfully! It will be verified by admin.');

        } catch (\Exception $e) {
            \Log::error('Failed to add bank account', [
                'doctor_id' => Auth::guard('doctor')->id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to add bank account: ' . $e->getMessage());
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

    /**
     * Display doctor profile page
     */
    public function profile()
    {
        $doctor = Auth::guard('doctor')->user();
        return view('doctor.profile', compact('doctor'));
    }

    /**
     * Update doctor profile
     */
    public function updateProfile(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:doctors,email,' . $doctor->id,
            'gender' => 'nullable|in:Male,Female,male,female',
            'specialization' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'languages' => 'nullable|string|max:255',
            'place_of_work' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            try {
                // Delete old photo if exists
                if ($doctor->photo && Storage::disk('public')->exists($doctor->photo)) {
                    Storage::disk('public')->delete($doctor->photo);
                }

                // Store new photo
                $photo = $request->file('photo');
                $fileName = Str::slug($doctor->name) . '-' . time() . '.' . $photo->getClientOriginalExtension();
                
                // Use putFileAs which properly handles the file stream
                // This stores the file at storage/app/public/doctors/filename.jpg
                // and returns the path 'doctors/filename.jpg'
                $path = Storage::disk('public')->putFileAs('doctors', $photo, $fileName);
                
                // Verify the file was stored
                if ($path && Storage::disk('public')->exists($path)) {
                    $validated['photo'] = $path;
                    
                    \Log::info('Photo uploaded successfully', [
                        'doctor_id' => $doctor->id,
                        'photo_path' => $path,
                        'url' => Storage::url($path)
                    ]);
                } else {
                    \Log::error('Photo upload failed - file not found after storage', [
                        'doctor_id' => $doctor->id,
                        'photo_name' => $photoName,
                        'path' => $path
                    ]);
                    
                    return redirect()->back()->with('error', 'Failed to upload photo. Please try again.');
                }
            } catch (\Exception $e) {
                \Log::error('Photo upload exception', [
                    'doctor_id' => $doctor->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()->with('error', 'Failed to upload photo: ' . $e->getMessage());
            }
        }

        // Update doctor
        $doctor->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Display availability settings page
     */
    public function availability()
    {
        $doctor = Auth::guard('doctor')->user();
        
        // Parse availability schedule if exists
        $schedule = $doctor->availability_schedule ?? [];
        $defaultSchedule = [
            'monday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'tuesday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'wednesday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'thursday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'friday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'saturday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'sunday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
        ];
        
        // Merge with existing schedule
        $schedule = array_merge($defaultSchedule, $schedule);
        
        return view('doctor.availability', compact('doctor', 'schedule'));
    }

    /**
     * Update availability settings
     */
    public function updateAvailability(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();

        // Handle is_available checkbox
        $isAvailable = $request->has('is_available') ? true : false;

        // Process availability schedule
        $schedule = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($days as $day) {
            $enabled = $request->has("availability_schedule.{$day}.enabled");
            $start = $request->input("availability_schedule.{$day}.start", '09:00');
            $end = $request->input("availability_schedule.{$day}.end", '17:00');
            
            // Validate time format
            if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $start)) {
                $start = '09:00';
            }
            if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $end)) {
                $end = '17:00';
            }
            
            // Ensure end time is after start time
            if (strtotime($end) <= strtotime($start)) {
                $end = date('H:i', strtotime($start . ' +8 hours'));
            }
            
            $schedule[$day] = [
                'enabled' => $enabled,
                'start' => $start,
                'end' => $end,
            ];
        }

        // Update doctor
        $doctor->update([
            'is_available' => $isAvailable,
            'availability_schedule' => $schedule,
        ]);

        return redirect()->back()->with('success', 'Availability settings updated successfully!');
    }
}

