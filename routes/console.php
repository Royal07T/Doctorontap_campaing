<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Consultation;
use App\Models\Patient;
use App\Jobs\WeeklyReportJob;
use App\Jobs\MissedMedicationAlertJob;
use App\Jobs\DailyFamilySummaryJob;
use App\Jobs\LeadFollowUpJob;
use App\Jobs\EscalationAlertJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('patients:backfill {--dry-run}', function () {
    $dryRun = $this->option('dry-run');
    $created = 0;
    $updated = 0;
    $skipped = 0;

    Consultation::chunk(500, function ($consultations) use (&$created, &$updated, &$skipped, $dryRun) {
        foreach ($consultations as $c) {
            if (empty($c->email)) {
                $skipped++;
                continue;
            }

            $attributes = [
                'name' => trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')) ?: ($c->email ?? 'Unknown'),
                'phone' => $c->mobile ?? '',
                'gender' => $c->gender ?? 'other',
            ];

            $existing = Patient::where('email', $c->email)->first();
            if ($existing) {
                // Determine if an update is needed
                $needsUpdate = (
                    ($attributes['name'] && $attributes['name'] !== $existing->name) ||
                    ($attributes['phone'] && $attributes['phone'] !== $existing->phone) ||
                    ($attributes['gender'] && $attributes['gender'] !== $existing->gender)
                );

                if ($needsUpdate) {
                    if (!$dryRun) {
                        $existing->update($attributes);
                    }
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                if (!$dryRun) {
                    Patient::create(array_merge($attributes, [
                        'email' => $c->email,
                    ]));
                }
                $created++;
            }
        }
    });

    $this->info("Backfill complete. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}" . ($dryRun ? ' (dry-run)' : ''));
})->purpose('Backfill patients table from existing consultations');

/*
|--------------------------------------------------------------------------
| Caregiver Module Scheduled Jobs
|--------------------------------------------------------------------------
*/

// Weekly health reports — every Sunday at 08:00
Schedule::job(new WeeklyReportJob)->weeklyOn(0, '08:00')
    ->withoutOverlapping()
    ->onOneServer();

// Missed medication alerts — every hour
Schedule::job(new MissedMedicationAlertJob)->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// Daily family summary — every day at 20:00
Schedule::job(new DailyFamilySummaryJob)->dailyAt('20:00')
    ->withoutOverlapping()
    ->onOneServer();

// Lead follow-up processing — every day at 09:00
Schedule::job(new LeadFollowUpJob)->dailyAt('09:00')
    ->withoutOverlapping()
    ->onOneServer();
