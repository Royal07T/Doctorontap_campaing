<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests */
class ReferralNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;
    public $recipientType; // 'patient' or 'doctor'
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data, string $recipientType)
    {
        $this->data = $data;
        $this->recipientType = $recipientType;
        
        // Prepare template data with comprehensive recipient information
        $templateData = [
            // Recipient Information
            'name' => $data['name'] ?? ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'full_name' => $data['name'] ?? ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
            'email' => $data['email'] ?? '',
            'phone' => $data['mobile'] ?? $data['phone'] ?? '',
            'mobile' => $data['mobile'] ?? $data['phone'] ?? '',
            'age' => isset($data['age']) ? (string)$data['age'] : '',
            'gender' => $data['gender'] ?? '',
            
            // Consultation Information
            'reference' => $data['original_consultation_reference'] ?? $data['reference'] ?? '',
            'referring_doctor' => $data['referring_doctor_name'] ?? '',
            'referred_to_doctor' => $data['referred_to_doctor_name'] ?? '',
            'reason' => $data['reason'] ?? 'Consultation referral',
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('ReferralNotification', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $subject = ($this->recipientType === 'patient')
                ? "Your Consultation Has Been Referred - DoctorOnTap"
                : "New Patient Referral - DoctorOnTap";
            $this->templateSubject = $subject;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->templateSubject,
            replyTo: config('mail.admin_email'),
            from: config('mail.from.address'),
            tags: ['consultation', 'referral', $this->recipientType],
            metadata: [
                'consultation_reference' => $this->data['original_consultation_reference'] ?? '',
                'new_consultation_reference' => $this->data['new_consultation_reference'] ?? '',
                'recipient_type' => $this->recipientType,
            ],
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

        $view = ($this->recipientType === 'patient')
            ? 'emails.referral-patient'
            : 'emails.referral-doctor';

        return new Content(
            view: $view,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

