<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * The current unique constraint on (doctor_id, is_default) is problematic
     * because it prevents multiple rows with is_default=0 for the same doctor.
     * 
     * We need to drop it and use a different approach that only enforces
     * uniqueness when is_default=1.
     */
    public function up(): void
    {
        // First, ensure data integrity - only one default per doctor
        $doctorsWithMultipleDefaults = DB::table('doctor_bank_accounts')
            ->select('doctor_id', DB::raw('COUNT(*) as count'))
            ->where('is_default', 1)
            ->groupBy('doctor_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($doctorsWithMultipleDefaults as $doctor) {
            // Keep only the first default account, set others to false
            $defaultAccounts = DB::table('doctor_bank_accounts')
                ->where('doctor_id', $doctor->doctor_id)
                ->where('is_default', 1)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($defaultAccounts->count() > 1) {
                $otherAccounts = $defaultAccounts->skip(1);
                foreach ($otherAccounts as $account) {
                    // Update one at a time to avoid constraint issues
                    DB::table('doctor_bank_accounts')
                        ->where('id', $account->id)
                        ->update(['is_default' => false]);
                }
            }
        }

        // Drop the problematic constraint
        try {
            Schema::table('doctor_bank_accounts', function (Blueprint $table) {
                $table->dropUnique('unique_default_bank_per_doctor');
            });
        } catch (\Exception $e) {
            // Try dropping via raw SQL if Schema method fails
            try {
                DB::statement('ALTER TABLE doctor_bank_accounts DROP INDEX unique_default_bank_per_doctor');
            } catch (\Exception $e2) {
                // Constraint might not exist or might be named differently
                \Log::warning('Could not drop unique constraint: ' . $e2->getMessage());
            }
        }

        // For MySQL 8.0+, we can use a functional unique index
        // For older versions, we'll rely on application logic
        try {
            // Try MySQL 8.0+ functional index approach
            DB::statement('
                CREATE UNIQUE INDEX unique_default_bank_per_doctor 
                ON doctor_bank_accounts (doctor_id, (CASE WHEN is_default = 1 THEN 1 ELSE NULL END))
            ');
        } catch (\Exception $e) {
            // If functional index fails, try a different approach for MySQL 8.0+
            try {
                // Alternative: Use a generated column (MySQL 5.7+)
                if (!Schema::hasColumn('doctor_bank_accounts', 'default_key')) {
                    Schema::table('doctor_bank_accounts', function (Blueprint $table) {
                        $table->integer('default_key')->nullable()->after('is_default');
                    });
                }

                // Update existing records
                DB::statement('UPDATE doctor_bank_accounts SET default_key = doctor_id WHERE is_default = 1');
                DB::statement('UPDATE doctor_bank_accounts SET default_key = NULL WHERE is_default = 0');

                // Create unique index on the generated column
                DB::statement('CREATE UNIQUE INDEX unique_default_bank_per_doctor ON doctor_bank_accounts (default_key)');
            } catch (\Exception $e2) {
                // If all else fails, we'll rely on application logic
                \Log::info('Could not create functional unique index. Relying on application logic for uniqueness.');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new index/constraint
        try {
            DB::statement('DROP INDEX unique_default_bank_per_doctor ON doctor_bank_accounts');
        } catch (\Exception $e) {
            // Index might not exist
        }

        // Drop the default_key column if it exists
        if (Schema::hasColumn('doctor_bank_accounts', 'default_key')) {
            Schema::table('doctor_bank_accounts', function (Blueprint $table) {
                $table->dropColumn('default_key');
            });
        }

        // Restore the original constraint
        Schema::table('doctor_bank_accounts', function (Blueprint $table) {
            $table->unique(['doctor_id', 'is_default'], 'unique_default_bank_per_doctor');
        });
    }
};
