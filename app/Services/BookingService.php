<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingPatient;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\FeeAdjustmentLog;
use App\Mail\FeeAdjustmentNotification;
use App\Mail\FeeAdjustmentAdminNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Create a multi-patient booking
     */
    public function createMultiPatientBooking(array $data): Booking
    {
        DB::beginTransaction();
        try {
            // 1. Create booking container
            $booking = Booking::create([
                'reference' => $this->generateBookingReference(),
                'payer_name' => $data['payer_name'],
                'payer_email' => $data['payer_email'],
                'payer_mobile' => $data['payer_mobile'],
                'consult_mode' => $data['consult_mode'] ?? 'chat',
                'doctor_id' => $data['doctor_id'] ?? null,
                'canvasser_id' => $data['canvasser_id'] ?? null,
                'nurse_id' => $data['nurse_id'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            // 2. Get doctor's base fee
            $doctor = Doctor::find($data['doctor_id']);
            $baseFee = $doctor ? $doctor->effective_consultation_fee : 0;

            // 3. Add each patient to booking
            foreach ($data['patients'] as $index => $patientData) {
                // 3a. Create or find patient record
                $patient = $this->findOrCreatePatient($patientData, $data['payer_email']);

                // 3b. Create consultation record for this patient
                $consultation = Consultation::create([
                    'reference' => $this->generateConsultationReference(),
                    'booking_id' => $booking->id,
                    'patient_id' => $patient->id,
                    'is_multi_patient_booking' => true,
                    'first_name' => $patientData['first_name'],
                    'last_name' => $patientData['last_name'],
                    'email' => $patientData['email'] ?? $data['payer_email'],
                    'mobile' => $patientData['mobile'] ?? $data['payer_mobile'],
                    'age' => $patientData['age'],
                    'gender' => $patientData['gender'],
                    'problem' => $patientData['problem'] ?? 'General consultation',
                    'symptoms' => $patientData['symptoms'] ?? '',
                    'severity' => $patientData['severity'] ?? 'moderate',
                    'consult_mode' => $booking->consult_mode,
                    'doctor_id' => $booking->doctor_id,
                    'canvasser_id' => $booking->canvasser_id,
                    'nurse_id' => $booking->nurse_id,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                ]);

                // 3c. Link patient to booking with pricing
                BookingPatient::create([
                    'booking_id' => $booking->id,
                    'patient_id' => $patient->id,
                    'consultation_id' => $consultation->id,
                    'relationship_to_payer' => $patientData['relationship'] ?? 'self',
                    'base_fee' => $baseFee,
                    'adjusted_fee' => $baseFee,  // Initially same as base
                    'order_index' => $index,
                    'consultation_status' => 'pending',
                ]);
            }

            // 4. Calculate total and create invoice
            $booking->total_amount = $baseFee * count($data['patients']);
            $booking->total_adjusted_amount = $booking->total_amount;
            $booking->save();

            $this->createInvoice($booking);

            DB::commit();
            return $booking->fresh(['bookingPatients.patient', 'bookingPatients.consultation', 'doctor', 'invoice']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create multi-patient booking', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Find or create patient record
     */
    private function findOrCreatePatient(array $patientData, string $payerEmail): Patient
    {
        // Try to find existing patient by email or name+age combination
        $email = $patientData['email'] ?? $payerEmail;
        
        $patient = Patient::where('email', $email)->first();

        if (!$patient) {
            // Create new patient record
            $patient = Patient::create([
                'name' => $patientData['first_name'] . ' ' . $patientData['last_name'],
                'email' => $email,
                'phone' => $patientData['mobile'] ?? null,
                'age' => $patientData['age'],
                'gender' => $patientData['gender'],
                'is_minor' => $patientData['age'] < 18,
                'date_of_birth' => $patientData['date_of_birth'] ?? null,
            ]);
        }

        // If patient is a minor and has a guardian relationship, link them
        if ($patient->isMinor() && isset($patientData['guardian_id'])) {
            $patient->guardian_id = $patientData['guardian_id'];
            $patient->save();
        }

        return $patient;
    }

    /**
     * Create invoice with line items for each patient
     */
    private function createInvoice(Booking $booking): Invoice
    {
        $invoice = Invoice::create([
            'reference' => 'INV-' . time() . '-' . Str::random(6),
            'booking_id' => $booking->id,
            'customer_name' => $booking->payer_name,
            'customer_email' => $booking->payer_email,
            'customer_phone' => $booking->payer_mobile,
            'subtotal' => $booking->total_amount,
            'total_amount' => $booking->total_amount,
            'status' => 'draft',
            'currency' => 'NGN',
        ]);

        // Create line item for each patient
        foreach ($booking->bookingPatients as $bp) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'patient_id' => $bp->patient_id,
                'consultation_id' => $bp->consultation_id,
                'description' => "Consultation for {$bp->patient->name} ({$bp->patient->age} yrs, {$bp->patient->gender})",
                'quantity' => 1,
                'unit_price' => $bp->base_fee,
                'adjustment' => 0.00,
                'total_price' => $bp->adjusted_fee,
                'item_type' => 'consultation',
                'order_index' => $bp->order_index,
            ]);
        }

        return $invoice;
    }

    /**
     * Adjust patient fee (doctor-initiated)
     */
    public function adjustPatientFee(
        Booking $booking,
        int $patientId,
        float $newFee,
        string $reason,
        Doctor $doctor
    ): bool {
        DB::beginTransaction();
        try {
            $bookingPatient = BookingPatient::where('booking_id', $booking->id)
                ->where('patient_id', $patientId)
                ->firstOrFail();

            $oldFee = $bookingPatient->adjusted_fee;

            // Update booking_patient
            $bookingPatient->update([
                'adjusted_fee' => $newFee,
                'fee_adjustment_reason' => $reason,
                'fee_adjusted_by' => $doctor->id,
                'fee_adjusted_at' => now(),
            ]);

            // Update invoice item
            $invoiceItem = InvoiceItem::where('invoice_id', $booking->invoice->id)
                ->where('patient_id', $patientId)
                ->first();

            if ($invoiceItem) {
                $adjustment = $newFee - $invoiceItem->unit_price;
                $invoiceItem->update([
                    'adjustment' => $adjustment,
                    'adjustment_reason' => $reason,
                    'total_price' => $newFee,
                ]);
            }

            // Recalculate invoice totals
            $invoice = $booking->invoice;
            $oldTotal = $invoice->total_amount;
            $invoice->recalculate();

            // Recalculate booking totals
            $booking->total_adjusted_amount = $invoice->total_amount;
            $booking->save();

            // Log the adjustment
            $log = FeeAdjustmentLog::create([
                'booking_id' => $booking->id,
                'patient_id' => $patientId,
                'invoice_item_id' => $invoiceItem->id ?? null,
                'adjusted_by_type' => 'doctor',
                'adjusted_by_id' => $doctor->id,
                'old_amount' => $oldFee,
                'new_amount' => $newFee,
                'adjustment_reason' => $reason,
                'total_invoice_before' => $oldTotal,
                'total_invoice_after' => $invoice->total_amount,
            ]);

            // Trigger notifications
            $this->notifyFeeAdjustment($booking, $patientId, $oldFee, $newFee, $reason);

            // Update log with notification status
            $log->update([
                'notification_sent_to_payer' => true,
                'notification_sent_to_accountant' => true,
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to adjust patient fee', [
                'booking_id' => $booking->id,
                'patient_id' => $patientId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send fee adjustment notifications
     */
    private function notifyFeeAdjustment($booking, $patientId, $oldFee, $newFee, $reason)
    {
        $patient = Patient::find($patientId);

        try {
            // Notify payer
            if (class_exists(FeeAdjustmentNotification::class)) {
                Mail::to($booking->payer_email)->queue(
                    new FeeAdjustmentNotification($booking, $patient, $oldFee, $newFee, $reason)
                );
            }

            // Notify accountant/admin
            $accountantEmail = config('app.accountant_email') ?? config('mail.from.address');
            if ($accountantEmail && class_exists(FeeAdjustmentAdminNotification::class)) {
                Mail::to($accountantEmail)->queue(
                    new FeeAdjustmentAdminNotification($booking, $patient, $oldFee, $newFee, $reason)
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send fee adjustment notifications', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark booking as completed
     */
    public function completeBooking(Booking $booking): void
    {
        DB::beginTransaction();
        try {
            $booking->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Mark all consultations as completed
            $booking->consultations()->update([
                'status' => 'completed',
                'consultation_completed_at' => now(),
            ]);

            // Update invoice status to pending (awaiting payment)
            $booking->invoice->update([
                'status' => 'pending',
                'issued_at' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate unique booking reference
     */
    private function generateBookingReference(): string
    {
        do {
            $reference = 'BOOK-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Booking::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Generate unique consultation reference
     */
    private function generateConsultationReference(): string
    {
        do {
            $reference = 'CONS-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Consultation::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Get booking with all relationships
     */
    public function getBookingDetails(int $bookingId): ?Booking
    {
        return Booking::with([
            'bookingPatients.patient',
            'bookingPatients.consultation',
            'consultations',
            'doctor',
            'invoice.items',
            'feeAdjustmentLogs'
        ])->find($bookingId);
    }
}

