<?php

namespace Database\Seeders;

use App\Models\CareGiver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCareGiverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $primaryEmail = 'rollingstonny15@gmail.com';
        $primaryPassword = 'password';

        CareGiver::updateOrCreate(
            ['email' => $primaryEmail],
            [
                'name' => 'Rolling Stonny',
                'password' => Hash::make($primaryPassword),
                'phone' => '08000000000',
                'email_verified_at' => now(),
                'is_active' => true,
                'verification_status' => 'verified',
                'role' => 'Caregiver',
                'experience_years' => 2,
                'gender' => 'male',
                'date_of_birth' => '1998-01-01',
            ]
        );

        // Create 10 extra caregiver accounts
        for ($i = 1; $i <= 10; $i++) {
            $email = "caregiver{$i}@demo.com";

            CareGiver::updateOrCreate(
                ['email' => $email],
                [
                    'name' => "Demo Caregiver {$i}",
                    'password' => Hash::make('password'),
                    'phone' => '08000000000',
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'verification_status' => 'verified',
                    'role' => 'Caregiver',
                    'experience_years' => rand(0, 10),
                    'gender' => $i % 2 === 0 ? 'female' : 'male',
                    'date_of_birth' => '1995-01-01',
                ]
            );
        }

        $this->command->info('✅ Demo caregiver accounts created/updated successfully!');
        $this->command->info('');
        $this->command->info("📧 Primary Email: {$primaryEmail}");
        $this->command->info("🔑 Primary Password: {$primaryPassword}");
        $this->command->info('');
        $this->command->info('🔑 Extra demo caregivers: caregiver1@demo.com ... caregiver10@demo.com (password: password)');
        $this->command->info('🔗 Login at: /care-giver/login');
    }
}
