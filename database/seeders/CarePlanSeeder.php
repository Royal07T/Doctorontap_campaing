<?php

namespace Database\Seeders;

use App\Models\CarePlan;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class CarePlanSeeder extends Seeder
{
    /**
     * Seed demo care plans for existing patients.
     *
     * Assigns one of each plan type to the first 3 patients,
     * so developers can test feature scoping immediately.
     */
    public function run(): void
    {
        $patients = Patient::orderBy('id')->limit(6)->get();

        if ($patients->isEmpty()) {
            $this->command->warn('⚠️  No patients found. Run patient seeders first.');
            return;
        }

        $plans = [
            [
                'plan_type'  => CarePlan::PLAN_MERIDIAN,
                'status'     => CarePlan::STATUS_ACTIVE,
                'start_date' => now()->subDays(30),
                'expiry_date' => now()->addDays(60),
                'notes'      => 'Meridian plan — basic caregiver monitoring & vitals.',
            ],
            [
                'plan_type'  => CarePlan::PLAN_EXECUTIVE,
                'status'     => CarePlan::STATUS_ACTIVE,
                'start_date' => now()->subDays(15),
                'expiry_date' => now()->addDays(75),
                'notes'      => 'Executive plan — includes physician review & weekly reports.',
            ],
            [
                'plan_type'  => CarePlan::PLAN_SOVEREIGN,
                'status'     => CarePlan::STATUS_ACTIVE,
                'start_date' => now()->subDays(7),
                'expiry_date' => now()->addDays(83),
                'notes'      => 'Sovereign plan — full-spectrum with dietician & physiotherapy.',
            ],
        ];

        $created = 0;

        foreach ($patients as $index => $patient) {
            $planData = $plans[$index % count($plans)];

            CarePlan::updateOrCreate(
                [
                    'patient_id' => $patient->id,
                    'plan_type'  => $planData['plan_type'],
                    'status'     => CarePlan::STATUS_ACTIVE,
                ],
                array_merge($planData, [
                    'patient_id' => $patient->id,
                    'created_by' => null, // No admin reference needed for seeds
                ])
            );

            $created++;
        }

        $this->command->info("✅ {$created} demo care plans created/updated.");
        $this->command->info('   Meridian × ' . $patients->filter(fn ($p, $i) => $i % 3 === 0)->count());
        $this->command->info('   Executive × ' . $patients->filter(fn ($p, $i) => $i % 3 === 1)->count());
        $this->command->info('   Sovereign × ' . $patients->filter(fn ($p, $i) => $i % 3 === 2)->count());
    }
}
