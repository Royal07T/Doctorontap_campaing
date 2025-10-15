<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Canvasser;
use Illuminate\Support\Facades\Hash;

class DemoCanvasserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Canvasser::create([
            'name' => 'Demo Canvasser',
            'email' => 'canvasser@demo.com',
            'password' => Hash::make('password'),
            'phone' => '0802 345 6789',
            'is_active' => true,
            'created_by' => 1, // Assuming admin user ID 1 exists
            'email_verified_at' => now(), // Auto-verified
            'last_login_at' => null,
        ]);

        $this->command->info('✅ Demo canvasser account created successfully!');
        $this->command->info('');
        $this->command->info('📧 Email: canvasser@demo.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('');
        $this->command->info('🔗 Login at: /canvasser/login');
    }
}
