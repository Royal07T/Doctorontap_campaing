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
class SecurityAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $eventType;
    public $data;
    public $severity;
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(string $eventType, array $data, string $severity)
    {
        $this->eventType = $eventType;
        $this->data = $data;
        $this->severity = $severity;
        
        // Prepare template data
        $templateData = [
            'event_type' => ucwords(str_replace('_', ' ', $eventType)),
            'severity' => ucfirst($severity),
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'details' => json_encode($data, JSON_PRETTY_PRINT),
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('SecurityAlert', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $severityLabels = [
                'critical' => '🚨 CRITICAL',
                'high' => '⚠️ HIGH',
                'medium' => '⚡ MEDIUM',
                'low' => 'ℹ️ LOW',
            ];
            $severityLabel = $severityLabels[$severity] ?? strtoupper($severity);
            $eventTypeLabel = ucwords(str_replace('_', ' ', $eventType));
            $this->templateSubject = "{$severityLabel} Security Alert: {$eventTypeLabel} - DoctorOnTap";
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->templateSubject,
            tags: ['security', 'alert', $this->severity],
            metadata: [
                'event_type' => $this->eventType,
                'severity' => $this->severity,
            ],
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
            view: 'emails.security-alert',
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
