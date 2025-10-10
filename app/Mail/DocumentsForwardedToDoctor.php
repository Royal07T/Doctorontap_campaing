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

class DocumentsForwardedToDoctor extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Consultation $consultation)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Patient Medical Documents - ' . $this->consultation->full_name . ' (Ref: ' . $this->consultation->reference . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
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
