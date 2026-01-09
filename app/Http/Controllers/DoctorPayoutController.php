<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\DoctorPayout;
use App\Models\Doctor;
use App\Models\Setting;
use App\Services\KorapayPayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DoctorPayoutController extends Controller
{
    protected KorapayPayoutService $payoutService;

    public function __construct(KorapayPayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    /**
     * Get unpaid consultations for a doctor
     * Returns all consultations that are completed, paid by patient, but not yet paid to doctor
     * 
     * @param int $doctorId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnpaidConsultations(int $doctorId)
    {
        try {
            // Get all consultation IDs that are already included in pending/processing/success payouts
            $excludedConsultationIds = DoctorPayout::where('doctor_id', $doctorId)
                ->whereIn('status', ['pending', 'processing', 'success'])
                ->whereNotNull('consultation_ids')
                ->get()
                ->flatMap(function($payout) {
                    $ids = $payout->consultation_ids ?? [];
                    return is_array($ids) ? $ids : [];
                })
                ->unique()
                ->values()
                ->toArray();

            // Get consultations that are completed, paid by patient, but not yet paid to doctor
            $query = Consultation::where('doctor_id', $doctorId)
                ->where('status', 'completed')
                ->where('payment_status', 'paid');

            // Exclude consultations already in pending/processing/success payouts
            if (!empty($excludedConsultationIds)) {
                $query->whereNotIn('id', $excludedConsultationIds);
            }

            $consultations = $query->with('payment')->latest()->get();
            $doctor = Doctor::findOrFail($doctorId);

            return response()->json([
                'success' => true,
                'consultations' => $consultations->map(function($c) use ($doctor) {
                    return [
                        'id' => $c->id,
                        'reference' => $c->reference,
                        'patient_name' => $c->full_name,
                        'date' => $c->created_at->format('Y-m-d'),
                        'amount' => $doctor->effective_consultation_fee ?? 0,
                    ];
                }),
                'total_count' => $consultations->count(),
                'total_amount' => $consultations->sum(function($c) use ($doctor) {
                    return $doctor->effective_consultation_fee ?? 0;
                }),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get unpaid consultations', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get unpaid consultations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create batch payout for multiple consultations
     * Similar to createDoctorPayment in admin dashboard
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createBatchPayout(Request $request)
    {
        try {
            $validated = $request->validate([
                'doctor_id' => 'required|exists:doctors,id',
                'consultation_ids' => 'required|array|min:1',
                'consultation_ids.*' => 'exists:consultations,id',
                'doctor_percentage' => 'nullable|numeric|min:0|max:100',
                'period_from' => 'nullable|date',
                'period_to' => 'nullable|date|after_or_equal:period_from',
            ]);

            $doctor = Doctor::with('bankAccounts')->findOrFail($validated['doctor_id']);

            // Get verified bank account
            $bankAccount = $doctor->bankAccounts()
                ->where('is_verified', true)
                ->where('is_default', true)
                ->first();

            if (!$bankAccount) {
                $bankAccount = $doctor->bankAccounts()
                    ->where('is_verified', true)
                    ->first();
            }

            if (!$bankAccount || !$bankAccount->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor does not have a verified bank account.'
                ], 400);
            }

            // Validate consultations
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
                    'message' => 'Some consultations are not completed. Only completed consultations can be included.'
                ], 400);
            }

            // Check if all consultations are paid
            $unpaidConsultations = $submittedConsultations->where('payment_status', '!=', 'paid');
            if ($unpaidConsultations->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some consultations are not paid. Only consultations with payment_status = "paid" can be included.'
                ], 400);
            }

            // Get valid consultations
            $consultations = Consultation::whereIn('id', $validated['consultation_ids'])
                ->where('doctor_id', $doctor->id)
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->get();

            if ($consultations->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid paid consultations found.'
                ], 400);
            }

            // Check if any consultations are already in a payout
            $excludedIds = DoctorPayout::where('doctor_id', $doctor->id)
                ->whereIn('status', ['pending', 'processing', 'success'])
                ->whereNotNull('consultation_ids')
                ->get()
                ->flatMap(function($payout) {
                    return $payout->consultation_ids ?? [];
                })
                ->unique()
                ->toArray();

            $alreadyIncluded = $consultations->whereIn('id', $excludedIds);
            if ($alreadyIncluded->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some consultations are already included in a pending/processing payout.',
                    'conflicting_ids' => $alreadyIncluded->pluck('id')->toArray()
                ], 400);
            }

            // Use custom percentage or default from settings
            $doctorPercentage = $validated['doctor_percentage'] ?? Setting::get('doctor_payment_percentage', 70);

            // Calculate payment details (like DoctorPayment::calculatePayment)
            $calculation = DoctorPayout::calculatePayment($consultations, $doctorPercentage);

            if ($calculation['doctor_amount'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payout amount. Total consultation amount is zero.'
                ], 400);
            }

            // Generate unique payout reference
            $payoutReference = $this->payoutService->generatePayoutReference();

            // Create payout record
            $payout = DoctorPayout::create([
                'doctor_id' => $doctor->id,
                'consultation_ids' => $consultations->pluck('id')->toArray(),
                'payout_reference' => $payoutReference,
                'total_consultations_amount' => $calculation['total_consultations_amount'],
                'total_consultations_count' => $calculation['total_consultations_count'],
                'doctor_percentage' => $calculation['doctor_percentage'],
                'platform_percentage' => $calculation['platform_percentage'],
                'amount' => $calculation['doctor_amount'],
                'platform_fee' => $calculation['platform_fee'],
                'currency' => 'NGN',
                'status' => 'pending',
                'period_from' => $validated['period_from'] ?? null,
                'period_to' => $validated['period_to'] ?? null,
                'metadata' => [
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $doctor->full_name,
                    'doctor_email' => $doctor->email,
                    'consultation_count' => $calculation['total_consultations_count'],
                    'initiated_at' => now()->toIso8601String(),
                ],
            ]);

            // Initiate payout via Korapay
            $result = $this->payoutService->sendPayout(
                $doctor,
                $calculation['doctor_amount'],
                $payoutReference,
                [
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $doctor->full_name,
                    'doctor_email' => $doctor->email,
                    'consultation_ids' => $consultations->pluck('id')->toArray(),
                    'consultation_count' => $calculation['total_consultations_count'],
                    'total_amount' => $calculation['total_consultations_amount'],
                    'doctor_percentage' => $doctorPercentage,
                    'doctor_amount' => $calculation['doctor_amount'],
                    'initiated_at' => now()->toIso8601String(),
                ]
            );

            // Update payout record with Korapay response
            $updateData = [
                'korapay_response' => $result['response'] ?? null,
            ];

            if ($result['success']) {
                $updateData['status'] = ($result['data']['status'] ?? 'processing') === 'success' ? 'success' : 'processing';
                $updateData['korapay_reference'] = $result['data']['korapay_reference'] ?? null;

                Log::info('✅ Doctor batch payout initiated successfully', [
                    'payout_id' => $payout->id,
                    'payout_reference' => $payoutReference,
                    'doctor_id' => $doctor->id,
                    'consultation_count' => $calculation['total_consultations_count'],
                    'amount' => $calculation['doctor_amount'],
                ]);
            } else {
                $updateData['status'] = 'failed';
                Log::error('❌ Doctor batch payout failed', [
                    'payout_id' => $payout->id,
                    'payout_reference' => $payoutReference,
                    'error' => $result['message'],
                ]);
            }

            $payout->update($updateData);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'payout' => $payout->fresh()->load('doctor'),
                'data' => $result['data'] ?? null,
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Failed to create batch payout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payout history for a doctor
     * 
     * @param Request $request
     * @param int $doctorId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayoutHistory(Request $request, int $doctorId)
    {
        $payouts = DoctorPayout::where('doctor_id', $doctorId)
            ->with(['doctor'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $payouts
        ]);
    }

    /**
     * Get payout details
     * 
     * @param int $payoutId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayout(int $payoutId)
    {
        $payout = DoctorPayout::with(['doctor'])->findOrFail($payoutId);
        $payout->load(['consultations' => function($query) {
            $query->with('patient', 'payment');
        }]);

        return response()->json([
            'success' => true,
            'data' => $payout
        ]);
    }

    /**
     * Handle Korapay payout webhook
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request)
    {
        // Set precision to maintain amount field precision
        ini_set('serialize_precision', '-1');

        // Check for POST method and signature header
        if (strtoupper($request->method()) !== 'POST' || !$request->hasHeader('x-korapay-signature')) {
            Log::warning('Invalid doctor payout webhook request', [
                'method' => $request->method(),
                'has_signature' => $request->hasHeader('x-korapay-signature'),
                'ip' => $request->ip()
            ]);
            return response()->json(['status' => 'invalid_request'], 200);
        }

        Log::info('Korapay Doctor Payout Webhook Received', [
            'event' => $request->input('event'),
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
        ]);

        try {
            $requestBody = $request->all();
            $webhookSignature = $request->header('x-korapay-signature');
            $korapaySecretKey = config('services.korapay.secret_key');

            // Verify signature
            if ($webhookSignature && $korapaySecretKey && isset($requestBody['data'])) {
                $dataJson = json_encode($requestBody['data'], JSON_UNESCAPED_SLASHES);
                $expectedSignature = hash_hmac('sha256', $dataJson, $korapaySecretKey);

                if ($webhookSignature !== $expectedSignature) {
                    Log::warning('SECURITY ALERT: Invalid doctor payout webhook signature', [
                        'expected' => $expectedSignature,
                        'received' => $webhookSignature,
                        'ip' => $request->ip(),
                    ]);
                    return response()->json(['status' => 'invalid_signature'], 200);
                }

                Log::info('Doctor payout webhook signature verified successfully');
            } else {
                Log::warning('Doctor payout webhook received without signature or data');
                return response()->json(['status' => 'missing_signature_or_data'], 200);
            }

            $event = $request->input('event');
            $data = $request->input('data');

            if (!$data || !isset($data['reference'])) {
                Log::error('Invalid doctor payout webhook payload', ['payload' => $request->all()]);
                return response()->json(['status' => 'invalid_payload'], 200);
            }

            $korapayReference = $data['reference'];
            $status = $data['status'] ?? 'processing';

            // Find payout by Korapay reference or payout reference
            $payout = DoctorPayout::where('korapay_reference', $korapayReference)
                ->orWhere('payout_reference', $korapayReference)
                ->first();

            if (!$payout) {
                Log::warning('Doctor payout webhook received for unknown payout', [
                    'korapay_reference' => $korapayReference,
                    'event' => $event
                ]);
                return response()->json(['status' => 'payout_not_found'], 200);
            }

            // Idempotency check
            $currentStatus = $payout->status;
            if ($currentStatus === 'success' && $status === 'success') {
                Log::info('Webhook already processed (idempotency check)', [
                    'payout_id' => $payout->id,
                    'payout_reference' => $payout->payout_reference,
                ]);
                return response()->json(['status' => 'already_processed'], 200);
            }

            Log::info('Processing doctor payout webhook', [
                'payout_id' => $payout->id,
                'payout_reference' => $payout->payout_reference,
                'status' => $status,
                'event' => $event,
            ]);

            // Update payout based on status
            $updateData = [
                'korapay_response' => $data,
            ];

            if ($status === 'success') {
                $updateData['status'] = 'success';
                $updateData['korapay_reference'] = $korapayReference;

                Log::info('✅ Doctor payout completed successfully', [
                    'payout_id' => $payout->id,
                    'payout_reference' => $payout->payout_reference,
                    'doctor_id' => $payout->doctor_id,
                    'amount' => $payout->amount,
                ]);

            } elseif ($status === 'failed') {
                $updateData['status'] = 'failed';
                $updateData['korapay_reference'] = $korapayReference;

                Log::warning('Doctor payout failed', [
                    'payout_id' => $payout->id,
                    'payout_reference' => $payout->payout_reference,
                    'message' => $data['message'] ?? 'Payout failed',
                ]);
            } else {
                $updateData['status'] = 'processing';
                if (!empty($korapayReference)) {
                    $updateData['korapay_reference'] = $korapayReference;
                }
            }

            $payout->update($updateData);

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Doctor payout webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
