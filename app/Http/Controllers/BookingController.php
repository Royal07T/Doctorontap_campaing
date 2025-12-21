<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Doctor;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Show multi-patient booking form
     */
    public function create()
    {
        // Get only General Practitioner/General Practice doctors for patients
        // Available doctors are shown first, then unavailable ones
        $doctors = Doctor::approved()
            ->generalPractitioner() // Only show GP doctors to patients
            ->orderByRaw('CASE WHEN is_available = 1 THEN 0 ELSE 1 END')
            ->orderBy('order', 'asc')
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();

        // Get dynamic fees from settings
        $baseFee = \App\Models\Setting::get('default_consultation_fee', 3000);
        $additionalPatientDiscount = \App\Models\Setting::get('additional_child_discount_percentage', 60);

        return view('booking.multi-patient', compact('doctors', 'baseFee', 'additionalPatientDiscount'));
    }

    /**
     * Store a new multi-patient booking
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payer_name' => 'required|string|max:255',
            'payer_email' => 'required|email',
            'payer_mobile' => 'required|string',
            'consult_mode' => 'required|in:voice,video,chat',
            'doctor_id' => 'nullable|exists:doctors,id',
            'patients' => 'required|array|min:1',
            'patients.*.first_name' => 'required|string',
            'patients.*.last_name' => 'required|string',
            'patients.*.age' => 'required|integer|min:0|max:150',
            'patients.*.gender' => 'required|in:male,female',
            'patients.*.relationship' => 'required|string',
            'patients.*.problem' => 'required|string|min:10|max:500',
            'patients.*.symptoms' => 'nullable|string',
            'patients.*.severity' => 'required|in:mild,moderate,severe',
            'patients.*.emergency_symptoms' => 'nullable|array',
            'patients.*.medical_documents' => 'nullable|array',
            'patients.*.medical_documents.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your data.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            
            // Handle file uploads for each patient
            if ($request->has('patients')) {
                foreach ($data['patients'] as $index => &$patientData) {
                    $patientDocs = [];
                    $patientFiles = $request->file("patients.{$index}.medical_documents");

                    if ($patientFiles) {
                        foreach ($patientFiles as $file) {
                            $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                            // Store in private storage
                            $filePath = $file->storeAs('medical_documents', $fileName);
                            
                            $patientDocs[] = [
                                'original_name' => $file->getClientOriginalName(),
                                'stored_name' => $fileName,
                                'path' => $filePath,
                                'size' => $file->getSize(),
                                'mime_type' => $file->getMimeType(),
                            ];
                        }
                    }
                    $patientData['medical_documents'] = $patientDocs;
                }
            }

            $booking = $this->bookingService->createMultiPatientBooking($data);

            return response()->json([
                'success' => true,
                'message' => 'Multi-patient booking created successfully!',
                'booking' => $booking,
                'redirect_url' => route('booking.confirmation', ['reference' => $booking->reference])
            ]);

        } catch (\Exception $e) {
            \Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show booking confirmation page
     */
    public function confirmation($reference)
    {
        $booking = Booking::with(['bookingPatients.patient', 'doctor', 'invoice.items'])
            ->where('reference', $reference)
            ->firstOrFail();

        return view('booking.confirmation', compact('booking'));
    }

    /**
     * Adjust patient fee (doctor or admin)
     */
    public function adjustFee(Request $request, $bookingId)
    {
        // Check if user is a doctor or admin
        $isDoctor = Auth::guard('doctor')->check();
        $isAdmin = Auth::guard('admin')->check();
        
        if (!$isDoctor && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only doctors or admins can adjust fees.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'new_fee' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $booking = Booking::with('doctor')->findOrFail($bookingId);
            
            if ($isDoctor) {
                $adjustedBy = Auth::guard('doctor')->user();
                $adjustedByType = 'doctor';

            // Verify doctor is assigned to this booking
                if ($booking->doctor_id !== $adjustedBy->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to adjust fees for this booking.'
                ], 403);
                }
            } else {
                $adjustedBy = Auth::guard('admin')->user();
                $adjustedByType = 'admin';
            }

            $this->bookingService->adjustPatientFee(
                $booking,
                $request->patient_id,
                $request->new_fee,
                $request->reason,
                $adjustedBy,
                $adjustedByType
            );

            return response()->json([
                'success' => true,
                'message' => 'Fee adjusted successfully. Notifications sent to payer and admin.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Fee adjustment failed', [
                'error' => $e->getMessage(),
                'booking_id' => $bookingId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust fee. Please try again.'
            ], 500);
        }
    }

    /**
     * Apply multi-patient pricing rules to a booking
     */
    public function applyPricingRules(Request $request, $bookingId)
    {
        // Check if user is admin
        if (!Auth::guard('admin')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can apply pricing rules.'
            ], 403);
        }

        try {
            $booking = Booking::with('bookingPatients.patient')->findOrFail($bookingId);
            
            // Calculate fees based on pricing rules
            $calculatedFees = $this->bookingService->calculateMultiPatientFees($booking);
            
            if (empty($calculatedFees)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No patients found in this booking.'
                ], 400);
            }
            
            // Apply the calculated fees
            $this->bookingService->applyMultiPatientFees($booking, $calculatedFees);
            
            return response()->json([
                'success' => true,
                'message' => 'Pricing rules applied successfully!',
                'fees' => $calculatedFees
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to apply pricing rules', [
                'error' => $e->getMessage(),
                'booking_id' => $bookingId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to apply pricing rules: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking details (for doctor dashboard)
     */
    public function show($bookingId)
    {
        if (!Auth::guard('doctor')->check()) {
            abort(403);
        }

        $booking = $this->bookingService->getBookingDetails($bookingId);

        if (!$booking) {
            abort(404);
        }

        // Verify doctor is assigned
        $doctor = Auth::guard('doctor')->user();
        if ($booking->doctor_id !== $doctor->id) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'booking' => $booking
        ]);
    }

    /**
     * List all bookings for current doctor
     */
    public function index()
    {
        if (!Auth::guard('doctor')->check()) {
            abort(403);
        }

        $doctor = Auth::guard('doctor')->user();

        $bookings = Booking::with(['bookingPatients.patient', 'invoice'])
            ->where('doctor_id', $doctor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('doctor.bookings', compact('bookings'));
    }
}

