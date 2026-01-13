<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds fields to track missed consultations and automatic penalty system.
     * When a doctor misses 3 consultations, they are automatically set to unavailable.
     */
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            // Missed consultation tracking
            $table->integer('missed_consultations_count')->default(0)->after('is_available')
                ->comment('Number of missed consultations (resets after penalty is applied)');
            
            $table->timestamp('last_missed_consultation_at')->nullable()->after('missed_consultations_count')
                ->comment('Timestamp of the last missed consultation');
            
            $table->timestamp('penalty_applied_at')->nullable()->after('last_missed_consultation_at')
                ->comment('Timestamp when penalty was last applied (auto-unavailable)');
            
            $table->text('unavailable_reason')->nullable()->after('penalty_applied_at')
                ->comment('Reason for being unavailable (e.g., "Auto-set unavailable due to 3 missed consultations")');
            
            $table->boolean('is_auto_unavailable')->default(false)->after('unavailable_reason')
                ->comment('Flag indicating if doctor was auto-set to unavailable due to penalties');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn([
                'missed_consultations_count',
                'last_missed_consultation_at',
                'penalty_applied_at',
                'unavailable_reason',
                'is_auto_unavailable'
            ]);
        });
    }
};
