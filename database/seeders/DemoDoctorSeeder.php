<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;

class DemoDoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Doctor::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'email' => 'doctor@demo.com',
            'password' => Hash::make('password'),
            'phone' => '0801 234 5678',
            'gender' => 'male',
            'specialization' => 'General Practice',
            'experience' => '5 years',
            'location' => 'Lagos',
            'place_of_work' => 'Demo Medical Center',
            'role' => 'clinical',
            'languages' => 'English, Yoruba',
            'min_consultation_fee' => 3000,
            'max_consultation_fee' => 5000,
            'consultation_fee' => 4000,
            'use_default_fee' => true,
            'mdcn_license_current' => true,
            'certificate_path' => null, // No certificate for demo
            'is_available' => true,
            'is_approved' => true,
            'approved_by' => 1, // Assuming admin user ID 1 exists
            'approved_at' => now(),
            'email_verified_at' => now(), // Already verified
            'order' => 0,
        ]);

        $this->command->info('✅ Demo doctor account created successfully!');
        $this->command->info('');
        $this->command->info('📧 Email: doctor@demo.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('');
        $this->command->info('🔗 Login at: /doctor/login');
    }
}
