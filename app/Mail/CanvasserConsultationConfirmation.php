<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CanvasserConsultationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $canvasser;

    /**
     * Create a new message instance.
     */
    public function __construct($data, $canvasser)
    {
        $this->data = $data;
        $this->canvasser = $canvasser;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Consultation Booked for You - DoctorOnTap',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.canvasser-consultation-confirmation',
        );
    }

    /**
     * Get the attachments for the array.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
