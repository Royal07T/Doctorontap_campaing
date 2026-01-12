<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds caregiver_id to vital_signs table for audit trail.
     * Allows tracking which caregiver recorded vital signs.
     */
    public function up(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->foreignId('caregiver_id')->nullable()->after('nurse_id')->constrained('care_givers')->onDelete('set null');
            $table->index('caregiver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->dropForeign(['caregiver_id']);
            $table->dropIndex(['caregiver_id']);
            $table->dropColumn('caregiver_id');
        });
    }
};
