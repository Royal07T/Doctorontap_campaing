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
     * Force drop the problematic unique constraint and rely on application logic
     * to ensure only one default account per doctor.
     */
    public function up(): void
    {
        // First, fix any data integrity issues
        $doctorsWithMultipleDefaults = DB::table('doctor_bank_accounts')
            ->select('doctor_id', DB::raw('COUNT(*) as count'))
            ->where('is_default', 1)
            ->groupBy('doctor_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($doctorsWithMultipleDefaults as $doctor) {
            $defaultAccounts = DB::table('doctor_bank_accounts')
                ->where('doctor_id', $doctor->doctor_id)
                ->where('is_default', 1)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($defaultAccounts->count() > 1) {
                $otherAccounts = $defaultAccounts->skip(1);
                foreach ($otherAccounts as $account) {
                    DB::statement('UPDATE doctor_bank_accounts SET is_default = 0 WHERE id = ?', [$account->id]);
                }
            }
        }

        // Force drop the constraint using raw SQL
        try {
            DB::statement('ALTER TABLE doctor_bank_accounts DROP INDEX unique_default_bank_per_doctor');
        } catch (\Exception $e) {
            // Try with IF EXISTS (MySQL 8.0+)
            try {
                DB::statement('ALTER TABLE doctor_bank_accounts DROP INDEX IF EXISTS unique_default_bank_per_doctor');
            } catch (\Exception $e2) {
                \Log::warning('Could not drop unique constraint: ' . $e2->getMessage());
            }
        }

        // We'll rely on application logic to ensure only one default per doctor
        // The application code already handles this correctly
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the original constraint
        Schema::table('doctor_bank_accounts', function (Blueprint $table) {
            try {
                $table->unique(['doctor_id', 'is_default'], 'unique_default_bank_per_doctor');
            } catch (\Exception $e) {
                // Constraint might already exist
            }
        });
    }
};
