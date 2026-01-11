<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

/** OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests */
class VitalSignsReport extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $patient;
    public $vitalSign;
    public $nurse;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct($patient, $vitalSign, $nurse, $pdfContent)
    {
        $this->patient = $patient;
        $this->vitalSign = $vitalSign;
        $this->nurse = $nurse;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Vital Signs Report from DoctorOnTap',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.vital-signs-report',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'vital-signs-report.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
