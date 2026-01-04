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
use App\Models\Setting;
use App\Mail\FeeAdjustmentNotification;
use App\Mail\FeeAdjustmentAdminNotification;
use App\Mail\ConsultationConfirmation;
use App\Mail\ConsultationAdminAlert;
use App\Mail\ConsultationDoctorNotification;
use App\Notifications\ConsultationSmsNotification;
use App\Notifications\ConsultationWhatsAppNotification;
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

            // 2. Fetch Doctor's fee for automatic calculation
            $doctorFee = 0;
            if ($booking->doctor_id) {
                $doctor = Doctor::find($booking->doctor_id);
                if ($doctor) {
                    $doctorFee = $doctor->effective_consultation_fee ?? 0;
                } else {
                    \Log::warning('Doctor not found for booking', [
                        'doctor_id' => $booking->doctor_id,
                        'booking_reference' => $booking->reference
                    ]);
                }
            }
            
            // If no doctor fee, use default from settings
            if ($doctorFee <= 0) {
                $doctorFee = Setting::get('default_consultation_fee', 3000);
            }

            $totalBaseAmount = 0;
            $totalAdjustedAmount = 0;

            // 3. Add each patient to booking
            foreach ($data['patients'] as $index => $patientData) {
                // 3a. Create or find patient record
                $patient = $this->findOrCreatePatient($patientData, $data['payer_email'], $data['payer_mobile']);

                // 3b. Create consultation record for this patient
                $consultationData = [
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
                    'problem' => $patientData['problem'], // Forms ensure this is present
                    'medical_documents' => $patientData['medical_documents'] ?? null,
                    'severity' => $patientData['severity'] ?? 'moderate',
                    'emergency_symptoms' => $patientData['emergency_symptoms'] ?? null,
                    'consult_mode' => $booking->consult_mode,
                    'doctor_id' => $booking->doctor_id,
                    'canvasser_id' => $booking->canvasser_id,
                    'nurse_id' => $booking->nurse_id,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                ];
                
                // Only add symptoms if the column exists in the database
                // Symptoms can be stored in the problem field or as a separate field if migration exists
                if (isset($patientData['symptoms']) && !empty($patientData['symptoms'])) {
                    // If symptoms column doesn't exist, append to problem field
                    $consultationData['problem'] = $consultationData['problem'] . 
                        (strlen($consultationData['problem']) > 0 ? "\n\nSymptoms: " : "Symptoms: ") . 
                        $patientData['symptoms'];
                }
                
                $consultation = Consultation::create($consultationData);

                // Create notifications for patient and doctor
                try {
                    // Notification for patient (if authenticated)
                    if ($patient && $patient->id) {
                        \App\Models\Notification::create([
                            'user_type' => 'patient',
                            'user_id' => $patient->id,
                            'title' => 'Consultation Created',
                            'message' => "Your consultation (Ref: {$consultation->reference}) has been created. " . ($booking->doctor_id ? "You have been assigned to a doctor." : "A doctor will be assigned shortly."),
                            'type' => 'success',
                            'action_url' => patient_url('consultations/' . $consultation->id),
                            'data' => [
                                'consultation_id' => $consultation->id,
                                'consultation_reference' => $consultation->reference,
                                'booking_id' => $booking->id,
                                'type' => 'consultation_created'
                            ]
                        ]);
                    }

                    // Notification for doctor (if assigned)
                    if ($booking->doctor_id) {
                        \App\Models\Notification::create([
                            'user_type' => 'doctor',
                            'user_id' => $booking->doctor_id,
                            'title' => 'New Consultation Assigned',
                            'message' => "A new consultation (Ref: {$consultation->reference}) has been assigned to you. Patient: {$consultationData['first_name']} {$consultationData['last_name']}",
                            'type' => 'info',
                            'action_url' => doctor_url('consultations/' . $consultation->id),
                            'data' => [
                                'consultation_id' => $consultation->id,
                                'consultation_reference' => $consultation->reference,
                                'patient_name' => $consultationData['first_name'] . ' ' . $consultationData['last_name'],
                                'booking_id' => $booking->id,
                                'type' => 'new_consultation'
                            ]
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to create booking consultation notifications', [
                        'consultation_id' => $consultation->id,
                        'error' => $e->getMessage()
                    ]);
                }

                // 3c. Link patient to booking with pricing
                // First patient 100%, others based on setting (e.g. 60%)
                $discountMultiplier = \App\Models\Setting::get('additional_child_discount_percentage', 60) / 100;
                $baseFee = $doctorFee;
                $adjustedFee = ($index === 0) ? $doctorFee : ($doctorFee * $discountMultiplier);

                BookingPatient::create([
                    'booking_id' => $booking->id,
                    'patient_id' => $patient->id,
                    'consultation_id' => $consultation->id,
                    'relationship_to_payer' => $patientData['relationship'] ?? 'self',
                    'base_fee' => $baseFee,
                    'adjusted_fee' => $adjustedFee,
                    'order_index' => $index,
                    'consultation_status' => 'pending',
                ]);

                $totalBaseAmount += $baseFee;
                $totalAdjustedAmount += $adjustedFee;
            }

            // 4. Update booking totals
            $booking->total_amount = $totalBaseAmount;
            $booking->total_adjusted_amount = $totalAdjustedAmount;
            $booking->save();

            // 5. Reload booking with relationships for invoice creation
            $booking->load('bookingPatients.patient', 'bookingPatients.consultation');

            // 6. Create initial invoice
            if ($totalAdjustedAmount > 0) {
                try {
                    $this->createInvoice($booking);
                } catch (\Exception $e) {
                    \Log::error('Failed to create invoice for booking', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Don't fail the entire booking if invoice creation fails
                    // Invoice can be created later
                }
            }

            // 7. Send confirmation emails (don't fail booking if emails fail)
            try {
                $this->sendMultiPatientBookingEmails($booking);
            } catch (\Exception $e) {
                \Log::warning('Failed to send booking confirmation emails', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the entire booking if email sending fails
            }

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
    private function findOrCreatePatient(array $patientData, string $payerEmail, string $payerMobile): Patient
    {
        // Try to find existing patient by email (including trashed)
        $email = $patientData['email'] ?? $payerEmail;
        
        $patient = Patient::withTrashed()->where('email', $email)->first();

        if ($patient) {
            // Restore if trashed
            if ($patient->trashed()) {
                $patient->restore();
            }
            
            // Update details if they've changed
            $patient->update([
                'name' => $patientData['first_name'] . ' ' . $patientData['last_name'],
                'age' => $patientData['age'],
                'gender' => $patientData['gender'],
                'phone' => $patientData['mobile'] ?? $patient->phone ?? $payerMobile ?? 'N/A',
            ]);
        } else {
            // Use patient's mobile if provided, otherwise fall back to payer's mobile
            $phone = $patientData['mobile'] ?? $payerMobile ?? 'N/A';
            
            // Create new patient record
            $patient = Patient::create([
                'name' => $patientData['first_name'] . ' ' . $patientData['last_name'],
                'email' => $email,
                'phone' => $phone,
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
     * Only includes patients that have fees set (not null)
     */
    private function createInvoice(Booking $booking): Invoice
    {
        // Ensure relationships are loaded
        if (!$booking->relationLoaded('bookingPatients')) {
            $booking->load('bookingPatients.patient', 'bookingPatients.consultation');
        }
        
        // Calculate totals from patients with fees set
        $totalAmount = $booking->bookingPatients
            ->whereNotNull('adjusted_fee')
            ->sum('adjusted_fee');
        
        $invoice = Invoice::create([
            'reference' => 'INV-' . time() . '-' . Str::random(6),
            'booking_id' => $booking->id,
            'customer_name' => $booking->payer_name,
            'customer_email' => $booking->payer_email,
            'customer_phone' => $booking->payer_mobile,
            'subtotal' => $totalAmount,
            'total_amount' => $totalAmount,
            'status' => 'draft',
            'currency' => 'NGN',
        ]);

        // Create line item only for patients that have fees set
        foreach ($booking->bookingPatients as $bp) {
            // Skip patients without fees set
            if ($bp->adjusted_fee === null) {
                continue;
            }
            
            // Ensure patient relationship is loaded
            if (!$bp->relationLoaded('patient')) {
                $bp->load('patient');
            }
            
            // Get patient name safely
            $patientName = $bp->patient->name ?? 'Patient';
            $patientAge = $bp->patient->age ?? 'N/A';
            $patientGender = $bp->patient->gender ?? 'N/A';
            
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'patient_id' => $bp->patient_id,
                'consultation_id' => $bp->consultation_id,
                'description' => "Consultation for {$patientName} ({$patientAge} yrs, {$patientGender})",
                'quantity' => 1,
                'unit_price' => $bp->base_fee ?? $bp->adjusted_fee,
                'adjustment' => ($bp->adjusted_fee - ($bp->base_fee ?? $bp->adjusted_fee)),
                'total_price' => $bp->adjusted_fee,
                'item_type' => 'consultation',
                'order_index' => $bp->order_index ?? 0,
            ]);
        }

        return $invoice;
    }

    /**
     * Adjust patient fee (doctor or admin-initiated)
     */
    public function adjustPatientFee(
        Booking $booking,
        int $patientId,
        float $newFee,
        string $reason,
        $adjustedBy, // Can be Doctor or AdminUser
        string $adjustedByType = 'doctor' // 'doctor' or 'admin'
    ): bool {
        DB::beginTransaction();
        try {
            // Ensure doctor relationship is loaded
            if (!$booking->relationLoaded('doctor')) {
                $booking->load('doctor');
            }
            
            $bookingPatient = BookingPatient::where('booking_id', $booking->id)
                ->where('patient_id', $patientId)
                ->firstOrFail();

            $oldFee = $bookingPatient->adjusted_fee ?? 0;
            
            // If this is the first time setting a fee (was null), set base_fee as well
            if ($bookingPatient->base_fee === null) {
                $bookingPatient->base_fee = $newFee;
            }

            // Update booking_patient
            // fee_adjusted_by is constrained to doctors table, so set to null if admin adjusted
            $updateData = [
                'adjusted_fee' => $newFee,
                'fee_adjustment_reason' => $reason,
                'fee_adjusted_at' => now(),
            ];
            
            // Only set fee_adjusted_by if it's a doctor (since it's constrained to doctors table)
            if ($adjustedByType === 'doctor') {
                $updateData['fee_adjusted_by'] = $adjustedBy->id;
            } else {
                // For admin adjustments, set to null since the column is constrained to doctors
                $updateData['fee_adjusted_by'] = null;
            }
            
            // Update base_fee if it was null
            if ($bookingPatient->base_fee === null) {
                $updateData['base_fee'] = $newFee;
            }
            
            $bookingPatient->update($updateData);

            // Ensure invoice exists (create if it doesn't exist yet)
            $invoice = $booking->invoice;
            if (!$invoice) {
                $invoice = $this->createInvoice($booking);
            }

            // Find or create invoice item
            $invoiceItem = InvoiceItem::where('invoice_id', $invoice->id)
                ->where('patient_id', $patientId)
                ->first();

            if ($invoiceItem) {
                // Update existing item
                $oldUnitPrice = $invoiceItem->unit_price ?? 0;
                $adjustment = $newFee - $oldUnitPrice;
                $invoiceItem->update([
                    'unit_price' => $bookingPatient->base_fee ?? $newFee,
                    'adjustment' => $adjustment,
                    'adjustment_reason' => $reason,
                    'total_price' => $newFee,
                ]);
            } else {
                // Create new invoice item
                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'patient_id' => $patientId,
                    'consultation_id' => $bookingPatient->consultation_id,
                    'description' => "Consultation for {$bookingPatient->patient->name} ({$bookingPatient->patient->age} yrs, {$bookingPatient->patient->gender})",
                    'quantity' => 1,
                    'unit_price' => $bookingPatient->base_fee ?? $newFee,
                    'adjustment' => 0.00,
                    'total_price' => $newFee,
                    'item_type' => 'consultation',
                    'order_index' => $bookingPatient->order_index ?? 0,
                ]);
            }

            // Recalculate invoice totals
            $oldTotal = $invoice->total_amount ?? 0;
            $invoice->recalculate();

            // Recalculate booking totals
            $booking->total_adjusted_amount = $invoice->total_amount;
            $booking->total_amount = $invoice->total_amount;
            $booking->save();

            // Log the adjustment
            $log = FeeAdjustmentLog::create([
                'booking_id' => $booking->id,
                'patient_id' => $patientId,
                'invoice_item_id' => $invoiceItem->id ?? null,
                'adjusted_by_type' => $adjustedByType,
                'adjusted_by_id' => $adjustedBy->id,
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
        
        // Ensure booking has doctor relationship loaded
        if (!$booking->relationLoaded('doctor')) {
            $booking->load('doctor');
        }

        try {
            // Notify payer
            if (class_exists(FeeAdjustmentNotification::class)) {
                Mail::to($booking->payer_email)->send(
                    new FeeAdjustmentNotification($booking, $patient, $oldFee, $newFee, $reason)
                );
            }

            // Notify accountant/admin
            $accountantEmail = config('app.accountant_email') ?? config('mail.from.address');
            if ($accountantEmail && class_exists(FeeAdjustmentAdminNotification::class)) {
                Mail::to($accountantEmail)->send(
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

    /**
     * Send confirmation emails for multi-patient booking
     */
    private function sendMultiPatientBookingEmails(Booking $booking): void
    {
        try {
            // Send confirmation email to payer
            // Aggregate problems for payer summary
            $allProblems = $booking->bookingPatients->map(function($bp) {
                return $bp->consultation->first_name . ': ' . $bp->consultation->problem;
            })->implode('; ');

            $allEmergencySymptoms = $booking->bookingPatients->flatMap(function($bp) {
                return $bp->consultation->emergency_symptoms ?? [];
            })->unique()->values()->all();

            $hasAnyDocs = $booking->bookingPatients->some(function($bp) {
                return !empty($bp->consultation->medical_documents);
            });
            $totalDocCount = $booking->bookingPatients->sum(function($bp) {
                return is_array($bp->consultation->medical_documents) ? count($bp->consultation->medical_documents) : 0;
            });

            $payerData = [
                'consultation_reference' => $booking->reference,
                'first_name' => explode(' ', $booking->payer_name)[0] ?? $booking->payer_name,
                'last_name' => implode(' ', array_slice(explode(' ', $booking->payer_name), 1)) ?? '',
                'email' => $booking->payer_email,
                'mobile' => $booking->payer_mobile,
                'age' => 'N/A', // Payer is not necessarily a patient
                'gender' => 'N/A', // Payer is not necessarily a patient
                'problem' => Str::limit($allProblems, 200),
                'emergency_symptoms' => $allEmergencySymptoms,
                'severity' => 'moderate',
                'consult_mode' => $booking->consult_mode,
                'has_documents' => $hasAnyDocs,
                'documents_count' => $totalDocCount,
                'doctor_fee' => $booking->total_adjusted_amount ?? 0,
            ];

            // Send confirmation email to payer
            Mail::to($booking->payer_email)->send(
                new ConsultationConfirmation($payerData)
            );

            // Send SMS confirmation to payer
            try {
                $smsNotification = new ConsultationSmsNotification();
                $smsResult = $smsNotification->sendConsultationConfirmation($payerData);
                
                if ($smsResult['success']) {
                    \Log::info('Payer confirmation SMS sent successfully', [
                        'booking_reference' => $booking->reference,
                        'payer_mobile' => $booking->payer_mobile
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send payer confirmation SMS: ' . $e->getMessage(), [
                    'booking_reference' => $booking->reference,
                    'payer_mobile' => $booking->payer_mobile ?? 'N/A'
                ]);
            }

            // Send confirmation email to each patient (if they have different email)
            foreach ($booking->bookingPatients as $bp) {
                $consultation = $bp->consultation;
                $patient = $bp->patient;
                
                // Only send if patient has a different email than payer
                if ($consultation->email && $consultation->email !== $booking->payer_email) {
                    $patientData = [
                        'consultation_reference' => $consultation->reference,
                        'first_name' => $consultation->first_name,
                        'last_name' => $consultation->last_name,
                        'email' => $consultation->email,
                        'mobile' => $consultation->mobile ?? $booking->payer_mobile,
                        'age' => $consultation->age ?? $patient->age ?? 'N/A',
                        'gender' => $consultation->gender ?? $patient->gender ?? 'N/A',
                        'problem' => $consultation->problem,
                        'severity' => $consultation->severity ?? 'moderate',
                        'emergency_symptoms' => $consultation->emergency_symptoms ?? null,
                        'consult_mode' => $consultation->consult_mode,
                        'has_documents' => !empty($consultation->medical_documents),
                        'documents_count' => is_array($consultation->medical_documents) ? count($consultation->medical_documents) : 0,
                        'doctor_fee' => $bp->adjusted_fee ?? $bp->base_fee ?? 0,
                    ];

                    Mail::to($consultation->email)->send(
                        new ConsultationConfirmation($patientData)
                    );

                    // Send SMS confirmation to patient (if they have different phone)
                    if ($consultation->mobile && $consultation->mobile !== $booking->payer_mobile) {
                        try {
                            $smsNotification = new ConsultationSmsNotification();
                            $smsResult = $smsNotification->sendConsultationConfirmation($patientData);
                            
                            if ($smsResult['success']) {
                                \Log::info('Patient confirmation SMS sent successfully', [
                                    'consultation_reference' => $consultation->reference,
                                    'patient_mobile' => $consultation->mobile
                                ]);
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Failed to send patient confirmation SMS: ' . $e->getMessage(), [
                                'consultation_reference' => $consultation->reference,
                                'patient_mobile' => $consultation->mobile ?? 'N/A'
                            ]);
                        }
                    }
                }
            }

            // Send admin alert
            $adminEmail = config('mail.admin_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(
                    new ConsultationAdminAlert($payerData)
                );
            }

            // Send notification to assigned doctor (email + SMS + WhatsApp)
            if ($booking->doctor) {
                foreach ($booking->bookingPatients as $bp) {
                    $consultation = $bp->consultation;
                    $patient = $bp->patient;

                    $patientNotificationData = [
                        'consultation_reference' => $consultation->reference,
                        'first_name' => $consultation->first_name,
                        'last_name' => $consultation->last_name,
                        'email' => $consultation->email,
                        'mobile' => $consultation->mobile ?? $booking->payer_mobile,
                        'age' => $consultation->age ?? $patient->age ?? 'N/A',
                        'gender' => $consultation->gender ?? $patient->gender ?? 'N/A',
                        'problem' => $consultation->problem,
                        'severity' => $consultation->severity ?? 'moderate',
                        'emergency_symptoms' => $consultation->emergency_symptoms ?? null,
                        'consult_mode' => $consultation->consult_mode,
                        'has_documents' => !empty($consultation->medical_documents),
                        'documents_count' => is_array($consultation->medical_documents) ? count($consultation->medical_documents) : 0,
                        'doctor_fee' => $bp->adjusted_fee ?? $bp->base_fee ?? 0,
                        'doctor' => $booking->doctor->name,
                    ];

                    // Email notification
                    if ($booking->doctor->email) {
                        Mail::to($booking->doctor->email)->send(
                            new ConsultationDoctorNotification($patientNotificationData)
                        );
                    }

                    // SMS notification to doctor
                    try {
                        $smsNotification = new ConsultationSmsNotification();
                        $smsResult = $smsNotification->sendDoctorNewConsultation($booking->doctor, $patientNotificationData);
                        
                        if ($smsResult['success']) {
                            \Log::info('Doctor notification SMS sent successfully', [
                                'booking_reference' => $booking->reference,
                                'consultation_reference' => $consultation->reference,
                                'doctor_id' => $booking->doctor->id,
                                'doctor_phone' => $booking->doctor->phone
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to send doctor notification SMS: ' . $e->getMessage(), [
                            'booking_reference' => $booking->reference,
                            'consultation_reference' => $consultation->reference,
                            'doctor_id' => $booking->doctor->id
                        ]);
                    }

                    // WhatsApp notification to doctor (if enabled)
                    if (config('services.termii.whatsapp_enabled') && $booking->doctor->phone) {
                        try {
                            $whatsapp = new \App\Notifications\ConsultationWhatsAppNotification();
                            $doctorResult = $whatsapp->sendDoctorNewConsultationTemplate(
                                $booking->doctor,
                                $patientNotificationData,
                                'doctor_new_consultation'
                            );
                            
                            if ($doctorResult['success']) {
                                \Log::info('Doctor WhatsApp notification sent successfully', [
                                    'booking_reference' => $booking->reference,
                                    'consultation_reference' => $consultation->reference,
                                    'doctor_id' => $booking->doctor->id,
                                    'doctor_phone' => $booking->doctor->phone
                                ]);
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Failed to send doctor WhatsApp notification: ' . $e->getMessage(), [
                                'booking_reference' => $booking->reference,
                                'consultation_reference' => $consultation->reference,
                                'doctor_id' => $booking->doctor->id
                            ]);
                        }
                    }
                }
            }

            // Send WhatsApp notification to payer (if enabled)
            if (config('services.termii.whatsapp_enabled') && $booking->payer_mobile) {
                try {
                    $whatsapp = new \App\Notifications\ConsultationWhatsAppNotification();
                    $payerResult = $whatsapp->sendConsultationConfirmationTemplate(
                        $payerData,
                        'patient_booking_confirmation'
                    );
                    
                    if ($payerResult['success']) {
                        \Log::info('Payer WhatsApp notification sent successfully', [
                            'booking_reference' => $booking->reference,
                            'payer_phone' => $booking->payer_mobile
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to send payer WhatsApp notification: ' . $e->getMessage(), [
                        'booking_reference' => $booking->reference,
                        'payer_phone' => $booking->payer_mobile ?? 'N/A'
                    ]);
                }
            }

            // Send WhatsApp notification to each patient (if they have different phone)
            foreach ($booking->bookingPatients as $bp) {
                $consultation = $bp->consultation;
                
                if ($consultation->mobile && 
                    $consultation->mobile !== $booking->payer_mobile && 
                    config('services.termii.whatsapp_enabled')) {
                    try {
                        $patientData = [
                            'consultation_reference' => $consultation->reference,
                            'first_name' => $consultation->first_name,
                            'last_name' => $consultation->last_name,
                            'email' => $consultation->email ?? $booking->payer_email,
                            'mobile' => $consultation->mobile,
                            'problem' => $consultation->problem ?? 'General consultation',
                            'severity' => $consultation->severity ?? 'moderate',
                            'consult_mode' => $consultation->consult_mode,
                            'has_documents' => false,
                            'documents_count' => 0,
                        ];

                        $whatsapp = new \App\Notifications\ConsultationWhatsAppNotification();
                        $patientResult = $whatsapp->sendConsultationConfirmationTemplate(
                            $patientData,
                            'patient_booking_confirmation'
                        );
                        
                        if ($patientResult['success']) {
                            \Log::info('Patient WhatsApp notification sent successfully', [
                                'consultation_reference' => $consultation->reference,
                                'patient_phone' => $consultation->mobile
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to send patient WhatsApp notification: ' . $e->getMessage(), [
                            'consultation_reference' => $consultation->reference,
                            'patient_phone' => $consultation->mobile ?? 'N/A'
                        ]);
                    }
                }
            }

            \Log::info('Multi-patient booking notifications queued successfully', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->reference,
                'payer_email' => $booking->payer_email,
                'patients_count' => $booking->bookingPatients->count(),
                'emails_sent' => true,
                'sms_sent' => true,
                'whatsapp_sent' => config('services.termii.whatsapp_enabled', false)
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send multi-patient booking emails', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - email failures shouldn't break booking creation
        }
    }

    /**
     * Apply multi-patient pricing rules to a booking
     * Pricing structure:
     * - Parent/Guardian: Base fee
     * - First child: Base fee
     * - Additional children: Base fee + (Base fee * additional_percentage / 100)
     * 
     * @param Booking $booking
     * @return array Array of calculated fees keyed by patient_id
     */
    public function calculateMultiPatientFees(Booking $booking): array
    {
        $baseFee = Setting::get('multi_patient_booking_fee', 4000);
        $additionalPercentage = Setting::get('additional_child_discount_percentage', 60);
        
        // Load booking patients with their relationships
        $bookingPatients = $booking->bookingPatients()
            ->with('patient')
            ->orderBy('order_index')
            ->get();
        
        if ($bookingPatients->isEmpty()) {
            return [];
        }
        
        $calculatedFees = [];
        $children = [];
        $parent = null;
        
        // Separate parent/guardian from children
        foreach ($bookingPatients as $bp) {
            $relationship = strtolower($bp->relationship_to_payer ?? 'self');
            if ($relationship === 'self') {
                $parent = $bp;
            } else {
                $children[] = $bp;
            }
        }
        
        // If no parent found, treat first patient as parent
        if (!$parent && !empty($bookingPatients)) {
            $parent = $bookingPatients->first();
            $children = $bookingPatients->slice(1)->values()->all();
        }
        
        // Apply base fee to parent
        if ($parent) {
            $calculatedFees[$parent->patient_id] = [
                'base_fee' => $baseFee,
                'adjusted_fee' => $baseFee,
                'reason' => 'Base fee for parent/guardian'
            ];
        }
        
        // Apply fees to children
        foreach ($children as $index => $child) {
            if ($index === 0) {
                // First child gets base fee
                $calculatedFees[$child->patient_id] = [
                    'base_fee' => $baseFee,
                    'adjusted_fee' => $baseFee,
                    'reason' => 'Base fee for first child'
                ];
            } else {
                // Additional children get base fee + additional percentage
                $additionalAmount = $baseFee * ($additionalPercentage / 100);
                $totalFee = $baseFee + $additionalAmount;
                $calculatedFees[$child->patient_id] = [
                    'base_fee' => $baseFee,
                    'adjusted_fee' => round($totalFee, 2),
                    'reason' => "Additional child fee (base fee + {$additionalPercentage}% additional charge)"
                ];
            }
        }
        
        return $calculatedFees;
    }

    /**
     * Apply calculated fees to a booking
     * 
     * @param Booking $booking
     * @param array $calculatedFees Fees calculated by calculateMultiPatientFees()
     * @return bool
     */
    public function applyMultiPatientFees(Booking $booking, array $calculatedFees): bool
    {
        DB::beginTransaction();
        try {
            foreach ($calculatedFees as $patientId => $feeData) {
                $bookingPatient = BookingPatient::where('booking_id', $booking->id)
                    ->where('patient_id', $patientId)
                    ->first();
                
                if ($bookingPatient) {
                    $bookingPatient->update([
                        'base_fee' => $feeData['base_fee'],
                        'adjusted_fee' => $feeData['adjusted_fee'],
                        'fee_adjustment_reason' => $feeData['reason'] . ' (Auto-calculated based on pricing rules)',
                        'fee_adjusted_at' => now(),
                    ]);
                }
            }
            
            // Recalculate booking totals
            $totalAmount = $booking->bookingPatients()
                ->whereNotNull('adjusted_fee')
                ->sum('adjusted_fee');
            
            $booking->total_amount = $totalAmount;
            $booking->total_adjusted_amount = $totalAmount;
            $booking->save();
            
            // Create or update invoice
            $invoice = $booking->invoice;
            if (!$invoice) {
                $invoice = $this->createInvoice($booking);
            } else {
                // Update existing invoice
                $invoice->recalculate();
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to apply multi-patient fees', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

