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
     * SAFE MIGRATION: This migration is backward-compatible:
     * - Existing consultations with 'voice', 'video', 'chat' will remain valid
     * - New enum includes 'whatsapp' as default for backward compatibility
     * - All existing WhatsApp consultations will be migrated to 'whatsapp' mode
     */
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // First, add new columns (nullable initially for safety)
            $table->enum('consultation_mode', ['whatsapp', 'voice', 'video', 'chat'])
                  ->default('whatsapp')
                  ->after('consult_mode')
                  ->comment('Primary consultation mode: whatsapp (legacy), voice, video, or chat');
            
            $table->enum('session_status', ['scheduled', 'waiting', 'active', 'completed', 'cancelled'])
                  ->nullable()
                  ->after('status')
                  ->comment('Session lifecycle status for in-app consultations');
            
            $table->timestamp('started_at')->nullable()->after('session_status');
            $table->timestamp('ended_at')->nullable()->after('started_at');
        });

        // Migrate existing data: Set consultation_mode based on consult_mode
        // If consult_mode exists and is valid, use it; otherwise default to 'whatsapp'
        DB::statement("
            UPDATE consultations 
            SET consultation_mode = CASE 
                WHEN consult_mode IN ('voice', 'video', 'chat') THEN consult_mode
                ELSE 'whatsapp'
            END
        ");

        // For consultations with WhatsApp fields set, ensure they're marked as whatsapp mode
        DB::statement("
            UPDATE consultations 
            SET consultation_mode = 'whatsapp'
            WHERE (whatsapp_last_message_at IS NOT NULL 
                   OR whatsapp_window_open = 1 
                   OR whatsapp_last_message IS NOT NULL)
            AND consultation_mode IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn([
                'consultation_mode',
                'session_status',
                'started_at',
                'ended_at'
            ]);
        });
    }
};
