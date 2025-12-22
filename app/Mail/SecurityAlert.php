<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SecurityAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $eventType;
    public $data;
    public $severity;

    /**
     * Create a new message instance.
     */
    public function __construct(string $eventType, array $data, string $severity)
    {
        $this->eventType = $eventType;
        $this->data = $data;
        $this->severity = $severity;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $severityLabels = [
            'critical' => '🚨 CRITICAL',
            'high' => '⚠️ HIGH',
            'medium' => '⚡ MEDIUM',
            'low' => 'ℹ️ LOW',
        ];

        $severityLabel = $severityLabels[$this->severity] ?? strtoupper($this->severity);
        $eventTypeLabel = ucwords(str_replace('_', ' ', $this->eventType));

        return new Envelope(
            subject: "{$severityLabel} Security Alert: {$eventTypeLabel} - DoctorOnTap",
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
