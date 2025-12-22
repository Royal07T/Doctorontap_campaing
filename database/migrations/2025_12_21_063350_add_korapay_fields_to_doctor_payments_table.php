<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('doctor_payments', function (Blueprint $table) {
            $table->string('korapay_reference')->nullable()->after('transaction_reference');
            $table->string('korapay_status')->nullable()->after('korapay_reference'); // processing, success, failed
            $table->decimal('korapay_fee', 10, 2)->nullable()->after('korapay_status');
            $table->json('korapay_response')->nullable()->after('korapay_fee');
            $table->timestamp('payout_initiated_at')->nullable()->after('korapay_response');
            $table->timestamp('payout_completed_at')->nullable()->after('payout_initiated_at');
            
            // Index for faster lookups
            $table->index('korapay_reference');
            $table->index('korapay_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctor_payments', function (Blueprint $table) {
            $table->dropIndex(['korapay_reference']);
            $table->dropIndex(['korapay_status']);
            $table->dropColumn([
                'korapay_reference',
                'korapay_status',
                'korapay_fee',
                'korapay_response',
                'payout_initiated_at',
                'payout_completed_at',
            ]);
        });
    }
};
