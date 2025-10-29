<?php

namespace App\Mail;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class TreatmentPlanNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(public Consultation $consultation)
    {
        //
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Generate PDF for the treatment plan
        $pdf = Pdf::loadView('pdfs.treatment-plan', [
            'consultation' => $this->consultation
        ]);
        
        $this->pdfContent = $pdf->output();
        
        return $this;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Treatment Plan is Ready - ' . $this->consultation->reference,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.treatment-plan-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Generate PDF if not already generated
        if (!isset($this->pdfContent)) {
            $pdf = Pdf::loadView('pdfs.treatment-plan', [
                'consultation' => $this->consultation
            ]);
            $this->pdfContent = $pdf->output();
        }
        
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'treatment-plan-' . $this->consultation->reference . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}