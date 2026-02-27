<?php

namespace App\Notifications;

use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Daily summary sent to the patient's emergency / family contact
 * via email (WhatsApp via VonageService is sent separately in the job).
 */
class DailyFamilySummary extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Patient $patient,
        protected array   $summary,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $s = $this->summary;

        return (new MailMessage)
            ->subject("Daily Update – {$this->patient->name}")
            ->greeting("Hello {$this->patient->emergency_contact_name},")
            ->line("Here is today's summary for **{$this->patient->name}**:")
            ->line("**Vitals recorded:** {$s['vitals_count']}")
            ->line("**Mood:** {$s['latest_mood']}")
            ->line("**Medications given:** {$s['meds_given']} / {$s['meds_total']}")
            ->line("**Critical flags:** {$s['critical_count']}")
            ->when($s['critical_count'] > 0, function (MailMessage $m) {
                return $m->line('⚠ Please contact the care team for details on flagged readings.');
            })
            ->salutation('— DoctorOnTap Care Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'daily_family_summary',
            'patient_id' => $this->patient->id,
            'summary'    => $this->summary,
        ];
    }
}
