<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Consultation;
use App\Models\Patient;

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
