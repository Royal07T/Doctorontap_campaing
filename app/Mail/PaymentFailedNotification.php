<?php

namespace App\Mail;

use App\Models\Consultation;
use App\Models\Payment;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests */
class PaymentFailedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $consultation;
    public $payment;
    public $failureReason;
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(Consultation $consultation, Payment $payment, string $failureReason = '')
    {
        $this->consultation = $consultation;
        $this->payment = $payment;
        $this->failureReason = $failureReason;
        
        // Prepare template data with comprehensive recipient information
        $templateData = [
            // Recipient Information
            'first_name' => $consultation->first_name ?? '',
            'last_name' => $consultation->last_name ?? '',
            'full_name' => ($consultation->first_name ?? '') . ' ' . ($consultation->last_name ?? ''),
            'email' => $consultation->email ?? '',
            'phone' => $consultation->mobile ?? '',
            'mobile' => $consultation->mobile ?? '',
            'age' => isset($consultation->age) ? (string)$consultation->age : '',
            'gender' => $consultation->gender ?? '',
            
            // Payment Information
            'reference' => $consultation->reference ?? '',
            'amount' => number_format($payment->amount ?? 0, 2),
            'failure_reason' => $failureReason ?: 'Payment processing failed',
            'retry_link' => route('payment.request', ['reference' => $consultation->reference]),
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('PaymentFailedNotification', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Payment Unsuccessful - DoctorOnTap';
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
            view: 'emails.payment-failed',
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

