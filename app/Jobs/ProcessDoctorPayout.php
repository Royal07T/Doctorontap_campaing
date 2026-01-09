<?php

namespace App\Jobs;

use App\Models\Consultation;
use App\Models\DoctorPayout;
use App\Services\KorapayPayoutService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDoctorPayout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // Wait 60 seconds between retries

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $consultationId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(KorapayPayoutService $payoutService): void
    {
        try {
            $consultation = Consultation::with(['doctor', 'booking'])->findOrFail($this->consultationId);

            // Validate consultation is eligible for payout
            if ($consultation->status !== 'completed' || $consultation->payment_status !== 'paid') {
                Log::warning('Consultation not eligible for payout', [
                    'consultation_id' => $this->consultationId,
                    'status' => $consultation->status,
                    'payment_status' => $consultation->payment_status,
                ]);
                return;
            }

            // Check if payout already exists
            $existingPayout = DoctorPayout::where('consultation_id', $this->consultationId)
                ->whereIn('status', ['pending', 'processing', 'success'])
                ->first();

            if ($existingPayout) {
                Log::info('Payout already exists for consultation', [
                    'consultation_id' => $this->consultationId,
                    'payout_id' => $existingPayout->id,
                ]);
                return;
            }

            $doctor = $consultation->doctor;
            if (!$doctor) {
                Log::error('Doctor not found for consultation', [
                    'consultation_id' => $this->consultationId,
                ]);
                return;
            }

            // Calculate doctor's share (70% by default)
            $doctorPercentage = \App\Models\Setting::get('doctor_payment_percentage', 70);
            $consultationFee = $doctor->effective_consultation_fee ?? 0;
            $doctorAmount = ($consultationFee * $doctorPercentage) / 100;

            if ($doctorAmount <= 0) {
                Log::warning('Invalid payout amount', [
                    'consultation_id' => $this->consultationId,
                    'consultation_fee' => $consultationFee,
                    'doctor_percentage' => $doctorPercentage,
                ]);
                return;
            }

            // Generate unique payout reference
            $payoutReference = $payoutService->generatePayoutReference();

            // Prepare metadata
            $metadata = [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->full_name,
                'doctor_email' => $doctor->email,
                'consultation_id' => $consultation->id,
                'consultation_reference' => $consultation->reference,
                'booking_reference' => $consultation->booking_id ? ($consultation->booking->reference ?? null) : null,
                'consultation_fee' => $consultationFee,
                'doctor_percentage' => $doctorPercentage,
                'doctor_amount' => $doctorAmount,
                'initiated_at' => now()->toIso8601String(),
            ];

            // Create payout record
            $payout = DoctorPayout::create([
                'doctor_id' => $doctor->id,
                'consultation_id' => $consultation->id,
                'booking_reference' => $metadata['booking_reference'],
                'payout_reference' => $payoutReference,
                'amount' => $doctorAmount,
                'currency' => 'NGN',
                'status' => 'pending',
                'metadata' => $metadata,
            ]);

            // Initiate payout via Korapay
            $result = $payoutService->sendPayout(
                $doctor,
                $doctorAmount,
                $payoutReference,
                $metadata
            );

            // Update payout record with Korapay response
            $updateData = [
                'korapay_response' => $result['response'] ?? null,
            ];

            if ($result['success']) {
                $updateData['status'] = ($result['data']['status'] ?? 'processing') === 'success' ? 'success' : 'processing';
                $updateData['korapay_reference'] = $result['data']['korapay_reference'] ?? null;

                Log::info('✅ Doctor payout initiated successfully', [
                    'payout_id' => $payout->id,
                    'payout_reference' => $payoutReference,
                    'consultation_id' => $this->consultationId,
                    'status' => $updateData['status'],
                ]);
            } else {
                $updateData['status'] = 'failed';
                Log::error('❌ Doctor payout failed', [
                    'payout_id' => $payout->id,
                    'payout_reference' => $payoutReference,
                    'error' => $result['message'],
                ]);
            }

            $payout->update($updateData);

        } catch (\Exception $e) {
            Log::error('Failed to process doctor payout job', [
                'consultation_id' => $this->consultationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Doctor payout job failed after all retries', [
            'consultation_id' => $this->consultationId,
            'error' => $exception->getMessage(),
        ]);
    }
}
