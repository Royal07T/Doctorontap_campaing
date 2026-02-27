<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the care_plans table. Each care plan is tied to a patient
     * and determines which features are visible in the caregiver dashboard.
     *
     * Plan types:
     *  - meridian:  Basic vitals + daily check-ins
     *  - executive: Adds physician review + risk flags + weekly reports
     *  - sovereign: Full suite incl. Dietician, Physiotherapist
     */
    public function up(): void
    {
        Schema::create('care_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->enum('plan_type', ['meridian', 'executive', 'sovereign'])->default('meridian');
            $table->date('start_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'paused', 'completed', 'expired'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['patient_id', 'status']);
            $table->index('plan_type');
            $table->index('status');
            $table->index('expiry_date');
        });

        // Now add proper foreign key constraint on caregiver_patient_assignments.care_plan_id
        Schema::table('caregiver_patient_assignments', function (Blueprint $table) {
            // Drop the existing column (was unsignedBigInteger placeholder)
            $table->foreign('care_plan_id')
                  ->references('id')
                  ->on('care_plans')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caregiver_patient_assignments', function (Blueprint $table) {
            $table->dropForeign(['care_plan_id']);
        });

        Schema::dropIfExists('care_plans');
    }
};
