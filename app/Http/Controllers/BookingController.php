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
        $doctors = Doctor::where('is_available', true)->get();
        return view('booking.multi-patient', compact('doctors'));
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
            'patients.*.symptoms' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $booking = $this->bookingService->createMultiPatientBooking($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Multi-patient booking created successfully!',
                'booking' => $booking,
                'redirect_url' => route('booking.confirmation', ['reference' => $booking->reference])
            ]);

        } catch (\Exception $e) {
            \Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking. Please try again.'
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
     * Adjust patient fee (doctor only)
     */
    public function adjustFee(Request $request, $bookingId)
    {
        // Check if user is a doctor
        if (!Auth::guard('doctor')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only doctors can adjust fees.'
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
            $booking = Booking::findOrFail($bookingId);
            $doctor = Auth::guard('doctor')->user();

            // Verify doctor is assigned to this booking
            if ($booking->doctor_id !== $doctor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to adjust fees for this booking.'
                ], 403);
            }

            $this->bookingService->adjustPatientFee(
                $booking,
                $request->patient_id,
                $request->new_fee,
                $request->reason,
                $doctor
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

