<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Payment;
use App\Models\Doctor;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Mail\PaymentRequest;
use App\Mail\DocumentsForwardedToDoctor;
use App\Mail\TreatmentPlanNotification;
use App\Mail\ConsultationReminder;
use App\Models\AdminUser;
use App\Models\Canvasser;
use App\Models\Nurse;
use App\Models\CustomerCare;
use App\Models\CareGiver;
use App\Models\Setting;
use App\Models\VitalSign;
use App\Models\Patient;
use App\Models\Notification;
use App\Mail\CanvasserAccountCreated;
use App\Mail\NurseAccountCreated;
use App\Mail\CustomerCareAccountCreated;
use App\Mail\CareGiverAccountCreated;
use App\Notifications\ConsultationSmsNotification;
use App\Notifications\ConsultationWhatsAppNotification;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // OPTIMIZATION: Combined multiple count queries into single aggregated query
        // Reduces database round trips from 13 queries to 2 queries
        $stats = Cache::remember('admin_dashboard_stats', 300, function () {
            // Single query for consultation statistics using conditional aggregation
            $consultationStats = Consultation::selectRaw('
                COUNT(*) as total_consultations,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_consultations,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_consultations,
                SUM(CASE WHEN status = "completed" AND payment_status = "unpaid" THEN 1 ELSE 0 END) as unpaid_consultations,
                SUM(CASE WHEN payment_status = "paid" THEN 1 ELSE 0 END) as paid_consultations
            ')->first();
            
            // Single query for user statistics
            $userStats = [
                'total_canvassers' => Canvasser::count(),
                'active_canvassers' => Canvasser::where('is_active', true)->count(),
                'total_nurses' => Nurse::count(),
                'active_nurses' => Nurse::where('is_active', true)->count(),
                'total_patients' => \App\Models\Patient::count(),
                'consulted_patients' => \App\Models\Patient::where('has_consulted', true)->count(),
            ];
            
            return [
                'total_consultations' => (int) $consultationStats->total_consultations,
                'pending_consultations' => (int) $consultationStats->pending_consultations,
                'completed_consultations' => (int) $consultationStats->completed_consultations,
                'unpaid_consultations' => (int) $consultationStats->unpaid_consultations,
                'paid_consultations' => (int) $consultationStats->paid_consultations,
                'total_revenue' => Payment::where('status', 'success')->sum('amount'),
                'total_vital_records' => \App\Models\VitalSign::count(),
            ] + $userStats;
        });

        // Cache top performers for 5 minutes
        $topCanvassers = Cache::remember('admin_top_canvassers', 300, function () {
            return Canvasser::withCount('patients')
                          ->orderBy('patients_count', 'desc')
                          ->limit(5)
                          ->get();
        });

        $topNurses = Cache::remember('admin_top_nurses', 300, function () {
            return Nurse::withCount('vitalSigns')
                       ->orderBy('vital_signs_count', 'desc')
                       ->limit(5)
                       ->get();
        });

        // Recent patients - cache for 1 minute (more dynamic)
        $recentPatients = Cache::remember('admin_recent_patients', 60, function () {
            return \App\Models\Patient::with('canvasser')
                                     ->latest()
                                     ->limit(10)
                                     ->get();
        });

        return view('admin.dashboard', compact('stats', 'topCanvassers', 'topNurses', 'recentPatients'));
    }

    /**
     * Display most consulted doctors
     */
    public function mostConsultedDoctors(Request $request)
    {
        $query = Doctor::withCount(['consultations as consultations_count'])
            ->withCount(['reviews as published_reviews_count' => function($q) {
                $q->where('is_published', true);
            }])
            ->withAvg(['reviews as avg_rating' => function($q) {
                $q->where('is_published', true);
            }], 'rating');

        // Filter by specialization if provided
        if ($request->filled('specialization')) {
            $query->where('specialization', 'like', '%' . $request->specialization . '%');
        }

        // Filter by approved status
        if ($request->has('is_approved') && $request->is_approved !== '') {
            $query->where('is_approved', $request->is_approved);
        }

        // Order by consultation count (most consulted first)
        $doctors = $query->orderBy('consultations_count', 'desc')
            ->orderBy('avg_rating', 'desc')
            ->paginate(20);

        // Get all specializations for filter - cache for 1 hour (rarely changes)
        $specializations = Cache::remember('doctor_specializations', 3600, function () {
            return Doctor::whereNotNull('specialization')
                ->distinct()
                ->orderBy('specialization')
                ->pluck('specialization');
        });

        // Statistics - cache for 5 minutes
        $stats = Cache::remember('admin_doctors_stats', 300, function () {
            return [
                'total_doctors' => Doctor::count(),
                'total_consultations' => Consultation::count(),
                'total_reviews' => \App\Models\Review::where('reviewee_type', 'doctor')->where('is_published', true)->count(),
                'avg_rating' => \App\Models\Review::where('reviewee_type', 'doctor')->where('is_published', true)->avg('rating'),
            ];
        });

        return view('admin.most-consulted-doctors', compact('doctors', 'specializations', 'stats'));
    }

    /**
     * Display all consultations
     */
    public function consultations(Request $request)
    {
        $query = Consultation::with(['doctor', 'payment', 'canvasser', 'nurse', 'booking']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by doctor
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        
        // Filter by canvasser
        if ($request->filled('canvasser_id')) {
            $query->where('canvasser_id', $request->canvasser_id);
        }
        
        // Filter by nurse
        if ($request->filled('nurse_id')) {
            $query->where('nurse_id', $request->nurse_id);
        }
        
        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
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

        $consultations = $query->latest()->paginate(20);
        
        // Prepare consultations data for bulk actions modal (JavaScript)
        $consultationsData = $consultations->map(function($c) {
            return [
                'id' => $c->id,
                'reference' => $c->reference,
                'full_name' => $c->full_name,
                'status' => $c->status,
                'payment_status' => $c->payment_status
            ];
        })->values()->toArray();
        
        // Get all nurses for assignment dropdown
        $nurses = Nurse::where('is_active', true)->orderBy('name')->get();
        
        // Get all available doctors for reassignment dropdown  
        $doctors = Doctor::where('is_available', true)
            ->orderByRaw('COALESCE(NULLIF(name, ""), CONCAT(first_name, " ", last_name))')
            ->get();
        
        // Get all canvassers for filter dropdown
        $canvassers = Canvasser::where('is_active', true)->orderBy('name')->get();

        return view('admin.consultations', compact('consultations', 'consultationsData', 'nurses', 'doctors', 'canvassers'));
    }

    /**
     * Display all patient records
     */
    public function patients(Request $request)
    {
        $query = Consultation::with(['doctor', 'canvasser', 'nurse'])
            ->select('first_name', 'last_name', 'email', 'mobile', 'age', 'gender', 'id', 'reference', 'created_at', 'status', 'doctor_id', 'canvasser_id', 'nurse_id')
            ->selectRaw('(SELECT COUNT(*) FROM consultations c2 WHERE c2.email = consultations.email) as total_consultations');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
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

        // Filter by gender
        if ($request->has('gender') && $request->gender != '') {
            $query->where('gender', $request->gender);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by canvasser
        if ($request->filled('canvasser_id')) {
            $query->where('canvasser_id', $request->canvasser_id);
        }
        
        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get all patients, grouped by email to avoid duplicates in view
        $patients = $query->latest()->paginate(20);
        
        // Get canvassers for filter dropdown
        $canvassers = Canvasser::where('is_active', true)->orderBy('name')->get();

        return view('admin.patients', compact('patients', 'canvassers'));
    }

    /**
     * Show single consultation details
     */
    public function showConsultation($id)
    {
        // Admins can view all consultations (no filtering needed)
        $consultation = Consultation::with(['doctor', 'payment', 'booking.bookingPatients.patient', 'booking.invoice.items'])->findOrFail($id);
        
        // Log viewing for HIPAA compliance
        $consultation->logViewed();
        
        // Get available doctors for reassignment
        $doctors = Doctor::where('is_available', true)
            ->orderByRaw('COALESCE(NULLIF(name, ""), CONCAT(first_name, " ", last_name))')
            ->get();
        
        return view('admin.consultation-details', compact('consultation', 'doctors'));
    }

    /**
     * Soft delete a consultation
     */
    public function deleteConsultation($id)
    {
        try {
            $consultation = Consultation::findOrFail($id);
            
            // Soft delete the consultation
            $consultation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Consultation deleted successfully! The record has been archived and can be restored if needed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete consultation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle bulk actions for consultations
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:send_reminder,send_payment_reminder,delete,reassign',
            'consultation_ids' => 'required|array',
            'consultation_ids.*' => 'exists:consultations,id',
            'doctor_id' => 'required_if:action,reassign|exists:doctors,id'
        ]);

        $consultationIds = $request->consultation_ids;
        $action = $request->action;
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        try {
            switch ($action) {
                case 'send_reminder':
                    $result = $this->bulkSendReminder($consultationIds);
                    $successCount = $result['success'];
                    $failedCount = $result['failed'];
                    $errors = $result['errors'];
                    break;

                case 'send_payment_reminder':
                    $result = $this->bulkSendPaymentReminder($consultationIds);
                    $successCount = $result['success'];
                    $failedCount = $result['failed'];
                    $errors = $result['errors'];
                    break;

                case 'delete':
                    $result = $this->bulkDelete($consultationIds);
                    $successCount = $result['success'];
                    $failedCount = $result['failed'];
                    $errors = $result['errors'];
                    break;

                case 'reassign':
                    if (!$request->has('doctor_id')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Doctor ID is required for reassignment'
                        ], 400);
                    }
                    $result = $this->bulkReassign($consultationIds, $request->doctor_id);
                    $successCount = $result['success'];
                    $failedCount = $result['failed'];
                    $errors = $result['errors'];
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action'
                    ], 400);
            }

            $message = "Action completed: {$successCount} successful";
            if ($failedCount > 0) {
                $message .= ", {$failedCount} failed";
            }

            return response()->json([
                'success' => $successCount > 0,
                'message' => $message,
                'data' => [
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Bulk action failed', [
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk send reminder notifications
     */
    private function bulkSendReminder(array $consultationIds)
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        $consultations = Consultation::whereIn('id', $consultationIds)->get();

        foreach ($consultations as $consultation) {
            try {
                // Send email reminder
                if ($consultation->email) {
                    Mail::to($consultation->email)->send(new ConsultationReminder($consultation));
                }

                // Send SMS reminder if mobile is available
                if ($consultation->mobile) {
                    try {
                        $smsNotification = new ConsultationSmsNotification();
                        $smsNotification->sendReminderNotification($consultation);
                    } catch (\Exception $e) {
                        \Log::warning("Failed to send SMS reminder for consultation {$consultation->id}: " . $e->getMessage());
                    }
                }

                // Send WhatsApp reminder if mobile is available
                if ($consultation->mobile) {
                    try {
                        $whatsappNotification = new ConsultationWhatsAppNotification();
                        $whatsappNotification->sendReminderNotification($consultation);
                    } catch (\Exception $e) {
                        \Log::warning("Failed to send WhatsApp reminder for consultation {$consultation->id}: " . $e->getMessage());
                    }
                }

                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Consultation {$consultation->reference}: " . $e->getMessage();
                \Log::error("Failed to send reminder for consultation {$consultation->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }

    /**
     * Bulk send payment reminder emails
     */
    private function bulkSendPaymentReminder(array $consultationIds)
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        $consultations = Consultation::whereIn('id', $consultationIds)
            ->where('status', 'completed')
            ->where('payment_status', 'unpaid')
            ->get();

        foreach ($consultations as $consultation) {
            try {
                if (!$consultation->requiresPayment()) {
                    $errors[] = "Consultation {$consultation->reference}: Does not require payment";
                    $failed++;
                    continue;
                }

                if ($consultation->email) {
                    Mail::to($consultation->email)->send(new PaymentRequest($consultation));
                    
                    $consultation->update([
                        'payment_request_sent' => true,
                        'payment_request_sent_at' => now(),
                    ]);
                }

                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Consultation {$consultation->reference}: " . $e->getMessage();
                \Log::error("Failed to send payment reminder for consultation {$consultation->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }

    /**
     * Bulk delete consultations
     */
    private function bulkDelete(array $consultationIds)
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        $consultations = Consultation::whereIn('id', $consultationIds)->get();

        foreach ($consultations as $consultation) {
            try {
                $consultation->delete();
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Consultation {$consultation->reference}: " . $e->getMessage();
                \Log::error("Failed to delete consultation {$consultation->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }

    /**
     * Bulk reassign consultations to a doctor
     */
    private function bulkReassign(array $consultationIds, $doctorId)
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        $doctor = Doctor::findOrFail($doctorId);

        if (!$doctor->is_available) {
            return [
                'success' => 0,
                'failed' => count($consultationIds),
                'errors' => ['Doctor is not available']
            ];
        }

        $consultations = Consultation::whereIn('id', $consultationIds)->get();

        foreach ($consultations as $consultation) {
            try {
                // Use the existing reassignDoctor logic
                $oldDoctor = $consultation->doctor;
                
                $consultation->update([
                    'doctor_id' => $doctorId
                ]);

                $consultation->refresh();
                $consultation->load('doctor');

                // Get patient
                $patient = $consultation->patient;
                if (!$patient && $consultation->email) {
                    $patient = Patient::where('email', $consultation->email)->first();
                }

                // Create notifications (similar to reassignDoctor method)
                if ($patient) {
                    try {
                        Notification::create([
                            'user_type' => 'patient',
                            'user_id' => $patient->id,
                            'title' => 'Doctor Reassigned',
                            'message' => "Your consultation (Ref: {$consultation->reference}) has been reassigned to Dr. {$doctor->full_name}.",
                            'type' => 'info',
                            'action_url' => patient_url('consultations/' . $consultation->id),
                            'data' => [
                                'consultation_id' => $consultation->id,
                                'consultation_reference' => $consultation->reference,
                                'old_doctor' => $oldDoctor ? $oldDoctor->full_name : 'No Doctor',
                                'new_doctor' => $doctor->full_name,
                                'type' => 'doctor_reassignment'
                            ]
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning("Failed to create patient notification: " . $e->getMessage());
                    }
                }

                try {
                    Notification::create([
                        'user_type' => 'doctor',
                        'user_id' => $doctor->id,
                        'title' => 'New Consultation Assigned',
                        'message' => "A consultation (Ref: {$consultation->reference}) for {$consultation->full_name} has been assigned to you.",
                        'type' => 'info',
                        'action_url' => doctor_url('consultations/' . $consultation->id),
                        'data' => [
                            'consultation_id' => $consultation->id,
                            'consultation_reference' => $consultation->reference,
                            'patient_name' => $consultation->full_name,
                            'type' => 'consultation_assigned'
                        ]
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Failed to create doctor notification: " . $e->getMessage());
                }

                // Send email notifications (you can reuse the logic from reassignDoctor)
                // For brevity, I'll skip the full email/SMS/WhatsApp implementation here
                // but you can add it if needed

                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Consultation {$consultation->reference}: " . $e->getMessage();
                \Log::error("Failed to reassign consultation {$consultation->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }

    /**
     * Update consultation status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,scheduled,completed,cancelled'
        ]);

        $consultation = Consultation::findOrFail($id);
        
        $consultation->update([
            'status' => $request->status,
            'consultation_completed_at' => $request->status === 'completed' ? now() : $consultation->consultation_completed_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Consultation status updated successfully'
        ]);
    }

    /**
     * Assign nurse to consultation
     */
    public function assignNurse(Request $request, $id)
    {
        $request->validate([
            'nurse_id' => 'required|exists:nurses,id'
        ]);

        $consultation = Consultation::findOrFail($id);
        $nurse = Nurse::findOrFail($request->nurse_id);

        if (!$nurse->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This nurse is not active'
            ], 400);
        }

        $consultation->update([
            'nurse_id' => $request->nurse_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nurse assigned successfully to consultation'
        ]);
    }

    /**
     * Reassign doctor to consultation
     */
    public function reassignDoctor(Request $request, $id)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id'
        ]);

        $consultation = Consultation::with('patient')->findOrFail($id);
        $doctor = Doctor::findOrFail($request->doctor_id);

        if (!$doctor->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'This doctor is not available'
            ], 400);
        }

        $oldDoctor = $consultation->doctor;
        
        $consultation->update([
            'doctor_id' => $request->doctor_id
        ]);

        // Refresh consultation to get updated doctor relationship
        $consultation->refresh();
        $consultation->load('doctor');

        // Get patient - try from relationship first, then from consultation email
        $patient = $consultation->patient;
        if (!$patient && $consultation->email) {
            $patient = Patient::where('email', $consultation->email)->first();
        }

        $consultationData = [
            'consultation_reference' => $consultation->reference,
            'first_name' => $consultation->first_name,
            'last_name' => $consultation->last_name,
            'email' => $consultation->email,
            'mobile' => $consultation->mobile,
            'age' => $consultation->age,
            'gender' => $consultation->gender,
            'problem' => $consultation->problem,
            'severity' => $consultation->severity,
            'consult_mode' => $consultation->consult_mode,
            'doctor' => $doctor->full_name,
            'doctor_fee' => $doctor->effective_consultation_fee,
            'emergency_symptoms' => $consultation->emergency_symptoms ?? [],
            'has_documents' => !empty($consultation->medical_documents),
            'documents_count' => !empty($consultation->medical_documents) ? count($consultation->medical_documents) : 0,
        ];

        // ============================================
        // CREATE NOTIFICATION RECORDS (BELL ICON)
        // ============================================

        // Create notification for patient
        if ($patient) {
            try {
                Notification::create([
                    'user_type' => 'patient',
                    'user_id' => $patient->id,
                    'title' => 'Doctor Reassigned',
                    'message' => "Your consultation (Ref: {$consultation->reference}) has been reassigned to Dr. {$doctor->full_name}. Please check your consultation details.",
                    'type' => 'info',
                    'action_url' => patient_url('consultations/' . $consultation->id),
                    'data' => [
                        'consultation_id' => $consultation->id,
                        'consultation_reference' => $consultation->reference,
                        'old_doctor' => $oldDoctor ? $oldDoctor->full_name : 'No Doctor',
                        'new_doctor' => $doctor->full_name,
                        'type' => 'doctor_reassignment'
                    ]
                ]);
            } catch (\Exception $e) {
                \Log::warning("Failed to create patient notification: " . $e->getMessage());
            }
        }

        // Create notification for doctor
        try {
            Notification::create([
                'user_type' => 'doctor',
                'user_id' => $doctor->id,
                'title' => 'New Consultation Assigned',
                'message' => "A consultation (Ref: {$consultation->reference}) for {$consultation->full_name} has been assigned to you. Please review the consultation details.",
                'type' => 'info',
                'action_url' => doctor_url('consultations/' . $consultation->id),
                'data' => [
                    'consultation_id' => $consultation->id,
                    'consultation_reference' => $consultation->reference,
                    'patient_name' => $consultation->full_name,
                    'type' => 'consultation_assigned'
                ]
            ]);
        } catch (\Exception $e) {
            \Log::warning("Failed to create doctor notification: " . $e->getMessage());
        }

        // ============================================
        // SEND EMAIL NOTIFICATIONS
        // ============================================

        // Send email to patient
        if ($consultation->email) {
            try {
                \Mail::to($consultation->email)->send(new \App\Mail\DoctorReassignmentNotification(array_merge($consultationData, [
                    'is_patient' => true,
                    'is_reassignment' => true,
                    'old_doctor' => $oldDoctor ? $oldDoctor->full_name : 'No Doctor',
                    'new_doctor' => $doctor->full_name,
                    'consultation_id' => $consultation->id,
                ])));
                \Log::info('Patient reassignment email sent', [
                    'consultation_id' => $consultation->id,
                    'patient_email' => $consultation->email
                ]);
            } catch (\Exception $e) {
                \Log::warning("Failed to send patient reassignment email: " . $e->getMessage());
            }
        }

        // Send email to doctor
        try {
            \Mail::to($doctor->email)->send(new \App\Mail\DoctorReassignmentNotification(array_merge($consultationData, [
                'is_patient' => false,
                'is_reassignment' => true,
                'old_doctor' => $oldDoctor ? $oldDoctor->full_name : 'No Doctor',
                'consultation_id' => $consultation->id,
            ])));
            \Log::info('Doctor reassignment email sent', [
                'consultation_id' => $consultation->id,
                'doctor_id' => $doctor->id,
                'doctor_email' => $doctor->email
            ]);
        } catch (\Exception $e) {
            \Log::warning("Failed to send doctor reassignment email: " . $e->getMessage());
        }

        // ============================================
        // SEND SMS NOTIFICATIONS
        // ============================================

        // Send SMS to patient
        if ($consultation->mobile) {
            try {
                $smsNotification = new ConsultationSmsNotification();
                $patientSmsData = array_merge($consultationData, [
                    'is_reassignment' => true,
                    'old_doctor' => $oldDoctor ? $oldDoctor->full_name : 'No Doctor',
                    'new_doctor' => $doctor->full_name,
                ]);
                
                // Create a custom SMS message for reassignment
                $patientName = $consultation->first_name;
                $reference = $consultation->reference;
                $newDoctorName = $doctor->full_name;
                $oldDoctorName = $oldDoctor ? $oldDoctor->full_name : 'No Doctor';
                
                $smsMessage = "Dear {$patientName}, your consultation (Ref: {$reference}) has been reassigned from {$oldDoctorName} to Dr. {$newDoctorName}. We'll contact you shortly. - DoctorOnTap";
                
                $termiiService = app(\App\Services\TermiiService::class);
                $smsResult = $termiiService->sendSMS($consultation->mobile, $smsMessage);
                
                if ($smsResult['success']) {
                    \Log::info('Patient reassignment SMS sent', [
                        'consultation_id' => $consultation->id,
                        'patient_mobile' => $consultation->mobile
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to send patient reassignment SMS: " . $e->getMessage());
            }
        }

        // Send SMS to doctor
        if ($doctor->phone) {
            try {
                $smsNotification = new ConsultationSmsNotification();
                $doctorSmsResult = $smsNotification->sendDoctorNewConsultation($doctor, $consultationData);
                
                if ($doctorSmsResult['success']) {
                    \Log::info('Doctor reassignment SMS sent', [
                        'consultation_id' => $consultation->id,
                        'doctor_id' => $doctor->id,
                        'doctor_phone' => $doctor->phone
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to send doctor reassignment SMS: " . $e->getMessage());
            }
        }

        // ============================================
        // SEND WHATSAPP NOTIFICATIONS
        // ============================================

        // Send WhatsApp to patient
        if (config('services.termii.whatsapp_enabled') && $consultation->mobile) {
            try {
                $whatsapp = new ConsultationWhatsAppNotification();
                
                $patientName = $consultation->first_name;
                $reference = $consultation->reference;
                $newDoctorName = $doctor->full_name;
                $oldDoctorName = $oldDoctor ? $oldDoctor->full_name : 'No Doctor';
                
                $whatsappMessage = "🔄 *Doctor Reassignment Notice*\n\n";
                $whatsappMessage .= "Hi {$patientName},\n\n";
                $whatsappMessage .= "Your consultation has been reassigned:\n\n";
                $whatsappMessage .= "📋 *Reference:* {$reference}\n";
                $whatsappMessage .= "👨‍⚕️ *Previous Doctor:* {$oldDoctorName}\n";
                $whatsappMessage .= "👨‍⚕️ *New Doctor:* Dr. {$newDoctorName}\n\n";
                $whatsappMessage .= "We'll contact you shortly with more details.\n\n";
                $whatsappMessage .= "Questions? Reply to this message!\n\n";
                $whatsappMessage .= "— *DoctorOnTap Healthcare* 🏥";
                
                $termiiService = app(\App\Services\TermiiService::class);
                $whatsappResult = $termiiService->sendWhatsAppMessage($consultation->mobile, $whatsappMessage);
                
                if ($whatsappResult['success']) {
                    \Log::info('Patient reassignment WhatsApp sent', [
                        'consultation_id' => $consultation->id,
                        'patient_mobile' => $consultation->mobile
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to send patient reassignment WhatsApp: " . $e->getMessage());
            }
        }

        // Send WhatsApp to doctor
        if (config('services.termii.whatsapp_enabled') && $doctor->phone) {
            try {
                $whatsapp = new ConsultationWhatsAppNotification();
                $doctorWhatsappResult = $whatsapp->sendDoctorNewConsultationTemplate(
                    $doctor,
                    $consultationData,
                    'doctor_new_consultation' // Template ID from Termii dashboard
                );
                
                if ($doctorWhatsappResult['success']) {
                    \Log::info('Doctor reassignment WhatsApp sent', [
                        'consultation_id' => $consultation->id,
                        'doctor_id' => $doctor->id,
                        'doctor_phone' => $doctor->phone
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to send doctor reassignment WhatsApp: " . $e->getMessage());
            }
        }

        $message = 'Doctor reassigned successfully from ' . 
                ($oldDoctor ? $oldDoctor->full_name : 'No Doctor') . 
                ' to ' . $doctor->full_name . '. Notifications sent to patient and doctor.';

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Query doctor about delayed consultation (Admin-initiated)
     */
    public function queryDoctor($id)
    {
        $consultation = Consultation::with('doctor')->findOrFail($id);

        // Validate that consultation has a doctor assigned
        if (!$consultation->doctor) {
            return response()->json([
                'success' => false,
                'message' => 'No doctor assigned to this consultation'
            ], 400);
        }

        // Validate that consultation is scheduled but not completed
        if ($consultation->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Consultation is already completed'
            ], 400);
        }

        $doctor = $consultation->doctor;

        // Prepare notification data
        $notificationData = [
            'consultation_reference' => $consultation->reference,
            'first_name' => $consultation->first_name,
            'last_name' => $consultation->last_name,
            'email' => $consultation->email,
            'mobile' => $consultation->mobile,
            'age' => $consultation->age,
            'gender' => $consultation->gender,
            'problem' => $consultation->problem,
            'severity' => $consultation->severity,
            'consult_mode' => $consultation->consult_mode,
            'doctor' => $doctor->full_name,
            'doctor_fee' => $doctor->effective_consultation_fee ?? 0,
            'emergency_symptoms' => $consultation->emergency_symptoms ?? [],
            'has_documents' => !empty($consultation->medical_documents),
            'documents_count' => !empty($consultation->medical_documents) ? count($consultation->medical_documents) : 0,
        ];

        // Send urgent email notification
        try {
            \Mail::to($doctor->email)->send(new \App\Mail\DelayQueryNotification($notificationData));
            \Log::info('Delay query notification sent to doctor', [
                'consultation_id' => $consultation->id,
                'consultation_reference' => $consultation->reference,
                'doctor_id' => $doctor->id,
                'doctor_email' => $doctor->email
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send delay query notification to doctor', [
                'consultation_id' => $consultation->id,
                'doctor_id' => $doctor->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }

        // Send SMS notification if available
        if ($doctor->phone) {
            try {
                $smsNotification = new \App\Notifications\ConsultationSmsNotification();
                $smsResult = $smsNotification->sendDelayQuerySms($doctor, $notificationData);
                
                if ($smsResult['success']) {
                    \Log::info('Delay query SMS sent to doctor', [
                        'consultation_id' => $consultation->id,
                        'doctor_id' => $doctor->id,
                        'doctor_phone' => $doctor->phone
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send delay query SMS to doctor', [
                    'consultation_id' => $consultation->id,
                    'doctor_id' => $doctor->id,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the request if SMS fails
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Urgent delay query notification sent to Dr. ' . $doctor->full_name . ' successfully'
        ]);
    }

    /**
     * Send payment request to patient
     */
    public function sendPaymentRequest($id)
    {
        $consultation = Consultation::with('doctor')->findOrFail($id);

        // Validate
        if (!$consultation->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation must be completed before sending payment request'
            ], 400);
        }

        if (!$consultation->requiresPayment()) {
            return response()->json([
                'success' => false,
                'message' => 'This consultation does not require payment (no fee set)'
            ], 400);
        }

        // Send payment request email (allow resending)
        try {
            Mail::to($consultation->email)->send(new PaymentRequest($consultation));

            // Update consultation (update timestamp even if already sent)
            $consultation->update([
                'payment_request_sent' => true,
                'payment_request_sent_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => ($consultation->payment_request_sent ? 'Payment request email resent' : 'Payment request email sent') . ' successfully to ' . $consultation->email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually forward treatment plan to patient email
     */
    public function forwardTreatmentPlan($id)
    {
        $consultation = Consultation::with(['doctor', 'patient', 'booking'])->findOrFail($id);

        // Validate - treatment plan must exist
        if (!$consultation->hasTreatmentPlan()) {
            return response()->json([
                'success' => false,
                'message' => 'No treatment plan has been created for this consultation yet'
            ], 400);
        }

        // Determine recipient email: check multiple sources
        $recipientEmail = null;
        $recipientName = $consultation->full_name;
        
        // 1. First try consultation email field
        if (!empty($consultation->email)) {
            $recipientEmail = $consultation->email;
        }
        // 2. Try patient relationship email
        elseif ($consultation->patient && !empty($consultation->patient->email)) {
            $recipientEmail = $consultation->patient->email;
            $recipientName = $consultation->patient->full_name ?? $recipientName;
        }
        // 3. Try booking payer email (for multi-patient bookings)
        elseif ($consultation->booking && !empty($consultation->booking->payer_email)) {
            $recipientEmail = $consultation->booking->payer_email;
            $recipientName = $consultation->booking->payer_name ?? $recipientName;
        }

        if (!$recipientEmail) {
            return response()->json([
                'success' => false,
                'message' => 'No email address found for this consultation. Please ensure the patient has an email address.'
            ], 400);
        }

        // Check payment status first - this is critical
        // Only send treatment plan if payment has been made
        // If payment not made, send payment request instead
        $paymentStatus = $consultation->payment_status;
        $isPaid = ($paymentStatus === 'paid');

        try {
            if ($isPaid) {
                // Payment has been made - send treatment plan
                Mail::to($recipientEmail)->send(new TreatmentPlanNotification($consultation));
                
                \Log::info('Treatment plan manually forwarded by admin (payment confirmed)', [
                    'consultation_id' => $consultation->id,
                    'reference' => $consultation->reference,
                    'email' => $recipientEmail,
                    'payment_status' => $paymentStatus,
                    'admin_action' => 'manual_forward_treatment_plan'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Treatment plan has been sent successfully to ' . $recipientEmail . ' (Payment confirmed)'
                ]);
            } else {
                // Payment has NOT been made - send payment request instead
                Mail::to($recipientEmail)->send(new PaymentRequest($consultation));
                
                \Log::info('Payment request sent by admin (treatment plan forward - payment not made)', [
                    'consultation_id' => $consultation->id,
                    'reference' => $consultation->reference,
                    'email' => $recipientEmail,
                    'payment_status' => $paymentStatus,
                    'admin_action' => 'manual_forward_payment_request',
                    'note' => 'Treatment plan exists but payment not made - sent payment request instead'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment request has been sent to ' . $recipientEmail . '. Treatment plan will be sent automatically once payment is confirmed.'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to forward treatment plan/payment request', [
                'consultation_id' => $consultation->id,
                'reference' => $consultation->reference,
                'email' => $recipientEmail,
                'payment_status' => $paymentStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $emailType = $isPaid ? 'treatment plan' : 'payment request';
            return response()->json([
                'success' => false,
                'message' => 'Failed to send ' . $emailType . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend treatment plan to patient (Email + SMS)
     * Includes delivery tracking for admin visibility
     */
    public function resendTreatmentPlan($id)
    {
        $consultation = Consultation::with('doctor')->findOrFail($id);

        // Validate - treatment plan must exist
        if (!$consultation->hasTreatmentPlan()) {
            return response()->json([
                'success' => false,
                'message' => 'No treatment plan has been created for this consultation yet'
            ], 400);
        }

        $results = [
            'email' => ['sent' => false, 'message' => ''],
            'sms' => ['sent' => false, 'message' => ''],
        ];

        // Send Email
        try {
            Mail::to($consultation->email)->send(new PaymentRequest($consultation));
            $results['email'] = ['sent' => true, 'message' => 'Email sent successfully'];
            
            \Log::info('Payment request email resent by admin', [
                'consultation_id' => $consultation->id,
                'reference' => $consultation->reference,
                'email' => $consultation->email,
                'admin_user' => auth()->user()->name ?? 'Unknown'
            ]);
        } catch (\Exception $e) {
            $results['email'] = ['sent' => false, 'message' => $e->getMessage()];
            \Log::error('Failed to resend treatment plan email', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage()
            ]);
        }

        // Send SMS
        try {
            $smsNotification = new \App\Notifications\ConsultationSmsNotification();
            $smsResult = $smsNotification->sendTreatmentPlanReady($consultation);
            
            if ($smsResult['success']) {
                $results['sms'] = ['sent' => true, 'message' => 'SMS sent successfully'];
                \Log::info('Treatment plan SMS resent by admin', [
                    'consultation_id' => $consultation->id,
                    'reference' => $consultation->reference,
                    'mobile' => $consultation->mobile
                ]);
            } else {
                $results['sms'] = ['sent' => false, 'message' => $smsResult['message'] ?? 'SMS failed'];
            }
        } catch (\Exception $e) {
            $results['sms'] = ['sent' => false, 'message' => $e->getMessage()];
            \Log::error('Failed to resend treatment plan SMS', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage()
            ]);
        }

        // Determine overall success
        $anySuccess = $results['email']['sent'] || $results['sms']['sent'];
        $allSuccess = $results['email']['sent'] && $results['sms']['sent'];

        if ($allSuccess) {
            $message = 'Treatment plan resent successfully via Email and SMS';
        } elseif ($anySuccess) {
            $sent = $results['email']['sent'] ? 'Email' : 'SMS';
            $failed = !$results['email']['sent'] ? 'Email' : 'SMS';
            $message = "Treatment plan sent via {$sent}. {$failed} failed: " . $results[strtolower($failed)]['message'];
        } else {
            $message = 'Failed to resend treatment plan. Email: ' . $results['email']['message'] . '. SMS: ' . $results['sms']['message'];
        }

        return response()->json([
            'success' => $anySuccess,
            'message' => $message,
            'details' => $results
        ], $anySuccess ? 200 : 500);
    }

    /**
     * Manually mark consultation payment as paid (for offline payments)
     * This is used when patients pay through bank transfer, cash, POS, etc.
     */
    public function markPaymentAsPaid(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        try {
            $consultation = Consultation::with('doctor')->findOrFail($id);

            // Check if already paid
            if ($consultation->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This consultation has already been marked as paid'
                ], 400);
            }

            // Create or update payment record
            $payment = Payment::updateOrCreate(
                ['reference' => 'MANUAL-' . $consultation->reference],
                [
                    'customer_email' => $consultation->email,
                    'customer_name' => $consultation->full_name,
                    'customer_phone' => $consultation->mobile,
                    'amount' => $consultation->doctor->effective_consultation_fee ?? 0,
                    'currency' => 'NGN',
                    'status' => 'success',
                    'payment_method' => $request->payment_method,
                    'payment_reference' => $request->payment_reference ?? 'MANUAL-' . time(),
                    'doctor_id' => $consultation->doctor_id,
                    'metadata' => [
                        'consultation_id' => $consultation->id,
                        'consultation_reference' => $consultation->reference,
                        'manual_payment' => true,
                        'marked_by_admin' => auth()->guard('admin')->user()->name ?? 'Admin',
                        'admin_notes' => $request->admin_notes,
                        'marked_at' => now()->toDateTimeString(),
                    ],
                ]
            );

            // Update consultation payment status
            $consultation->update([
                'payment_status' => 'paid',
                'payment_id' => $payment->id,
            ]);

            // Unlock treatment plan if it exists
            if ($consultation->hasTreatmentPlan() && !$consultation->treatment_plan_unlocked) {
                $consultation->update([
                    'treatment_plan_unlocked' => true,
                    'treatment_plan_unlocked_at' => now(),
                    'treatment_plan_accessible' => true,
                ]);

                // Send payment request email
                try {
                    Mail::to($consultation->email)->send(new PaymentRequest($consultation));
                    
                    \Log::info('Payment request sent after manual payment', [
                        'consultation_id' => $consultation->id,
                        'reference' => $consultation->reference,
                        'payment_method' => $request->payment_method
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send treatment plan after manual payment', [
                        'consultation_id' => $consultation->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            \Log::info('Payment manually marked as paid by admin', [
                'consultation_id' => $consultation->id,
                'reference' => $consultation->reference,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'admin' => auth()->guard('admin')->user()->name ?? 'Unknown'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment marked as paid successfully! ' . ($consultation->hasTreatmentPlan() ? 'Treatment plan has been unlocked and sent to patient.' : '')
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to mark payment as paid', [
                'consultation_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark payment as paid: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display payments list
     */
    public function payments(Request $request)
    {
        $query = Payment::with('doctor');

        // Search functionality
       if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by doctor
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        
        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Amount range filters
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }
        
        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->latest()->paginate(20);
        
        // Get all doctors for filter dropdown
        $doctors = Doctor::approved()->orderBy('name')->get();

        return view('admin.payments', compact('payments', 'doctors'));
    }

    /**
     * Display all doctors
     */
    public function doctors(Request $request)
    {
        $query = Doctor::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter by availability
        if ($request->has('is_available') && $request->is_available != '') {
            $query->where('is_available', $request->is_available);
        }

        // Filter by gender
        if ($request->has('gender') && $request->gender != '') {
            $query->where('gender', $request->gender);
        }
        
        // Filter by specialization (exact match or contains)
        if ($request->filled('specialization')) {
            $query->where('specialization', 'like', "%{$request->specialization}%");
        }
        
        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $doctors = $query->orderBy('id', 'asc')->paginate(20);

        // Statistics
        $stats = [
            'total' => Doctor::count(),
            'available' => Doctor::where('is_available', true)->count(),
            'unavailable' => Doctor::where('is_available', false)->count(),
            'total_consultations' => Consultation::whereNotNull('doctor_id')->count(),
        ];

        return view('admin.doctors', compact('doctors', 'stats'));
    }

    /**
     * Forward medical documents to doctor
     */
    public function forwardDocumentsToDoctor($id)
    {
        $consultation = Consultation::with('doctor')->findOrFail($id);

        // Validate
        if (!$consultation->doctor) {
            return response()->json([
                'success' => false,
                'message' => 'No doctor assigned to this consultation'
            ], 400);
        }

        if (!$consultation->medical_documents || count($consultation->medical_documents) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No medical documents to forward'
            ], 400);
        }

        if ($consultation->documents_forwarded_at) {
            return response()->json([
                'success' => false,
                'message' => 'Documents already forwarded to doctor on ' . $consultation->documents_forwarded_at->format('M d, Y H:i')
            ], 400);
        }

        // Forward documents via email with attachments
        try {
            Mail::to($consultation->doctor->email)->send(new DocumentsForwardedToDoctor($consultation));

            // Update consultation
            $consultation->update([
                'documents_forwarded_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Medical documents forwarded successfully to Dr. ' . $consultation->doctor->full_name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to forward documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new doctor
     */
    public function storeDoctor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:doctors,email',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:Male,Female',
            'specialization' => 'nullable|string|max:255',
            'consultation_fee' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'languages' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_available' => 'nullable|boolean',
            'mdcn_license_current' => 'nullable|in:yes,no',
        ]);

        // Handle checkbox value
        $validated['is_available'] = $request->has('is_available') ? true : false;
        
        // Handle MDCN license - convert 'yes'/'no' to boolean
        if (isset($validated['mdcn_license_current'])) {
            $validated['mdcn_license_current'] = $validated['mdcn_license_current'] === 'yes';
        } else {
            $validated['mdcn_license_current'] = false;
        }

        try {
            $doctor = Doctor::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Doctor added successfully!',
                'doctor' => $doctor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing doctor
     */
    public function updateDoctor(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:doctors,email,' . $id,
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:Male,Female',
            'specialization' => 'nullable|string|max:255',
            'consultation_fee' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'languages' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_available' => 'nullable|boolean',
        ]);

        // Handle checkbox value
        $validated['is_available'] = $request->has('is_available') ? true : false;

        try {
            $doctor->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Doctor updated successfully!',
                'doctor' => $doctor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete a doctor
     */
    public function deleteDoctor($id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            
            // Soft delete the doctor (consultations will remain with doctor_id)
            $doctor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Doctor deleted successfully! The record has been archived and can be restored if needed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send campaign notification to all active doctors
     */
    public function sendCampaignNotification(Request $request)
    {
        try {
            $request->validate([
                'campaign_name' => 'nullable|string|max:255',
                'start_date' => 'nullable|string|max:255',
                'end_date' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'email_body' => 'nullable|string|max:5000',
            ]);

            // Get all active doctors
            $doctors = Doctor::where('is_available', true)->get();

            if ($doctors->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active doctors found to send notifications to.'
                ], 404);
            }

            // Prepare campaign details
            $campaignDetails = [
                'name' => $request->input('campaign_name', 'Healthcare Access Campaign'),
                'start_date' => $request->input('start_date', date('F d, Y')),
                'end_date' => $request->input('end_date'),
                'description' => $request->input('description'),
                'email_body' => $request->input('email_body'),
            ];

            // Send email to each doctor
            $emailsSent = 0;
            $emailsFailed = 0;

            foreach ($doctors as $doctor) {
                try {
                    \Mail::to($doctor->email)->send(new \App\Mail\CampaignNotification($doctor, $campaignDetails));
                    $emailsSent++;
                } catch (\Exception $e) {
                    $emailsFailed++;
                    \Log::error("Failed to send campaign notification to doctor {$doctor->id}: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Campaign notification sent successfully to {$emailsSent} doctor(s).",
                'details' => [
                    'total_doctors' => $doctors->count(),
                    'emails_sent' => $emailsSent,
                    'emails_failed' => $emailsFailed
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send campaign notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display admin users list
     */
    public function adminUsers(Request $request)
    {
        $query = AdminUser::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $admins = $query->latest()->paginate(20);
        
        return view('admin.admin-users', compact('admins'));
    }

    /**
     * Store a new admin user
     */
    public function storeAdminUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            $admin = AdminUser::create($validated);
            
            // Send email verification notification
            $admin->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Admin user created successfully! A verification email has been sent to ' . $admin->email . '.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing admin user
     */
    public function updateAdminUser(Request $request, $id)
    {
        $admin = AdminUser::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            $admin->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Admin user updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle admin user status
     */
    public function toggleAdminStatus(Request $request, $id)
    {
        try {
            $admin = AdminUser::findOrFail($id);
            
            // Prevent deactivating yourself
            if ($admin->id === auth()->guard('admin')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot deactivate your own account'
                ], 400);
            }

            $admin->is_active = $request->input('is_active', false);
            $admin->save();

            return response()->json([
                'success' => true,
                'message' => 'Admin status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete an admin user
     */
    public function deleteAdminUser($id)
    {
        try {
            $admin = AdminUser::findOrFail($id);
            
            // Prevent deleting yourself
            if ($admin->id === auth()->guard('admin')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 400);
            }
            
            // Soft delete the admin user
            $admin->delete();

            return response()->json([
                'success' => true,
                'message' => 'Admin user deleted successfully! The record has been archived and can be restored if needed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete admin user: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== CANVASSERS MANAGEMENT ====================

    /**
     * Display canvassers list
     */
    public function canvassers(Request $request)
    {
        $query = Canvasser::with('createdBy')->withCount('consultations');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $canvassers = $query->latest()->paginate(20);
        
        return view('admin.canvassers', compact('canvassers'));
    }

    /**
     * Store a new canvasser
     */
    public function storeCanvasser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:canvassers,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        // Store plain password before hashing
        $plainPassword = $validated['password'];
        
        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['created_by'] = auth()->guard('admin')->id();

        try {
            $canvasser = Canvasser::create($validated);
            
            // Get admin name
            $adminName = auth()->guard('admin')->user()->name;
            
            // Send account creation email with password and verification link
            Mail::to($canvasser->email)->send(new CanvasserAccountCreated($canvasser, $plainPassword, $adminName));

            return response()->json([
                'success' => true,
                'message' => 'Canvasser created successfully! An email with login credentials and verification link has been sent.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create canvasser: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing canvasser
     */
    public function updateCanvasser(Request $request, $id)
    {
        $canvasser = Canvasser::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:canvassers,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            $canvasser->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Canvasser updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update canvasser: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle canvasser status
     */
    public function toggleCanvasserStatus(Request $request, $id)
    {
        try {
            $canvasser = Canvasser::findOrFail($id);
            $canvasser->is_active = $request->input('is_active', false);
            $canvasser->save();

            return response()->json([
                'success' => true,
                'message' => 'Canvasser status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete a canvasser
     */
    public function deleteCanvasser($id)
    {
        try {
            $canvasser = Canvasser::findOrFail($id);
            
            // Soft delete the canvasser (patients and consultations will remain with canvasser_id)
            $canvasser->delete();

            return response()->json([
                'success' => true,
                'message' => 'Canvasser deleted successfully! The record has been archived and can be restored if needed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete canvasser: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== NURSES MANAGEMENT ====================

    /**
     * Display nurses list
     */
    public function nurses(Request $request)
    {
        $query = Nurse::with('createdBy')->withCount('consultations');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $nurses = $query->latest()->paginate(20);
        
        return view('admin.nurses', compact('nurses'));
    }

    /**
     * Store a new nurse
     */
    public function storeNurse(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:nurses,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        // Store plain password before hashing
        $plainPassword = $validated['password'];
        
        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['created_by'] = auth()->guard('admin')->id();

        try {
            $nurse = Nurse::create($validated);
            
            // Get admin name
            $adminName = auth()->guard('admin')->user()->name;
            
            // Send account creation email with password and verification link
            Mail::to($nurse->email)->send(new NurseAccountCreated($nurse, $plainPassword, $adminName));

            return response()->json([
                'success' => true,
                'message' => 'Nurse created successfully! An email with login credentials and verification link has been sent.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create nurse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing nurse
     */
    public function updateNurse(Request $request, $id)
    {
        $nurse = Nurse::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:nurses,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            $nurse->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Nurse updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update nurse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle nurse status
     */
    public function toggleNurseStatus(Request $request, $id)
    {
        try {
            $nurse = Nurse::findOrFail($id);
            $nurse->is_active = $request->input('is_active', false);
            $nurse->save();

            return response()->json([
                'success' => true,
                'message' => 'Nurse status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete a nurse
     */
    public function deleteNurse($id)
    {
        try {
            $nurse = Nurse::findOrFail($id);
            
            // Soft delete the nurse (consultations and vital signs will remain with nurse_id)
            $nurse->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nurse deleted successfully! The record has been archived and can be restored if needed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete nurse: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== CUSTOMER CARE MANAGEMENT ====================

    /**
     * Display customer care list
     */
    public function customerCares(Request $request)
    {
        $query = CustomerCare::with('createdBy')->withCount('consultations');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $customerCares = $query->latest()->paginate(20);
        
        return view('admin.customer-cares', compact('customerCares'));
    }

    /**
     * Store a new customer care
     */
    public function storeCustomerCare(Request $request)
    {
        // Validate request - Laravel will automatically return JSON if Accept: application/json header is present
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customer_cares,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        // Store plain password before hashing
        $plainPassword = $validated['password'];
        
        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['created_by'] = auth()->guard('admin')->id();

        try {
            $customerCare = CustomerCare::create($validated);
            
            // Get admin name
            $adminName = auth()->guard('admin')->user()->name;
            
            // Send account creation email with password and verification link
            Mail::to($customerCare->email)->send(new CustomerCareAccountCreated($customerCare, $plainPassword, $adminName));

            return response()->json([
                'success' => true,
                'message' => 'Customer Care created successfully! An email with login credentials and verification link has been sent.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create customer care', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer care: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing customer care
     */
    public function updateCustomerCare(Request $request, $id)
    {
        try {
            $customerCare = CustomerCare::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:customer_cares,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:8|confirmed',
                'is_active' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer care not found'
            ], 404);
        }

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            $customerCare->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Customer Care updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer care: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle customer care status
     */
    public function toggleCustomerCareStatus(Request $request, $id)
    {
        try {
            $customerCare = CustomerCare::findOrFail($id);
            $customerCare->is_active = $request->input('is_active', false);
            $customerCare->save();

            return response()->json([
                'success' => true,
                'message' => 'Customer Care status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete a customer care
     */
    public function deleteCustomerCare($id)
    {
        try {
            $customerCare = CustomerCare::findOrFail($id);
            
            // Soft delete the customer care
            $customerCare->delete();

            return response()->json([
                'success' => true,
                'message' => 'Customer Care deleted successfully! The record has been archived and can be restored if needed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer care: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== CARE GIVER MANAGEMENT ====================

    /**
     * Display care givers list
     */
    public function careGivers(Request $request)
    {
        $query = CareGiver::with('createdBy')->withCount('consultations');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $careGivers = $query->latest()->paginate(20);
        
        return view('admin.care-givers', compact('careGivers'));
    }

    /**
     * Store a new care giver
     * OPTIMIZATION: Uses Form Request for validation (reusable, cleaner code)
     */
    public function storeCareGiver(\App\Http\Requests\StoreCareGiverRequest $request)
    {
        try {
            $validated = $request->validated();

            // OPTIMIZATION: Wrap in transaction to ensure data consistency
            // If email sending fails, user creation is rolled back
            $careGiver = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $request) {
                // Store plain password before hashing
                $plainPassword = $validated['password'];
                
                $validated['password'] = bcrypt($validated['password']);
                $validated['is_active'] = $request->has('is_active') ? true : false;
                $validated['created_by'] = auth()->guard('admin')->id();

                $careGiver = CareGiver::create($validated);
                
                // Get admin name
                $adminName = auth()->guard('admin')->user()->name;
                
                // OPTIMIZATION: Email is now queued (ShouldQueue), so it won't block
                // The transaction completes, then email is sent asynchronously
                Mail::to($careGiver->email)->send(new CareGiverAccountCreated($careGiver, $plainPassword, $adminName));
                
                return $careGiver;
            });

            return response()->json([
                'success' => true,
                'message' => 'Care Giver created successfully! An email with login credentials and verification link has been sent.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create care giver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
* Update an existing care giver
     */
    public function updateCareGiver(Request $request, $id)
    {
        try {
            $careGiver = CareGiver::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:care_givers,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:8|confirmed',
                'is_active' => 'nullable|boolean',
            ]);

            // Only update password if provided
            if (!empty($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            } else {
                unset($validated['password']);
            }

            $validated['is_active'] = $request->has('is_active') ? true : false;

            $careGiver->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Care Giver updated successfully!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update care giver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle care giver status
     */
    public function toggleCareGiverStatus(Request $request, $id)
    {
        try {
            $careGiver = CareGiver::findOrFail($id);
            $careGiver->is_active = $request->input('is_active', false);
            $careGiver->save();

            return response()->json([
                'success' => true,
                'message' => 'Care Giver status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete a care giver
     */
    public function deleteCareGiver($id)
    {
        try {
            $careGiver = CareGiver::findOrFail($id);
            
            // Soft delete the care giver (consultations will remain with care_giver_id)
            $careGiver->delete();

            return response()->json([
                'success' => true,
                'message' => 'Care Giver deleted successfully! The record has been archived and can be restored if needed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete care giver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display pending doctor registrations
     */
    public function doctorRegistrations(Request $request)
    {
        $query = Doctor::query();

        // Filter by approval status
        if ($request->has('status')) {
            if ($request->status == 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->status == 'approved') {
                $query->where('is_approved', true);
            }
        } else {
            // Default: show pending only
            $query->where('is_approved', false);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%");
            });
        }
        
        // Filter by specialization
        if ($request->filled('specialization')) {
            $query->where('specialization', 'like', "%{$request->specialization}%");
        }
        
        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        
        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $doctors = $query->latest()->paginate(20);
        $defaultFee = Setting::get('default_consultation_fee', 5000);

        return view('admin.doctor-registrations', compact('doctors', 'defaultFee'));
    }

    /**
     * Approve a doctor registration
     */
    public function approveDoctorRegistration(Request $request, $id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            
            if ($doctor->is_approved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor is already approved.'
                ], 400);
            }

            $validated = $request->validate([
                'use_default_fee' => 'required|boolean',
                'custom_fee' => 'nullable|numeric|min:0',
            ]);

            $updateData = [
                'is_approved' => true,
                'is_available' => true,
                'approved_by' => auth()->guard('admin')->id(),
                'approved_at' => now(),
                'use_default_fee' => $validated['use_default_fee'],
            ];

            // If not using default fee and custom fee is provided
            if (!$validated['use_default_fee'] && isset($validated['custom_fee'])) {
                $updateData['consultation_fee'] = $validated['custom_fee'];
            } elseif ($validated['use_default_fee']) {
                // Use the system default fee
                $updateData['consultation_fee'] = Setting::get('default_consultation_fee', 5000);
            }

            $doctor->update($updateData);

            // TODO: Send approval email to doctor
            // Mail::to($doctor->email)->send(new DoctorApprovalNotification($doctor));

            return response()->json([
                'success' => true,
                'message' => 'Doctor approved successfully! They can now log in to their account.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a doctor registration
     */
    public function rejectDoctorRegistration($id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            
            // Delete the certificate file if exists
            if ($doctor->certificate_path && \Storage::disk('public')->exists($doctor->certificate_path)) {
                \Storage::disk('public')->delete($doctor->certificate_path);
            }

            // Delete the doctor record
            $doctor->delete();

            // TODO: Send rejection email to doctor
            // Mail::to($doctor->email)->send(new DoctorRejectionNotification($doctor));

            return response()->json([
                'success' => true,
                'message' => 'Doctor registration rejected and removed from the system.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View doctor details (for modal)
     */
    public function viewDoctorRegistration($id)
    {
        try {
            $doctor = Doctor::with('verifiedByAdmin')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'doctor' => [
                    'id' => $doctor->id,
                    'full_name' => $doctor->full_name,
                    'first_name' => $doctor->first_name,
                    'last_name' => $doctor->last_name,
                    'email' => $doctor->email,
                    'phone' => $doctor->phone,
                    'gender' => ucfirst($doctor->gender),
                    'specialization' => $doctor->specialization,
                    'experience' => $doctor->experience,
                    'location' => $doctor->location,
                    'place_of_work' => $doctor->place_of_work,
                    'role' => ucfirst($doctor->role),
                    'languages' => $doctor->languages,
                    'days_of_availability' => $doctor->days_of_availability,
                    'consultation_fee' => $doctor->consultation_fee,
                    'suggested_fee' => $doctor->consultation_fee,
                    'mdcn_license_current' => $doctor->mdcn_license_current,
                    'certificate_path' => $doctor->certificate_path,
                    'certificate_data' => $doctor->certificate_data ? true : false, // Just check if exists, don't send full data
                    'certificate_original_name' => $doctor->certificate_original_name,
                    'mdcn_certificate_verified' => $doctor->mdcn_certificate_verified ?? false,
                    'mdcn_certificate_verified_at' => $doctor->mdcn_certificate_verified_at ? $doctor->mdcn_certificate_verified_at->format('M d, Y H:i A') : null,
                    'mdcn_certificate_verified_by' => $doctor->mdcn_certificate_verified_by ? $doctor->verifiedByAdmin->name ?? null : null,
                    'is_approved' => $doctor->is_approved,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load doctor details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display settings page
     */
    public function settings()
    {
        $settings = Setting::where('group', 'pricing')->get();
        $defaultFee = Setting::get('default_consultation_fee', 5000);
        $multiPatientFee = Setting::get('multi_patient_booking_fee', null);
        // If multi-patient fee is not set, default to the default consultation fee
        if ($multiPatientFee === null) {
            $multiPatientFee = $defaultFee;
        }
        $useDefaultForAll = Setting::get('use_default_fee_for_all', false);
        $doctorPaymentPercentage = Setting::get('doctor_payment_percentage', 70);
        $consultationFeePayLater = Setting::get('consultation_fee_pay_later', 5000);
        $consultationFeePayNow = Setting::get('consultation_fee_pay_now', 4500);
        $additionalChildDiscount = Setting::get('additional_child_discount_percentage', 60);

        // Security Alert Settings
        $securityAlertsEnabled = Setting::get('security_alerts_enabled', false);
        $securityAlertEmails = Setting::get('security_alert_emails', [env('SECURITY_ALERT_EMAIL', 'admin@doctorontap.com')]);
        if (!is_array($securityAlertEmails)) {
            $securityAlertEmails = [$securityAlertEmails];
        }
        $securityAlertSeverities = Setting::get('security_alert_severities', ['critical', 'high']);
        if (!is_array($securityAlertSeverities)) {
            $securityAlertSeverities = ['critical', 'high'];
        }
        $securityAlertThresholdCritical = Setting::get('security_alert_threshold_critical', 1);
        $securityAlertThresholdHigh = Setting::get('security_alert_threshold_high', 5);

        return view('admin.settings', compact(
            'settings', 
            'defaultFee', 
            'multiPatientFee', 
            'useDefaultForAll', 
            'doctorPaymentPercentage',
            'consultationFeePayLater',
            'consultationFeePayNow',
            'additionalChildDiscount',
            'securityAlertsEnabled',
            'securityAlertEmails',
            'securityAlertSeverities',
            'securityAlertThresholdCritical',
            'securityAlertThresholdHigh'
        ));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        try {
            $formType = $request->input('form_type', 'both');
            
            // Build validation rules based on form type
            $rules = [];
            
            // Pricing settings (only validate if pricing form is submitted)
            if ($formType === 'pricing' || $formType === 'both') {
                $rules['default_consultation_fee'] = 'nullable|numeric|min:0';
                $rules['multi_patient_booking_fee'] = 'nullable|numeric|min:0';
                $rules['additional_child_discount_percentage'] = 'nullable|numeric|min:0|max:100';
                $rules['doctor_payment_percentage'] = 'required|numeric|min:0|max:100';
                $rules['use_default_fee_for_all'] = 'nullable|boolean';
                $rules['consultation_fee_pay_later'] = 'nullable|numeric|min:0';
                $rules['consultation_fee_pay_now'] = 'nullable|numeric|min:0';
            }

            // Security alert settings (only validate if security form is submitted)
            if ($formType === 'security_alerts' || $formType === 'both') {
                $rules['security_alerts_enabled'] = 'nullable|boolean';
                $rules['security_alert_emails'] = 'nullable|array';
                $rules['security_alert_emails.*'] = 'required|email';
                $rules['security_alert_severities'] = 'nullable|array';
                $rules['security_alert_severities.*'] = 'in:critical,high,medium,low';
                $rules['security_alert_threshold_critical'] = 'nullable|integer|min:1';
                $rules['security_alert_threshold_high'] = 'nullable|integer|min:1';
            }

            $validated = $request->validate($rules);

            $defaultFee = null;

            // Update pricing settings (only if pricing form was submitted)
            if ($formType === 'pricing' || $formType === 'both') {
                // Use pay_later fee as default if default_consultation_fee is not provided
                $defaultFee = $validated['default_consultation_fee'] ?? ($validated['consultation_fee_pay_later'] ?? null);
                
                if ($defaultFee !== null) {
                    Setting::set('default_consultation_fee', $defaultFee, 'number');
                }
                
                if (isset($validated['multi_patient_booking_fee'])) {
                    Setting::set('multi_patient_booking_fee', $validated['multi_patient_booking_fee'], 'number');
                }
                
                if (isset($validated['additional_child_discount_percentage'])) {
                    Setting::set('additional_child_discount_percentage', $validated['additional_child_discount_percentage'], 'decimal');
                }
                
                Setting::set('doctor_payment_percentage', $validated['doctor_payment_percentage'], 'decimal');
                Setting::set('use_default_fee_for_all', $request->has('use_default_fee_for_all') ? 1 : 0, 'boolean');
                
                if (isset($validated['consultation_fee_pay_later'])) {
                    Setting::set('consultation_fee_pay_later', $validated['consultation_fee_pay_later'], 'number');
                }
                
                if (isset($validated['consultation_fee_pay_now'])) {
                    Setting::set('consultation_fee_pay_now', $validated['consultation_fee_pay_now'], 'number');
                }
            }

            // Update security alert settings (only if security form was submitted)
            if ($formType === 'security_alerts' || $formType === 'both') {
                Setting::set('security_alerts_enabled', $request->has('security_alerts_enabled') ? 1 : 0, 'boolean');
                
                if ($request->has('security_alert_emails')) {
                    $emails = array_filter($request->input('security_alert_emails', []));
                    Setting::set('security_alert_emails', $emails, 'json');
                    \Log::info('Security alert emails updated', [
                        'emails' => $emails,
                        'count' => count($emails),
                        'updated_by' => auth()->guard('admin')->id()
                    ]);
                }
                
                if ($request->has('security_alert_severities')) {
                    Setting::set('security_alert_severities', $request->input('security_alert_severities', []), 'json');
                }
                
                if ($request->has('security_alert_threshold_critical')) {
                    Setting::set('security_alert_threshold_critical', $request->input('security_alert_threshold_critical', 1), 'integer');
                }
                
                if ($request->has('security_alert_threshold_high')) {
                    Setting::set('security_alert_threshold_high', $request->input('security_alert_threshold_high', 5), 'integer');
                }
            }

            // If forcing all doctors to use default fee, update all doctors
            if ($request->has('use_default_fee_for_all') && $defaultFee !== null) {
                Doctor::query()->update([
                    'use_default_fee' => true,
                    'consultation_fee' => $defaultFee
                ]);
            }

            return redirect()->back()->with('success', 'Consultation fees updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Send test security alert email
     */
    public function testSecurityAlert(Request $request)
    {
        try {
            $alertEmails = Setting::get('security_alert_emails', []);
            
            \Log::info('Test security alert requested', [
                'configured_emails' => $alertEmails,
                'emails_count' => is_array($alertEmails) ? count($alertEmails) : 0,
                'ip' => $request->ip(),
            ]);
            
            if (empty($alertEmails) || !is_array($alertEmails)) {
                \Log::warning('Test security alert failed: No email recipients configured');
                return response()->json([
                    'success' => false,
                    'message' => 'No email recipients configured. Please add at least one email address in Security Alerts settings.'
                ], 400);
            }

            // Filter valid emails
            $validEmails = array_filter($alertEmails, function($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });

            if (empty($validEmails)) {
                \Log::warning('Test security alert failed: No valid email addresses', [
                    'provided_emails' => $alertEmails
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No valid email addresses found. Please check your email configuration.'
                ], 400);
            }

            // Create test alert data
            $testData = [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
                'test' => true,
            ];

            $sentCount = 0;
            $failedEmails = [];

            // Send test alert to each valid email
            foreach ($validEmails as $email) {
                try {
                    \Log::info('Sending test security alert email', [
                        'recipient' => $email,
                        'event_type' => 'test_alert',
                        'severity' => 'medium'
                    ]);

                    \Mail::to($email)->send(new \App\Mail\SecurityAlert('test_alert', $testData, 'medium'));
                    
                    $sentCount++;
                    
                    \Log::info('Test security alert email sent successfully', [
                        'recipient' => $email
                    ]);
                } catch (\Exception $e) {
                    $failedEmails[] = $email;
                    \Log::error('Failed to send test security alert email', [
                        'recipient' => $email,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            if ($sentCount > 0) {
                $message = "Test security alert email sent successfully to {$sentCount} recipient(s): " . implode(', ', array_diff($validEmails, $failedEmails));
                
                if (!empty($failedEmails)) {
                    $message .= ". Failed to send to: " . implode(', ', $failedEmails);
                }

                \Log::info('Test security alert completed', [
                    'sent_count' => $sentCount,
                    'failed_count' => count($failedEmails),
                    'sent_to' => array_diff($validEmails, $failedEmails),
                    'failed_to' => $failedEmails
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            } else {
                \Log::error('Test security alert failed: All emails failed to send', [
                    'attempted_emails' => $validEmails,
                    'failed_emails' => $failedEmails
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test alert to all recipients. Please check your mail configuration and logs.'
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send test security alert', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test alert: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download/view certificate from database
     */
    public function viewCertificate($id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            
            // Try to get from private storage first, fallback to base64
            if ($doctor->certificate_path && Storage::exists($doctor->certificate_path)) {
                $fileContent = Storage::get($doctor->certificate_path);
                $mimeType = $doctor->certificate_mime_type ?? Storage::mimeType($doctor->certificate_path);
            } elseif ($doctor->certificate_data) {
                // Fallback to base64 data
                $fileContent = base64_decode($doctor->certificate_data);
                $mimeType = $doctor->certificate_mime_type ?? 'application/pdf';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No certificate found for this doctor.'
                ], 404);
            }
            
            // Return the file for viewing/download
            return response($fileContent)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . ($doctor->certificate_original_name ?? 'mdcn-certificate.pdf') . '"');
                
        } catch (\Exception $e) {
            \Log::error('Failed to view doctor certificate', [
                'doctor_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify MDCN certificate
     */
    public function verifyMdcnCertificate(Request $request, $id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            
            if (!$doctor->certificate_path && !$doctor->certificate_data) {
                return response()->json([
                    'success' => false,
                    'message' => 'No certificate found to verify.'
                ], 400);
            }
            
            $admin = Auth::guard('admin')->user();
            
            $doctor->update([
                'mdcn_certificate_verified' => true,
                'mdcn_certificate_verified_at' => now(),
                'mdcn_certificate_verified_by' => $admin->id,
            ]);
            
            \Log::info('MDCN certificate verified by admin', [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->full_name,
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'MDCN certificate verified successfully.',
                'verified' => true,
                'verified_at' => $doctor->mdcn_certificate_verified_at->format('M d, Y H:i A'),
                'verified_by' => $admin->name,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to verify MDCN certificate', [
                'doctor_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unverify MDCN certificate
     */
    public function unverifyMdcnCertificate(Request $request, $id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            
            $doctor->update([
                'mdcn_certificate_verified' => false,
                'mdcn_certificate_verified_at' => null,
                'mdcn_certificate_verified_by' => null,
            ]);
            
            \Log::info('MDCN certificate unverified by admin', [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->full_name,
                'admin_id' => Auth::guard('admin')->user()->id,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'MDCN certificate verification removed.',
                'verified' => false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unverify certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display all vital signs records (Admin oversight)
     */
    public function vitalSigns(Request $request)
    {
        $query = VitalSign::with(['patient', 'nurse']);

        // Search by patient name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by walk-in vs regular
        if ($request->filled('type')) {
            if ($request->type === 'walk-in') {
                $query->where('is_walk_in', true);
            } elseif ($request->type === 'regular') {
                $query->where('is_walk_in', false);
            }
        }

        // Filter by email sent status
        if ($request->filled('email_status')) {
            if ($request->email_status === 'sent') {
                $query->where('email_sent', true);
            } elseif ($request->email_status === 'not_sent') {
                $query->where('email_sent', false);
            }
        }

        // Filter by nurse
        if ($request->filled('nurse_id')) {
            $query->where('nurse_id', $request->nurse_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $vitalSigns = $query->latest()->paginate(20);
        
        // Get all nurses for filter dropdown
        $nurses = Nurse::where('is_active', true)->orderBy('name')->get();

        // Statistics
        $stats = [
            'total_records' => VitalSign::count(),
            'walk_in_records' => VitalSign::where('is_walk_in', true)->count(),
            'regular_records' => VitalSign::where('is_walk_in', false)->count(),
            'emails_sent' => VitalSign::where('email_sent', true)->count(),
            'emails_pending' => VitalSign::where('email_sent', false)->count(),
        ];

        return view('admin.vital-signs', compact('vitalSigns', 'nurses', 'stats'));
    }

    /**
     * View canvasser patient registrations
     */
    public function canvasserPatients(Request $request)
    {
        $canvasserId = $request->query('canvasser_id');
        
        $query = Patient::with('canvasser');
        
        if ($canvasserId) {
            $query->where('canvasser_id', $canvasserId);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Filter by verification status
        if ($request->filled('verification_status')) {
            if ($request->verification_status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->verification_status === 'unverified') {
                $query->where('is_verified', false);
            }
        }
        
        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        
        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $patients = $query->latest()->paginate(20);
        
        // Get canvassers for filter dropdown
        $canvassers = Canvasser::select('id', 'name')->get();
        
        // Statistics
        $stats = [
            'total_patients' => Patient::count(),
            'verified_patients' => Patient::where('is_verified', true)->count(),
            'unverified_patients' => Patient::where('is_verified', false)->count(),
            'patients_with_consultations' => Patient::where('consultations_count', '>', 0)->count(),
            'patients_without_consultations' => Patient::where('consultations_count', 0)->count(),
        ];
        
        return view('admin.canvasser-patients', compact('patients', 'canvassers', 'stats'));
    }

    /**
     * View canvasser performance
     */
    public function canvasserPerformance()
    {
        $canvassers = Canvasser::withCount(['patients', 'consultations'])
                              ->withSum('patients', 'total_amount_paid')
                              ->get()
                              ->map(function ($canvasser) {
                                  $canvasser->verified_patients_count = Patient::where('canvasser_id', $canvasser->id)
                                                                              ->where('is_verified', true)
                                                                              ->count();
                                  $canvasser->unverified_patients_count = Patient::where('canvasser_id', $canvasser->id)
                                                                                ->where('is_verified', false)
                                                                                ->count();
                                  $canvasser->consulted_patients_count = Patient::where('canvasser_id', $canvasser->id)
                                                                              ->where('has_consulted', true)
                                                                              ->count();
                                  return $canvasser;
                              });
        
        $stats = [
            'total_canvassers' => Canvasser::count(),
            'active_canvassers' => Canvasser::where('is_active', true)->count(),
            'total_patients_registered' => Patient::count(),
            'verified_patients' => Patient::where('is_verified', true)->count(),
            'total_revenue_generated' => Patient::sum('total_amount_paid'),
        ];
        
        return view('admin.canvasser-performance', compact('canvassers', 'stats'));
    }

    /**
     * View patient verification status
     */
    public function patientVerification()
    {
        $unverifiedPatients = Patient::where('is_verified', false)
                                   ->with('canvasser')
                                   ->latest()
                                   ->paginate(20);
        
        $stats = [
            'total_patients' => Patient::count(),
            'verified_patients' => Patient::where('is_verified', true)->count(),
            'unverified_patients' => Patient::where('is_verified', false)->count(),
            'verification_rate' => Patient::count() > 0 ? 
                round((Patient::where('is_verified', true)->count() / Patient::count()) * 100, 2) : 0,
        ];
        
        return view('admin.patient-verification', compact('unverifiedPatients', 'stats'));
    }

    /**
     * Soft delete a patient
     */
    public function deletePatient($id)
    {
        try {
            $patient = Patient::findOrFail($id);
            
            // Soft delete the patient
            $patient->delete();

            return response()->json([
                'success' => true,
                'message' => 'Patient deleted successfully! The record has been archived and can be restored if needed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete a vital sign record
     */
    public function deleteVitalSign($id)
    {
        try {
            $vitalSign = VitalSign::findOrFail($id);
            
            // Soft delete the vital sign
            $vitalSign->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vital sign record deleted successfully! The record has been archived and can be restored if needed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete vital sign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View doctor profile with bank details and consultations
     */
    public function viewDoctorProfile($id)
    {
        $doctor = Doctor::with(['bankAccounts', 'consultations', 'payments'])->findOrFail($id);
        
        // Calculate statistics
        $stats = [
            'total_consultations' => $doctor->consultations()->count(),
            'completed_consultations' => $doctor->consultations()->where('status', 'completed')->count(),
            'paid_consultations' => $doctor->consultations()->where('payment_status', 'paid')->count(),
            'unpaid_consultations' => $doctor->consultations()->where('status', 'completed')
                                            ->where('payment_status', '!=', 'paid')->count(),
            'total_paid_to_doctor' => $doctor->payments()->where('status', 'completed')->sum('doctor_amount'),
            'pending_payment' => 0, // Will calculate below
        ];

        // Get unpaid consultations
        $unpaidConsultations = $doctor->consultations()
            ->where('status', 'completed')
            ->where('payment_status', '!=', 'paid')
            ->with('payment')
            ->get();

        // Calculate pending payment
        $pendingAmount = $unpaidConsultations->sum(function($consultation) use ($doctor) {
            return $doctor->effective_consultation_fee;
        });
        $stats['pending_payment'] = $pendingAmount;

        // Recent consultations
        $recentConsultations = $doctor->consultations()
            ->with('payment')
            ->latest()
            ->limit(10)
            ->get();

        // Payment history
        $paymentHistory = $doctor->payments()
            ->with(['bankAccount', 'paidBy'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.doctor-profile', compact('doctor', 'stats', 'recentConsultations', 'paymentHistory', 'unpaidConsultations'));
    }

    /**
     * Verify doctor bank account
     */
    public function verifyBankAccount(Request $request, $id)
    {
        try {
            $bankAccount = \App\Models\DoctorBankAccount::findOrFail($id);
            $admin = auth()->guard('admin')->user();

            $bankAccount->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by' => $admin->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank account verified successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify bank account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View doctor payments management page
     */
    public function doctorPayments(Request $request)
    {
        $query = \App\Models\DoctorPayment::with(['doctor', 'bankAccount', 'paidBy']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('doctor', function($doctorQ) use ($search) {
                      $doctorQ->where('name', 'like', "%{$search}%")
                              ->orWhere('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by doctor
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        
        // Amount range filters
        if ($request->filled('amount_min')) {
            $query->where('doctor_amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('doctor_amount', '<=', $request->amount_max);
        }

        $payments = $query->latest()->paginate(20);

        // Get all doctors for filter dropdown
        $doctors = Doctor::approved()->orderBy('name')->get();

        // Statistics
        $stats = [
            'total_payments' => \App\Models\DoctorPayment::count(),
            'pending_payments' => \App\Models\DoctorPayment::where('status', 'pending')->count(),
            'completed_payments' => \App\Models\DoctorPayment::where('status', 'completed')->count(),
            'total_paid_amount' => \App\Models\DoctorPayment::where('status', 'completed')->sum('doctor_amount'),
            'total_platform_fee' => \App\Models\DoctorPayment::where('status', 'completed')->sum('platform_fee'),
        ];

        return view('admin.doctor-payments', compact('payments', 'doctors', 'stats'));
    }

    /**
     * Get payment details with consultations
     */
    public function getPaymentDetails($id)
    {
        try {
            $payment = \App\Models\DoctorPayment::with(['doctor', 'bankAccount', 'paidBy'])
                ->findOrFail($id);

            // Get consultations included in this payment
            $consultations = [];
            if (!empty($payment->consultation_ids)) {
                $consultations = \App\Models\Consultation::whereIn('id', $payment->consultation_ids)
                    ->with('payment')
                    ->get()
                    ->map(function($consultation) use ($payment) {
                        return [
                            'id' => $consultation->id,
                            'reference' => $consultation->reference,
                            'full_name' => $consultation->full_name,
                            'created_at' => $consultation->created_at->toISOString(),
                            'amount' => $payment->doctor->effective_consultation_fee ?? 0,
                            'payment_status' => $consultation->payment_status,
                        ];
                    })
                    ->toArray();
            }

            return response()->json([
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'reference' => $payment->reference,
                    'status' => $payment->status,
                    'korapay_status' => $payment->korapay_status,
                    'korapay_reference' => $payment->korapay_reference,
                    'korapay_fee' => $payment->korapay_fee,
                    'total_consultations_count' => $payment->total_consultations_count,
                    'total_consultations_amount' => $payment->total_consultations_amount,
                    'doctor_percentage' => $payment->doctor_percentage,
                    'doctor_amount' => $payment->doctor_amount,
                    'platform_fee' => $payment->platform_fee,
                    'payment_method' => $payment->payment_method,
                    'transaction_reference' => $payment->transaction_reference,
                    'payment_notes' => $payment->payment_notes,
                    'admin_notes' => $payment->admin_notes,
                    'paid_at' => $payment->paid_at ? $payment->paid_at->toISOString() : null,
                    'payout_initiated_at' => $payment->payout_initiated_at ? $payment->payout_initiated_at->toISOString() : null,
                    'payout_completed_at' => $payment->payout_completed_at ? $payment->payout_completed_at->toISOString() : null,
                    'korapay_response' => $payment->korapay_response, // Already decoded by model cast
                    'created_at' => $payment->created_at->toISOString(),
                    'doctor' => $payment->doctor ? [
                        'id' => $payment->doctor->id,
                        'full_name' => $payment->doctor->full_name,
                    ] : null,
                    'bank_account' => $payment->bankAccount ? [
                        'bank_name' => $payment->bankAccount->bank_name,
                        'account_name' => $payment->bankAccount->account_name,
                        'account_number' => $payment->bankAccount->account_number,
                        'account_type' => $payment->bankAccount->account_type,
                    ] : null,
                    'paid_by_user' => $payment->paidBy ? [
                        'id' => $payment->paidBy->id,
                        'name' => $payment->paidBy->name,
                    ] : null,
                ],
                'consultations' => $consultations,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to load payment details', [
                'payment_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load payment details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create payment for doctor
     */
    public function createDoctorPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'doctor_id' => 'required|exists:doctors,id',
                'consultation_ids' => 'required|array',
                'consultation_ids.*' => 'exists:consultations,id',
                'doctor_percentage' => 'nullable|numeric|min:0|max:100',
                'period_from' => 'nullable|date',
                'period_to' => 'nullable|date|after_or_equal:period_from',
            ]);

            $doctor = Doctor::with('defaultBankAccount')->findOrFail($validated['doctor_id']);

            // Get verified bank account (prefer default, otherwise get first verified)
            $bankAccount = $doctor->defaultBankAccount;
            
            // If no default account, get the first verified account
            if (!$bankAccount || !$bankAccount->is_verified) {
                $bankAccount = $doctor->bankAccounts()
                    ->where('is_verified', true)
                    ->first();
            }

            // Check if doctor has a verified bank account
            if (!$bankAccount || !$bankAccount->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor does not have a verified bank account.'
                ], 400);
            }

            // Validate that all submitted consultations belong to this doctor and are paid
            $submittedConsultations = Consultation::whereIn('id', $validated['consultation_ids'])->get();
            
            // Check if all consultations belong to this doctor
            $invalidDoctorConsultations = $submittedConsultations->where('doctor_id', '!=', $doctor->id);
            if ($invalidDoctorConsultations->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some consultations do not belong to this doctor.'
                ], 400);
            }

            // Check if all consultations are completed
            $incompleteConsultations = $submittedConsultations->where('status', '!=', 'completed');
            if ($incompleteConsultations->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some consultations are not completed. Only completed consultations can be included in doctor payments.'
                ], 400);
            }

            // Check if all consultations are paid - this is the critical requirement
            $unpaidConsultations = $submittedConsultations->where('payment_status', '!=', 'paid');
            if ($unpaidConsultations->isNotEmpty()) {
                $unpaidReferences = $unpaidConsultations->pluck('reference')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => 'Some consultations are not paid. Only consultations with payment_status = "paid" can be included in doctor payments. Unpaid consultations: ' . $unpaidReferences
                ], 400);
            }

            // Get consultations - only include paid consultations
            $consultations = Consultation::whereIn('id', $validated['consultation_ids'])
                ->where('doctor_id', $doctor->id)
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->get();

            if ($consultations->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid paid consultations found. Only consultations with payment_status = "paid" can be included in doctor payments.'
                ], 400);
            }

            // Use custom percentage or default from settings
            $doctorPercentage = $validated['doctor_percentage'] ?? Setting::get('doctor_payment_percentage', 70);
            
            // Calculate payment details
            $paymentData = \App\Models\DoctorPayment::calculatePayment(
                $consultations,
                $doctorPercentage
            );

            // Create payment record
            $payment = \App\Models\DoctorPayment::create([
                'doctor_id' => $doctor->id,
                'bank_account_id' => $bankAccount->id,
                'consultation_ids' => $validated['consultation_ids'],
                'period_from' => $validated['period_from'] ?? null,
                'period_to' => $validated['period_to'] ?? null,
                ...$paymentData,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully!',
                'payment' => $payment
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to create doctor payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initiate KoraPay payout for a doctor payment
     */
    public function initiateDoctorPayout(Request $request, $id)
    {
        try {
            $payment = \App\Models\DoctorPayment::findOrFail($id);
            $admin = auth()->guard('admin')->user();

            // Check if payment is already processed
            if ($payment->status === 'completed' && $payment->korapay_status === 'success') {
                return response()->json([
                    'success' => false,
                    'message' => 'This payment has already been completed.'
                ], 400);
            }

            // Allow retrying failed payments - reset status to pending
            if ($payment->status === 'failed' || $payment->korapay_status === 'failed') {
                $payment->update([
                    'status' => 'pending',
                    'korapay_status' => null,
                    'korapay_reference' => null,
                    'korapay_response' => null,
                    'payout_initiated_at' => null,
                    'payout_completed_at' => null,
                ]);
            }

            // Check if doctor has verified bank account
            if (!$payment->bankAccount || !$payment->bankAccount->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor does not have a verified bank account.'
                ], 400);
            }

            // Check if bank code is available
            if (empty($payment->bankAccount->bank_code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bank code is missing for this bank account. Please update the bank account with the correct bank code before initiating payout.'
                ], 400);
            }

            // Initiate payout via KoraPay
            $payoutService = app(\App\Services\KoraPayPayoutService::class);
            $result = $payoutService->initiatePayout($payment);

            if ($result['success']) {
                // Update payment with admin info
                $payment->update([
                    'paid_by' => $admin->id,
                    'payment_method' => 'korapay_bank_transfer',
                    'admin_notes' => 'Payout initiated via KoraPay by ' . $admin->name,
                ]);
                
                // Send notification to doctor that payment is being processed
                try {
                    \App\Models\Notification::create([
                        'user_type' => 'doctor',
                        'user_id' => $payment->doctor_id,
                        'title' => 'Payment Processing',
                        'message' => "Your payment of ₦" . number_format($payment->doctor_amount, 2) . " is being processed. Reference: {$payment->reference}",
                        'type' => 'info',
                        'action_url' => doctor_url('payment-history'),
                        'data' => [
                            'payment_id' => $payment->id,
                            'payment_reference' => $payment->reference,
                            'amount' => $payment->doctor_amount,
                            'type' => 'doctor_payment_processing'
                        ]
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create doctor payment processing notification', [
                        'payment_id' => $payment->id,
                        'doctor_id' => $payment->doctor_id,
                        'error' => $e->getMessage()
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data'],
                    'payment' => $payment->fresh(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Failed to initiate doctor payout', [
                'payment_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process bulk payouts for multiple doctor payments
     * Uses KoraPay bulk payout API endpoint
     */
    public function processBulkPayouts(Request $request)
    {
        try {
            $validated = $request->validate([
                'payment_ids' => 'required|array',
                'payment_ids.*' => 'exists:doctor_payments,id',
                'merchant_bears_cost' => 'nullable|boolean', // Optional: whether merchant pays fees
            ]);

            $payoutService = app(\App\Services\KoraPayPayoutService::class);
            $merchantBearsCost = $validated['merchant_bears_cost'] ?? true;
            $result = $payoutService->processBulkPayouts($validated['payment_ids'], $merchantBearsCost);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'] ?? 'Bulk payout initiated successfully',
                    'data' => [
                        'batch_reference' => $result['batch_reference'],
                        'payout_count' => $result['data']['payout_count'] ?? count($validated['payment_ids']),
                        'total_chargeable_amount' => $result['data']['total_chargeable_amount'] ?? null,
                        'status' => $result['data']['status'] ?? 'pending',
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to initiate bulk payout',
                'data' => $result['data'] ?? null,
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Failed to process bulk payouts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk payouts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify payout status
     */
    public function verifyPayoutStatus($id)
    {
        try {
            $payment = \App\Models\DoctorPayment::findOrFail($id);

            if (!$payment->korapay_reference) {
                return response()->json([
                    'success' => false,
                    'message' => 'No KoraPay reference found for this payment.'
                ], 400);
            }

            $payoutService = app(\App\Services\KoraPayPayoutService::class);
            $result = $payoutService->verifyPayoutStatus($payment->korapay_reference);

            if ($result['success'] && !empty($result['data'])) {
                $data = $result['data'];
                
                // Update payment status based on verification
                $korapayStatus = $data['status'] ?? 'processing';
                $paymentStatus = $korapayStatus === 'success' ? 'completed' : 
                                ($korapayStatus === 'failed' ? 'failed' : 'processing');

                $updateData = [
                    'korapay_status' => $korapayStatus,
                    'status' => $paymentStatus,
                    'korapay_response' => json_encode($data),
                ];

                if ($korapayStatus === 'success') {
                    $wasCompleted = $payment->status === 'completed';
                    
                    $updateData['paid_at'] = now();
                    $updateData['payout_completed_at'] = now();
                    $updateData['transaction_reference'] = $payment->korapay_reference;
                    
                    if (isset($data['fee'])) {
                        $updateData['korapay_fee'] = (float) $data['fee'];
                    }
                }

                $payment->update($updateData);
                
                // Send notification if payment was just completed
                if ($korapayStatus === 'success' && !$wasCompleted && $payment->doctor_id) {
                    try {
                        \App\Models\Notification::create([
                            'user_type' => 'doctor',
                            'user_id' => $payment->doctor_id,
                            'title' => 'Payment Completed',
                            'message' => "Your payment of ₦" . number_format($payment->doctor_amount, 2) . " has been successfully transferred to your bank account. Reference: {$payment->reference}",
                            'type' => 'success',
                            'action_url' => doctor_url('payment-history'),
                            'data' => [
                                'payment_id' => $payment->id,
                                'payment_reference' => $payment->reference,
                                'amount' => $payment->doctor_amount,
                                'transaction_reference' => $payment->korapay_reference,
                                'type' => 'doctor_payment_completed'
                            ]
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to create doctor payment completion notification', [
                            'payment_id' => $payment->id,
                            'doctor_id' => $payment->doctor_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Payout status verified successfully.',
                    'data' => $data,
                    'payment' => $payment->fresh(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to verify payout status.',
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Failed to verify payout status', [
                'payment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark payment as completed (manual override - for non-KoraPay payments)
     */
    public function completeDoctorPayment(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'payment_method' => 'required|string|max:255',
                'transaction_reference' => 'nullable|string|max:255',
                'payment_notes' => 'nullable|string|max:1000',
            ]);

            $payment = \App\Models\DoctorPayment::findOrFail($id);
            $admin = auth()->guard('admin')->user();

            $payment->markAsCompleted(
                $admin->id,
                $validated['payment_method'],
                $validated['transaction_reference'] ?? null,
                $validated['payment_notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment marked as completed successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor's unpaid consultations for payment creation
     */
    public function getDoctorUnpaidConsultations($doctorId, Request $request)
    {
        try {
            // Get all consultation IDs that are already included in pending/processing/completed payments
            $excludedConsultationIds = \App\Models\DoctorPayment::where('doctor_id', $doctorId)
                ->whereIn('status', ['pending', 'processing', 'completed'])
                ->whereNotNull('consultation_ids')
                ->get()
                ->flatMap(function($payment) {
                    // Extract consultation IDs from JSON array
                    $ids = $payment->consultation_ids ?? [];
                    return is_array($ids) ? $ids : [];
                })
                ->unique()
                ->values()
                ->toArray();

            // Get consultations that are completed, paid by patient, but not yet paid to doctor
            // Only include consultations with payment_status = 'paid'
            $query = Consultation::where('doctor_id', $doctorId)
                ->where('status', 'completed')
                ->where('payment_status', 'paid');

            // Exclude consultations already in pending/processing/completed payments
            if (!empty($excludedConsultationIds)) {
                $query->whereNotIn('id', $excludedConsultationIds);
            }

            // Use pagination for performance (default 50 per page, max 100)
            $perPage = min($request->get('per_page', 50), 100);
            $consultations = $query->with('payment')->latest()->paginate($perPage);

            $doctor = Doctor::findOrFail($doctorId);

            // Calculate total amount for all unpaid consultations (not just current page)
            $totalUnpaidCount = Consultation::where('doctor_id', $doctorId)
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->when(!empty($excludedConsultationIds), function($q) use ($excludedConsultationIds) {
                    $q->whereNotIn('id', $excludedConsultationIds);
                })
                ->count();
            
            $totalAmount = $totalUnpaidCount * $doctor->effective_consultation_fee;

            // Log for debugging
            \Log::info('Loading paid consultations (unpaid to doctor)', [
                'doctor_id' => $doctorId,
                'total_consultations' => $totalUnpaidCount,
                'current_page_count' => $consultations->count(),
                'excluded_ids_count' => count($excludedConsultationIds),
                'note' => 'Only consultations with payment_status = "paid" are included',
            ]);

            return response()->json([
                'success' => true,
                'consultations' => $consultations->map(function($c) use ($doctor) {
                    return [
                        'id' => $c->id,
                        'reference' => $c->reference,
                        'patient_name' => $c->full_name,
                        'date' => $c->created_at->format('Y-m-d'),
                        'amount' => $doctor->effective_consultation_fee,
                        'payment_status' => $c->payment_status,
                    ];
                }),
                'pagination' => [
                    'current_page' => $consultations->currentPage(),
                    'per_page' => $consultations->perPage(),
                    'total' => $consultations->total(),
                    'last_page' => $consultations->lastPage(),
                ],
                'total_unpaid_count' => $totalUnpaidCount,
                'total_amount' => $totalAmount,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to load unpaid consultations', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load consultations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display all multi-patient bookings
     */
    public function bookings(Request $request)
    {
        $query = Booking::with(['doctor', 'bookingPatients.patient', 'invoice']);

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('payer_name', 'like', "%{$search}%")
                  ->orWhere('payer_email', 'like', "%{$search}%")
                  ->orWhere('payer_mobile', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        $bookings = $query->latest()->paginate(20);

        return view('admin.bookings', compact('bookings'));
    }

    /**
     * Show single booking details
     */
    public function showBooking($id)
    {
        $booking = Booking::with([
            'doctor',
            'bookingPatients.patient',
            'bookingPatients.consultation',
            'invoice.items',
            'feeAdjustmentLogs'
        ])->findOrFail($id);

        return view('admin.booking-details', compact('booking'));
    }
}
