<?php

namespace App\Notifications;

use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * Email the weekly health‑summary PDF to the patient
 * (and their emergency / family contact if available).
 */
class WeeklyHealthReport extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string  $pdfPath,
        protected Patient $patient,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->patient->name;

        return (new MailMessage)
            ->subject("Weekly Health Report – {$name}")
            ->greeting("Hello,")
            ->line("Please find attached the weekly health summary for **{$name}** covering the last 7 days.")
            ->line('This report includes vital sign trends, mood observations, medication compliance and any flagged readings.')
            ->line('If you have any concerns, please contact your care team immediately.')
            ->salutation('— DoctorOnTap Care Team')
            ->attach(Storage::disk('local')->path($this->pdfPath), [
                'as'   => "weekly-report-{$this->patient->id}.pdf",
                'mime' => 'application/pdf',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'weekly_health_report',
            'patient_id' => $this->patient->id,
            'pdf_path'   => $this->pdfPath,
        ];
    }
}
