<?php

namespace App\Mail;

use App\Models\Consultation;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests */
class TreatmentPlanReadyNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(public Consultation $consultation)
    {
        // Eager load the doctor relationship to ensure it's available when sending
        $this->consultation->load('doctor');
        
        // Prepare template data with comprehensive recipient information
        $templateData = [
            // Recipient Information
            'first_name' => $this->consultation->first_name ?? '',
            'last_name' => $this->consultation->last_name ?? '',
            'full_name' => ($this->consultation->first_name ?? '') . ' ' . ($this->consultation->last_name ?? ''),
            'email' => $this->consultation->email ?? '',
            'phone' => $this->consultation->mobile ?? '',
            'mobile' => $this->consultation->mobile ?? '',
            'age' => isset($this->consultation->age) ? (string)$this->consultation->age : '',
            'gender' => $this->consultation->gender ?? '',
            
            // Consultation Information
            'reference' => $this->consultation->reference ?? '',
            'view_link' => route('consultation.show', $this->consultation->id),
            
            // Doctor Information
            'doctor_name' => $this->consultation->doctor->name ?? '',
            'doctor_specialization' => $this->consultation->doctor->specialization ?? '',
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('TreatmentPlanReadyNotification', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Your Treatment Plan is Ready - Payment Required - ' . $this->consultation->reference;
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
            view: 'emails.treatment-plan-ready',
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
