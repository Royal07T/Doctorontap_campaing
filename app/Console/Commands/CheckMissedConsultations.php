<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DoctorPenaltyService;

/**
 * Check for missed consultations and apply penalties
 * 
 * This command should be run periodically (e.g., every hour) via Laravel scheduler
 * to check all doctors for missed consultations and apply automatic penalties.
 * 
 * Usage: php artisan consultations:check-missed
 */
class CheckMissedConsultations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consultations:check-missed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all doctors for missed consultations and apply automatic penalties';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for missed consultations...');

        $penaltyService = app(DoctorPenaltyService::class);
        $summary = $penaltyService->checkAllDoctors();

        $this->info("Checked {$summary['doctors_checked']} doctors");
        $this->info("Found {$summary['doctors_with_missed']} doctors with missed consultations");
        $this->info("Applied {$summary['penalties_applied']} penalties");

        if (!empty($summary['errors'])) {
            $this->warn('Errors encountered:');
            foreach ($summary['errors'] as $error) {
                $this->error("Doctor ID {$error['doctor_id']}: {$error['error']}");
            }
        }

        return Command::SUCCESS;
    }
}
