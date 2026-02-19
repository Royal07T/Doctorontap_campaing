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
class CampaignNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $doctor;
    public $campaignDetails;
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct($doctor, $campaignDetails = [])
    {
        $this->doctor = $doctor;
        $this->campaignDetails = $campaignDetails;
        
        // Prepare template data with comprehensive recipient information
        $templateData = [
            // Doctor Information
            'doctor_name' => $doctor->name ?? '',
            'doctor_email' => $doctor->email ?? '',
            'doctor_specialization' => $doctor->specialization ?? '',
            'doctor_phone' => $doctor->phone ?? '',
            
            // Campaign Information
            'campaign_title' => $campaignDetails['title'] ?? 'Campaign',
            'campaign_details' => $campaignDetails['description'] ?? $campaignDetails['details'] ?? '',
            'action_link' => $campaignDetails['action_link'] ?? route('doctor.dashboard'),
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('CampaignNotification', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Campaign Starting Soon - DoctorOnTap';
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
            view: 'emails.campaign-notification',
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
