<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds flag_status to existing vital_signs table for escalation logic.
     * When a caregiver records vitals that exceed thresholds, the system
     * marks the record as warning/critical and triggers alerts.
     */
    public function up(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->enum('flag_status', ['normal', 'warning', 'critical'])
                  ->default('normal')
                  ->after('notes')
                  ->comment('Escalation status based on threshold evaluation');

            $table->index('flag_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->dropIndex(['flag_status']);
            $table->dropColumn('flag_status');
        });
    }
};
