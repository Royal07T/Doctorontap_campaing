<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Get all payments
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userType = $user->getMorphClass();

        $query = Payment::query();

        // Filter based on user type
        if ($userType === 'Patient') {
            $query->whereHas('consultation', function ($q) use ($user) {
                $q->where('patient_id', $user->id);
            });
        }

        $payments = $query->with(['consultation'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Get a specific payment
     */
    public function show($id)
    {
        $user = Auth::user();
        $payment = Payment::with(['consultation'])->findOrFail($id);

        // Check authorization
        $userType = $user->getMorphClass();
        if ($userType === 'Patient' && $payment->consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $payment,
        ]);
    }

    /**
     * Initialize payment
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'amount' => 'required|numeric|min:100|max:1000000', // Min 100, Max 1 million
        ]);

        $consultation = Consultation::findOrFail($request->consultation_id);
        $user = Auth::user();

        // SECURITY: Check authorization
        if ($user->getMorphClass() === 'Patient' && $consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // SECURITY: Validate amount matches consultation fee
        $expectedAmount = $consultation->doctor->consultation_fee ?? 0;
        if (abs($request->amount - $expectedAmount) > 100) { // Allow 100 naira difference
            return response()->json([
                'success' => false,
                'message' => 'Payment amount does not match consultation fee'
            ], 400);
        }

        // Initialize payment logic would go here
        // This is a placeholder

        // Log payment initialization
        \Log::info('Payment initialized', [
            'consultation_id' => $request->consultation_id,
            'amount' => $request->amount,
            'user_id' => $user->id,
            'user_type' => $user->getMorphClass(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment initialized',
            'data' => [
                'payment_reference' => 'PAY-' . time(),
                'amount' => $request->amount,
            ]
        ]);
    }

    /**
     * Verify payment
     */
    public function verify(Request $request)
    {
        $request->validate([
            'payment_reference' => 'required|string',
        ]);

        // Payment verification logic would go here

        return response()->json([
            'success' => true,
            'message' => 'Payment verified',
        ]);
    }
}

