<?php

namespace Database\Seeders;

use App\Models\FamilyMember;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class FamilyMemberSeeder extends Seeder
{
    /**
     * Seed the family_members table with a demo account.
     */
    public function run(): void
    {
        // Get the first patient or skip if none exist
        $patient = Patient::first();

        if (!$patient) {
            $this->command->warn('No patients found. Skipping FamilyMemberSeeder.');
            return;
        }

        FamilyMember::updateOrCreate(
            ['email' => 'family@doctorontap.com'],
            [
                'name' => 'Demo Family Member',
                'password' => bcrypt('Family123!'),
                'phone' => '+2348012345678',
                'patient_id' => $patient->id,
                'relationship' => 'spouse',
                'is_active' => true,
            ]
        );

        $this->command->info('Family member seeded: family@doctorontap.com / Family123!');
    }
}
