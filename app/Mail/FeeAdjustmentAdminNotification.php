<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Patient;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests */
class FeeAdjustmentAdminNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $booking;
    public $patient;
    public $oldFee;
    public $newFee;
    public $reason;
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, Patient $patient, $oldFee, $newFee, $reason)
    {
        $this->booking = $booking;
        $this->patient = $patient;
        $this->oldFee = $oldFee;
        $this->newFee = $newFee;
        $this->reason = $reason;
        
        // Load doctor relationship if it exists
        $doctor = $this->booking->doctor;
        
        // Prepare template data
        $templateData = [
            'reference' => $booking->reference ?? '',
            'old_amount' => number_format($oldFee, 2),
            'new_amount' => number_format($newFee, 2),
            'patient_name' => ($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''),
            'adjusted_by' => $doctor->name ?? 'System',
            'reason' => $reason ?? 'Fee adjustment',
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('FeeAdjustmentAdminNotification', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = '[AUDIT] Fee Adjustment - ' . $this->booking->reference;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->templateSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // If template content is available, use it; otherwise fallback to view
        if ($this->templateContent) {
            return new Content(
                htmlString: $this->templateContent,
            );
        }

        $difference = $this->newFee - $this->oldFee;
        $doctor = $this->booking->doctor;

        return new Content(
            view: 'emails.fee-adjustment-admin-notification',
            with: [
                'booking' => $this->booking,
                'patient' => $this->patient,
                'oldFee' => number_format($this->oldFee, 2),
                'newFee' => number_format($this->newFee, 2),
                'difference' => number_format($difference, 2),
                'reason' => $this->reason,
                'doctor' => $doctor,
            ]
        );
    }
}

