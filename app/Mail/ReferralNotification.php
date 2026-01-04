<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReferralNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $recipientType; // 'patient' or 'doctor'

    /**
     * Create a new message instance.
     */
    public function __construct(array $data, string $recipientType)
    {
        $this->data = $data;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = ($this->recipientType === 'patient')
            ? "Your Consultation Has Been Referred - DoctorOnTap"
            : "New Patient Referral - DoctorOnTap";

        return new Envelope(
            subject: $subject,
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

