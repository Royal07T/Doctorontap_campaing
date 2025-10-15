<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nurse;
use Illuminate\Support\Facades\Hash;

class DemoNurseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Nurse::create([
            'name' => 'Demo Nurse',
            'email' => 'nurse@demo.com',
            'password' => Hash::make('password'),
            'phone' => '0803 456 7890',
            'is_active' => true,
            'created_by' => 1, // Assuming admin user ID 1 exists
            'email_verified_at' => now(), // Auto-verified
            'last_login_at' => null,
        ]);

        $this->command->info('✅ Demo nurse account created successfully!');
        $this->command->info('');
        $this->command->info('📧 Email: nurse@demo.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('');
        $this->command->info('🔗 Login at: /nurse/login');
    }
}
