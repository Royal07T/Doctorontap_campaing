<?php

namespace App\Notifications;

use App\Models\MedicationLog;
use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Alert sent when a scheduled medication has been missed.
 */
class MissedMedicationAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Patient $patient,
        protected int     $missedCount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠ Missed Medication – {$this->patient->name}")
            ->greeting('Medication Alert')
            ->line("{$this->patient->name} has **{$this->missedCount}** missed medication dose(s) that were not administered on time.")
            ->line('Please review the medication schedule and ensure all doses are accounted for.')
            ->action('View Patient', url("/care-giver/patients/{$this->patient->id}"))
            ->salutation('— DoctorOnTap Alerts');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'missed_medication_alert',
            'patient_id'   => $this->patient->id,
            'missed_count' => $this->missedCount,
        ];
    }
}
