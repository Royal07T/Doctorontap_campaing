<?php

namespace App\Mail;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests */
class ConsultationStatusChange extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $consultation;
    public $doctor;
    public $oldStatus;
    public $newStatus;
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(Consultation $consultation, Doctor $doctor, $oldStatus, $newStatus)
    {
        $this->consultation = $consultation;
        $this->doctor = $doctor;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        
        // Prepare template data with comprehensive recipient information
        $templateData = [
            // Consultation Information
            'reference' => $consultation->reference ?? '',
            'old_status' => $oldStatus ?? '',
            'new_status' => $newStatus ?? '',
            
            // Patient Information
            'patient_name' => ($consultation->first_name ?? '') . ' ' . ($consultation->last_name ?? ''),
            'first_name' => $consultation->first_name ?? '',
            'last_name' => $consultation->last_name ?? '',
            'full_name' => ($consultation->first_name ?? '') . ' ' . ($consultation->last_name ?? ''),
            'email' => $consultation->email ?? '',
            'phone' => $consultation->mobile ?? '',
            'mobile' => $consultation->mobile ?? '',
            'age' => isset($consultation->age) ? (string)$consultation->age : '',
            'gender' => $consultation->gender ?? '',
            
            // Change Information
            'changed_by' => $doctor->name ?? 'System',
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('ConsultationStatusChange', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Consultation Status Updated - ' . $this->consultation->reference;
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
