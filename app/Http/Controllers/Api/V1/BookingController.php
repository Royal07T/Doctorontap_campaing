<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Get all bookings
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userType = $user->getMorphClass();

        $query = Booking::query();

        // Filter based on user type
        if ($userType === 'Doctor') {
            $query->where('doctor_id', $user->id);
        } elseif ($userType === 'Canvasser') {
            $query->where('canvasser_id', $user->id);
        }

        $bookings = $query->with(['doctor', 'patients'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Create a new booking
     */
    public function store(Request $request)
    {
        $request->validate([
            'payer_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'payer_email' => 'required|email|max:255',
            'payer_mobile' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'consult_mode' => 'required|in:voice,video,chat',
            'doctor_id' => 'nullable|exists:doctors,id',
            'patients' => 'required|array|min:1|max:10', // SECURITY: Limit to 10 patients per booking
            'patients.*.first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'patients.*.last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'patients.*.age' => 'required|integer|min:0|max:150',
            'patients.*.gender' => 'required|in:male,female',
            'patients.*.problem' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        // SECURITY: Sanitize patient data
        $sanitizedData = $request->all();
        $sanitizedData['payer_name'] = trim(strip_tags($sanitizedData['payer_name']));
        $sanitizedData['payer_email'] = strtolower(trim($sanitizedData['payer_email']));
        $sanitizedData['payer_mobile'] = preg_replace('/[^0-9+\-()\s]/', '', $sanitizedData['payer_mobile']);
        
        foreach ($sanitizedData['patients'] as $key => $patient) {
            $sanitizedData['patients'][$key]['first_name'] = trim(strip_tags($patient['first_name']));
            $sanitizedData['patients'][$key]['last_name'] = trim(strip_tags($patient['last_name']));
            $sanitizedData['patients'][$key]['problem'] = trim(strip_tags($patient['problem']));
        }

        try {
            $result = $this->bookingService->createBooking($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $result
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific booking
     */
    public function show($id)
    {
        $booking = Booking::with(['doctor', 'patients', 'consultations'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    /**
     * Update booking
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();

        // Only admin or doctor can update
        if ($user->getMorphClass() !== 'AdminUser' && 
            ($user->getMorphClass() !== 'Doctor' || $booking->doctor_id !== $user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'sometimes|in:pending,confirmed,completed,cancelled',
            'scheduled_at' => 'sometimes|date',
        ]);

        $booking->update($request->only(['status', 'scheduled_at']));

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => $booking->load(['doctor', 'patients'])
        ]);
    }

    /**
     * Adjust booking fee
     */
    public function adjustFee(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();

        // SECURITY: Only admin can adjust fees
        if ($user->getMorphClass() !== 'AdminUser') {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }

        $request->validate([
            'total_adjusted_amount' => 'required|numeric|min:0|max:1000000', // Max 1 million
        ]);

        $oldAmount = $booking->total_adjusted_amount;
        $booking->update(['total_adjusted_amount' => $request->total_adjusted_amount]);

        // Log for audit trail
        \Log::info('Booking fee adjusted', [
            'booking_id' => $id,
            'old_amount' => $oldAmount,
            'new_amount' => $request->total_adjusted_amount,
            'adjusted_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fee adjusted successfully',
            'data' => $booking
        ]);
    }

    /**
     * Get consultations for a booking
     */
    public function getConsultations($id)
    {
        $booking = Booking::findOrFail($id);
        $consultations = $booking->consultations()->with(['doctor', 'patient'])->get();

        return response()->json([
            'success' => true,
            'data' => $consultations
        ]);
    }
}

