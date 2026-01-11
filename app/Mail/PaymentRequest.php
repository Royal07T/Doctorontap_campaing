<?php

namespace App\Mail;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests
 * Email will be sent asynchronously via queue, improving response times
 */
class PaymentRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $consultation;
    public $paymentUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
        
        // Generate payment URL with consultation reference
        $this->paymentUrl = route('payment.request', ['reference' => $consultation->reference]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Request - Your Consultation with ' . $this->consultation->doctor->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-request',
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
