<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Patient;
use App\Models\AdminUser;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Canvasser;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration backfills the users table from all role tables.
     * It processes roles in order: admin_users → doctors → nurses → canvassers → patients
     * 
     * For each role:
     * 1. Iterate through all records
     * 2. Check if user with same email already exists
     * 3. If exists, link to existing user (handles duplicate emails)
     * 4. If not exists, create new user record
     * 5. Update role record's user_id
     */
    public function up(): void
    {
        // Process AdminUsers
        $this->backfillRole(
            AdminUser::class,
            'admin_users',
            'admin'
        );

        // Process Doctors
        $this->backfillRole(
            Doctor::class,
            'doctors',
            'doctor'
        );

        // Process Nurses
        $this->backfillRole(
            Nurse::class,
            'nurses',
            'nurse'
        );

        // Process Canvassers
        $this->backfillRole(
            Canvasser::class,
            'canvassers',
            'canvasser'
        );

        // Process Patients
        $this->backfillRole(
            Patient::class,
            'patients',
            'patient'
        );
    }

    /**
     * Backfill users from a specific role table
     */
    private function backfillRole(string $modelClass, string $tableName, string $role): void
    {
        echo "Backfilling {$tableName}...\n";
        
        $records = DB::table($tableName)
            ->whereNull('deleted_at')
            ->orWhereNotNull('deleted_at')
            ->get();

        $processed = 0;
        $created = 0;
        $linked = 0;
        $skipped = 0;

        foreach ($records as $record) {
            // Skip if already linked to a user
            if (!empty($record->user_id)) {
                continue;
            }

            // Skip if email or password is null/empty
            if (empty($record->email) || empty($record->password)) {
                echo "  Skipping {$tableName} ID {$record->id}: missing email or password\n";
                $skipped++;
                continue;
            }

            // Check if user with this email already exists
            $existingUser = User::where('email', $record->email)->first();

            if ($existingUser) {
                // Link to existing user
                DB::table($tableName)
                    ->where('id', $record->id)
                    ->update(['user_id' => $existingUser->id]);
                $linked++;
            } else {
                // Create new user
                $user = User::create([
                    'name' => $record->name,
                    'email' => $record->email,
                    'password' => $record->password,
                    'role' => $role,
                    'email_verified_at' => $record->email_verified_at ?? null,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ]);

                // Link role record to user
                DB::table($tableName)
                    ->where('id', $record->id)
                    ->update(['user_id' => $user->id]);
                $created++;
            }

            $processed++;
        }

        echo "  Processed: {$processed} | Created: {$created} | Linked: {$linked} | Skipped: {$skipped}\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear user_id from all role tables
        DB::table('patients')->update(['user_id' => null]);
        DB::table('admin_users')->update(['user_id' => null]);
        DB::table('doctors')->update(['user_id' => null]);
        DB::table('nurses')->update(['user_id' => null]);
        DB::table('canvassers')->update(['user_id' => null]);

        // Delete users created by this migration (those with a role)
        User::whereNotNull('role')->delete();
    }
};
