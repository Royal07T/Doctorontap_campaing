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
use App\Models\AdminUser;
use App\Models\Canvasser;
use App\Models\Nurse;

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
        ];

        return view('admin.dashboard', compact('stats'));
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
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $consultations = $query->latest()->paginate(20);
        
        // Get all nurses for assignment dropdown
        $nurses = Nurse::where('is_active', true)->orderBy('name')->get();

        return view('admin.consultations', compact('consultations', 'nurses'));
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
                  ->orWhere('reference', 'like', "%{$search}%");
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

        if ($consultation->payment_request_sent) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request already sent on ' . $consultation->payment_request_sent_at->format('M d, Y H:i')
            ], 400);
        }

        if (!$consultation->requiresPayment()) {
            return response()->json([
                'success' => false,
                'message' => 'This consultation does not require payment (no fee set)'
            ], 400);
        }

        // Send payment request email
        try {
            Mail::to($consultation->email)->send(new PaymentRequest($consultation));

            // Update consultation
            $consultation->update([
                'payment_request_sent' => true,
                'payment_request_sent_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment request email sent successfully to ' . $consultation->email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
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

        $doctors = $query->orderBy('order')->paginate(20);

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
                'message' => 'Medical documents forwarded successfully to Dr. ' . $consultation->doctor->name
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
        ]);

        // Handle checkbox value
        $validated['is_available'] = $request->has('is_available') ? true : false;

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

        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['created_by'] = auth()->guard('admin')->id();

        try {
            $canvasser = Canvasser::create($validated);
            
            // Send email verification notification
            $canvasser->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Canvasser created successfully! A verification email has been sent.'
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

        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['created_by'] = auth()->guard('admin')->id();

        try {
            $nurse = Nurse::create($validated);
            
            // Send email verification notification
            $nurse->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Nurse created successfully! A verification email has been sent.'
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
}
