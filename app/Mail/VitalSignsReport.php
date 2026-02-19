<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
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
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct($patient, $vitalSign, $nurse, $pdfContent)
    {
        $this->patient = $patient;
        $this->vitalSign = $vitalSign;
        $this->nurse = $nurse;
        $this->pdfContent = $pdfContent;
        
        // Prepare template data with comprehensive recipient information
        $templateData = [
            // Recipient Information
            'first_name' => $patient->first_name ?? '',
            'last_name' => $patient->last_name ?? '',
            'full_name' => ($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''),
            'email' => $patient->email ?? '',
            'phone' => $patient->phone ?? '',
            'mobile' => $patient->phone ?? '',
            'age' => isset($patient->age) ? (string)$patient->age : '',
            'gender' => $patient->gender ?? '',
            
            // Report Information
            'report_date' => $vitalSign->created_at ? $vitalSign->created_at->format('Y-m-d') : now()->format('Y-m-d'),
            'report_datetime' => $vitalSign->created_at ? $vitalSign->created_at->format('F d, Y h:i A') : now()->format('F d, Y h:i A'),
            'vital_signs_summary' => $this->formatVitalSignsSummary($vitalSign),
            'view_link' => route('patient.vital-signs.show', $vitalSign->id ?? ''),
            
            // Nurse Information
            'nurse_name' => $nurse->name ?? '',
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('VitalSignsReport', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Your Vital Signs Report from DoctorOnTap';
        }
    }

    /**
     * Format vital signs summary for template
     */
    private function formatVitalSignsSummary($vitalSign): string
    {
        $summary = [];
        if (isset($vitalSign->blood_pressure)) $summary[] = "Blood Pressure: {$vitalSign->blood_pressure}";
        if (isset($vitalSign->heart_rate)) $summary[] = "Heart Rate: {$vitalSign->heart_rate} bpm";
        if (isset($vitalSign->temperature)) $summary[] = "Temperature: {$vitalSign->temperature}°C";
        if (isset($vitalSign->oxygen_saturation)) $summary[] = "Oxygen Saturation: {$vitalSign->oxygen_saturation}%";
        
        return implode(', ', $summary) ?: 'Vital signs recorded';
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
