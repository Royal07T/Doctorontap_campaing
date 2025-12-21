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
     * Fix data integrity first: ensure only one default account per doctor
     * Then we'll handle the constraint issue by updating the approach.
     */
    public function up(): void
    {
        // First, fix any data integrity issues
        // For each doctor, if there are multiple default accounts, keep only the first one
        $doctorsWithMultipleDefaults = DB::table('doctor_bank_accounts')
            ->select('doctor_id', DB::raw('COUNT(*) as count'))
            ->where('is_default', 1)
            ->groupBy('doctor_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($doctorsWithMultipleDefaults as $doctor) {
            // Get all default accounts for this doctor, ordered by created_at
            $defaultAccounts = DB::table('doctor_bank_accounts')
                ->where('doctor_id', $doctor->doctor_id)
                ->where('is_default', 1)
                ->orderBy('created_at', 'asc')
                ->get();

            // Keep the first one as default, set others to false
            if ($defaultAccounts->count() > 1) {
                $firstAccount = $defaultAccounts->first();
                $otherAccounts = $defaultAccounts->skip(1);

                foreach ($otherAccounts as $account) {
                    DB::table('doctor_bank_accounts')
                        ->where('id', $account->id)
                        ->update(['is_default' => false]);
                }
            }
        }

        // The constraint should now work correctly
        // The application code already handles updating one at a time
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse - we're just fixing data
    }
};
