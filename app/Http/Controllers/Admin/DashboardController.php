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
        $query = Consultation::with(['doctor', 'payment']);

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

        return view('admin.consultations', compact('consultations'));
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
}
