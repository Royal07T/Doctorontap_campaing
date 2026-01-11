<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if super admin already exists
        $existingSuperAdmin = AdminUser::where('role', 'super_admin')->first();
        
        if ($existingSuperAdmin) {
            $this->command->info('Super admin already exists. Updating existing user...');
            $existingSuperAdmin->update([
                'name' => 'Super Administrator',
                'email' => 'superadmin@doctorontap.com',
                'password' => Hash::make('SuperAdmin123!'),
                'role' => 'super_admin',
                'can_impersonate' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $this->command->info('Super admin updated successfully!');
        } else {
            // Create new super admin
            AdminUser::create([
                'name' => 'Super Administrator',
                'email' => 'superadmin@doctorontap.com',
                'password' => Hash::make('SuperAdmin123!'),
                'role' => 'super_admin',
                'can_impersonate' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $this->command->info('Super admin created successfully!');
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  Super Admin Credentials');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  Email:    superadmin@doctorontap.com');
        $this->command->info('  Password: SuperAdmin123!');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->warn('⚠️  Please change the password after first login!');
        $this->command->info('');
    }
}
