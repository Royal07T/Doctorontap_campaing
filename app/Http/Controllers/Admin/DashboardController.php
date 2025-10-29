<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Payment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentRequest;
use App\Mail\DocumentsForwardedToDoctor;
use App\Mail\TreatmentPlanNotification;
use App\Models\AdminUser;
use App\Models\Canvasser;
use App\Models\Nurse;
use App\Models\Setting;
use App\Models\VitalSign;
use App\Models\Patient;
use App\Mail\CanvasserAccountCreated;
use App\Mail\NurseAccountCreated;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_consultations' => Consultation::count(),
            'pending_consultations' => Consultation::where('status', 'pending')->count(),
            'completed_consultations' => Consultation::where('status', 'completed')->count(),
            'unpaid_consultations' => Consultation::where('payment_status', 'unpaid')->where('status', 'completed')->count(),
            'paid_consultations' => Consultation::where('payment_status', 'paid')->count(),
            'total_revenue' => Payment::where('status', 'success')->sum('amount'),
            
            // Canvasser and Nurse statistics
            'total_canvassers' => Canvasser::count(),
            'active_canvassers' => Canvasser::where('is_active', true)->count(),
            'total_nurses' => Nurse::count(),
            'active_nurses' => Nurse::where('is_active', true)->count(),
            'total_patients' => \App\Models\Patient::count(),
            'consulted_patients' => \App\Models\Patient::where('has_consulted', true)->count(),
            'total_vital_records' => \App\Models\VitalSign::count(),
        ];

        // Top performing canvassers
        $topCanvassers = Canvasser::withCount('patients')
                                  ->orderBy('patients_count', 'desc')
                                  ->limit(5)
                                  ->get();

        // Top performing nurses
        $topNurses = Nurse::withCount('vitalSigns')
                         ->orderBy('vital_signs_count', 'desc')
                         ->limit(5)
                         ->get();

        // Recent patients
        $recentPatients = \App\Models\Patient::with('canvasser')
                                             ->latest()
                                             ->limit(10)
                                             ->get();

        return view('admin.dashboard', compact('stats', 'topCanvassers', 'topNurses', 'recentPatients'));
    }

    /**
     * Display all consultations
     */
    public function consultations(Request $request)
    {
        $query = Consultation::with(['doctor', 'payment', 'canvasser', 'nurse']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
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
        
        // Get all nurses for assignment dropdown
        $nurses = Nurse::where('is_active', true)->orderBy('name')->get();
        
        // Get all available doctors for reassignment dropdown
        $doctors = Doctor::where('is_available', true)
            ->orderByRaw('COALESCE(NULLIF(name, ""), CONCAT(first_name, " ", last_name))')
            ->get();

        return view('admin.consultations', compact('consultations', 'nurses', 'doctors'));
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

        // Get all patients, grouped by email to avoid duplicates in view
        $patients = $query->latest()->paginate(20);

        return view('admin.patients', compact('patients'));
    }

    /**
     * Show single consultation details
     */
    public function showConsultation($id)
    {
        $consultation = Consultation::with(['doctor', 'payment'])->findOrFail($id);
        
        return view('admin.consultation-details', compact('consultation'));
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

        $consultation = Consultation::findOrFail($id);
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

        // Send notification to the new doctor
        try {
            \Mail::to($doctor->email)->send(new \App\Mail\ConsultationDoctorNotification([
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
            ]));
        } catch (\Exception $e) {
            \Log::warning("Failed to send notification to reassigned doctor: " . $e->getMessage());
        }

           $message = 'Doctor reassigned successfully from ' . 
                   ($oldDoctor ? $oldDoctor->full_name : 'No Doctor') . 
                   ' to ' . $doctor->full_name;

        return response()->json([
            'success' => true,
            'message' => $message
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
        $consultation = Consultation::with('doctor')->findOrFail($id);

        // Validate - treatment plan must exist
        if (!$consultation->hasTreatmentPlan()) {
            return response()->json([
                'success' => false,
                'message' => 'No treatment plan has been created for this consultation yet'
            ], 400);
        }

        // Send treatment plan notification email
        try {
            Mail::to($consultation->email)->queue(new TreatmentPlanNotification($consultation));
            
            \Log::info('Treatment plan manually forwarded by admin', [
                'consultation_id' => $consultation->id,
                'reference' => $consultation->reference,
                'email' => $consultation->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Treatment plan sent successfully to ' . $consultation->email
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to manually forward treatment plan', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send treatment plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display payments list
     */
    public function payments(Request $request)
    {
        $query = Payment::with('doctor');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(20);

        return view('admin.payments', compact('payments'));
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

        $doctors = $query->orderByRaw('COALESCE(NULLIF(name, ""), CONCAT(first_name, " ", last_name))')->orderBy('order')->paginate(20);

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
     * Delete a doctor
     */
    public function deleteDoctor($id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            
            // Check if doctor has any consultations
            $consultationsCount = Consultation::where('doctor_id', $id)->count();
            
            if ($consultationsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete doctor with existing consultations. Please reassign or remove consultations first.'
                ], 400);
            }

            $doctor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Doctor deleted successfully!'
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
    public function adminUsers()
    {
        $admins = AdminUser::latest()->paginate(10);
        
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
            AdminUser::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Admin user created successfully!'
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

    // ==================== CANVASSERS MANAGEMENT ====================

    /**
     * Display canvassers list
     */
    public function canvassers()
    {
        $canvassers = Canvasser::with('createdBy')->withCount('consultations')->latest()->paginate(10);
        
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
     * Delete a canvasser
     */
    public function deleteCanvasser($id)
    {
        try {
            $canvasser = Canvasser::findOrFail($id);
            $canvasser->delete();

            return response()->json([
                'success' => true,
                'message' => 'Canvasser deleted successfully!'
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
    public function nurses()
    {
        $nurses = Nurse::with('createdBy')->withCount('consultations')->latest()->paginate(10);
        
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
     * Delete a nurse
     */
    public function deleteNurse($id)
    {
        try {
            $nurse = Nurse::findOrFail($id);
            $nurse->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nurse deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete nurse: ' . $e->getMessage()
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

        $doctors = $query->latest()->paginate(15);
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
            $doctor = Doctor::findOrFail($id);
            
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
        $useDefaultForAll = Setting::get('use_default_fee_for_all', false);

        return view('admin.settings', compact('settings', 'defaultFee', 'useDefaultForAll'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'default_consultation_fee' => 'required|numeric|min:0',
                'use_default_fee_for_all' => 'nullable|boolean',
            ]);

            Setting::set('default_consultation_fee', $validated['default_consultation_fee'], 'number');
            Setting::set('use_default_fee_for_all', $request->has('use_default_fee_for_all') ? 1 : 0, 'boolean');

            // If forcing all doctors to use default fee, update all doctors
            if ($request->has('use_default_fee_for_all')) {
                Doctor::query()->update([
                    'use_default_fee' => true,
                    'consultation_fee' => $validated['default_consultation_fee']
                ]);
            }

            return redirect()->back()->with('success', 'Settings updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Download/view certificate from database
     */
    public function viewCertificate($id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            
            if (!$doctor->certificate_data) {
                return response()->json([
                    'success' => false,
                    'message' => 'No certificate found for this doctor.'
                ], 404);
            }
            
            // Decode the base64 data
            $fileContent = base64_decode($doctor->certificate_data);
            
            // Return the file for viewing/download
            return response($fileContent)
                ->header('Content-Type', $doctor->certificate_mime_type ?? 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . ($doctor->certificate_original_name ?? 'certificate.pdf') . '"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load certificate: ' . $e->getMessage()
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
}
