<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConsultationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Consultation Request Confirmation - DoctorOnTap',
            replyTo: env('ADMIN_EMAIL', 'inquiries@doctorontap.com.ng'),
            from: env('MAIL_FROM_ADDRESS', 'hello@doctorontap.com.ng'),
            tags: ['consultation', 'confirmation'],
            metadata: [
                'consultation_reference' => $this->data['consultation_reference'] ?? '',
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.consultation-confirmation',
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
