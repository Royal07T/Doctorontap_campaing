<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Consultation;
use App\Models\Doctor;

class ConsultationStatusChange extends Mailable
{
    use Queueable, SerializesModels;

    public $consultation;
    public $doctor;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(Consultation $consultation, Doctor $doctor, $oldStatus, $newStatus)
    {
        $this->consultation = $consultation;
        $this->doctor = $doctor;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Consultation Status Updated - ' . $this->consultation->reference,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.consultation-status-change',
            with: [
                'consultation' => $this->consultation,
                'doctor' => $this->doctor,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
            ]
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
