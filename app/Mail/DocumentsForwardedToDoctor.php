<?php

namespace App\Mail;

use App\Models\Consultation;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

/**
 * OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests
 */
class DocumentsForwardedToDoctor extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(public Consultation $consultation)
    {
        // Count documents
        $documentCount = $this->consultation->medical_documents ? count($this->consultation->medical_documents) : 0;
        
        // Prepare template data with comprehensive recipient information
        $templateData = [
            // Doctor Information
            'doctor_name' => $this->consultation->doctor->name ?? '',
            'doctor_specialization' => $this->consultation->doctor->specialization ?? '',
            
            // Consultation Information
            'reference' => $this->consultation->reference ?? '',
            'document_count' => (string)$documentCount,
            'view_link' => route('doctor.consultations.show', $this->consultation->id),
            
            // Patient Information
            'patient_name' => ($this->consultation->first_name ?? '') . ' ' . ($this->consultation->last_name ?? ''),
            'first_name' => $this->consultation->first_name ?? '',
            'last_name' => $this->consultation->last_name ?? '',
            'full_name' => ($this->consultation->first_name ?? '') . ' ' . ($this->consultation->last_name ?? ''),
            'email' => $this->consultation->email ?? '',
            'phone' => $this->consultation->mobile ?? '',
            'mobile' => $this->consultation->mobile ?? '',
            'age' => isset($this->consultation->age) ? (string)$this->consultation->age : '',
            'gender' => $this->consultation->gender ?? '',
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('DocumentsForwardedToDoctor', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Patient Medical Documents - ' . $this->consultation->full_name . ' (Ref: ' . $this->consultation->reference . ')';
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
            view: 'emails.documents-forwarded-to-doctor',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->consultation->medical_documents && count($this->consultation->medical_documents) > 0) {
            foreach ($this->consultation->medical_documents as $document) {
                $filePath = storage_path('app/public/' . $document['path']);
                
                if (file_exists($filePath)) {
                    $attachments[] = Attachment::fromPath($filePath)
                        ->as($document['original_name'])
                        ->withMime($document['mime_type']);
                }
            }
        }
        
        return $attachments;
    }
}
