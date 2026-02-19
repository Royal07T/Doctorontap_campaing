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
class CanvasserConsultationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;
    public $canvasser;
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct($data, $canvasser)
    {
        $this->data = $data;
        $this->canvasser = $canvasser;
        
        // Prepare template data with comprehensive recipient information
        $templateData = [
            // Canvasser Information
            'canvasser_name' => $canvasser->name ?? '',
            'canvasser_email' => $canvasser->email ?? '',
            
            // Consultation Information
            'reference' => $data['consultation_reference'] ?? $data['reference'] ?? '',
            'consultation_type' => $data['consultation_type'] ?? 'Consultation',
            'scheduled_date' => $data['scheduled_date'] ?? '',
            'scheduled_datetime' => $data['scheduled_at'] ?? '',
            
            // Patient Information
            'patient_name' => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'full_name' => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
            'email' => $data['email'] ?? '',
            'phone' => $data['mobile'] ?? $data['phone'] ?? '',
            'mobile' => $data['mobile'] ?? $data['phone'] ?? '',
            'age' => isset($data['age']) ? (string)$data['age'] : '',
            'gender' => $data['gender'] ?? '',
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('CanvasserConsultationConfirmation', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Consultation Booked for You - DoctorOnTap';
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
