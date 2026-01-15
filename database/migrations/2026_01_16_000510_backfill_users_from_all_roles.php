<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
// Models not needed for this migration as we use DB facade directly

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration backfills the users table with existing users from all role tables.
     * It creates a user record for each role-specific record and links them via user_id.
     */
    public function up(): void
    {
        // Define all role tables and their corresponding roles
        $roleTables = [
            'patients' => 'patient',
            'admin_users' => 'admin',
            'canvassers' => 'canvasser',
            'nurses' => 'nurse',
            'doctors' => 'doctor',
            'customer_cares' => 'customer_care',
            'care_givers' => 'care_giver',
        ];

        foreach ($roleTables as $table => $role) {
            $records = DB::table($table)
                ->whereNull('user_id')
                ->whereNotNull('email')
                ->get();

            foreach ($records as $record) {
                // Check if user already exists with this email
                $existingUser = DB::table('users')
                    ->where('email', $record->email)
                    ->first();

                if ($existingUser) {
                    // Link existing user
                    DB::table($table)
                        ->where('id', $record->id)
                        ->update(['user_id' => $existingUser->id]);
                } else {
                    // Create new user
                    $userId = DB::table('users')->insertGetId([
                        'name' => $record->name ?? ucfirst($role),
                        'email' => $record->email,
                        'password' => $record->password ?? Hash::make(Str::random(32)), // Use existing password or generate one
                        'role' => $role,
                        'email_verified_at' => $record->email_verified_at ?? null,
                        'created_at' => $record->created_at ?? now(),
                        'updated_at' => $record->updated_at ?? now(),
                    ]);

                    // Link record to user
                    DB::table($table)
                        ->where('id', $record->id)
                        ->update(['user_id' => $userId]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     * 
     * WARNING: This will delete user records that were created during backfill.
     * Only unlink the relationships, but keep the user records for safety.
     */
    public function down(): void
    {
        // Unlink all role tables (but don't delete users to preserve data)
        $tables = ['patients', 'admin_users', 'canvassers', 'nurses', 'doctors', 'customer_cares', 'care_givers'];
        
        foreach ($tables as $table) {
            DB::table($table)->update(['user_id' => null]);
        }
        
        // Optionally delete users with specific roles if you want full rollback
        // DB::table('users')->whereIn('role', ['patient', 'admin', 'canvasser', 'nurse', 'doctor', 'customer_care', 'care_giver'])->delete();
    }
};
