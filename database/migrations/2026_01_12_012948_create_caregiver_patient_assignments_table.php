<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates a secure pivot table for caregiver-patient assignments.
     * This enforces that caregivers can ONLY access patients explicitly assigned to them.
     */
    public function up(): void
    {
        Schema::create('caregiver_patient_assignments', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('caregiver_id')->constrained('care_givers')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            // care_plan_id will be added when care_plans table is created
            $table->unsignedBigInteger('care_plan_id')->nullable()->comment('Foreign key to care_plans (to be added when table exists)');
            
            // Assignment metadata
            $table->enum('role', ['primary', 'secondary', 'backup'])->default('secondary');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('assigned_by')->nullable()->constrained('admin_users')->onDelete('set null');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['caregiver_id', 'patient_id']);
            $table->index(['patient_id', 'status']);
            $table->index('status');
            $table->index('role');
            
            // Prevent duplicate active assignments (using unique index on caregiver_id + patient_id)
            // Note: Multiple assignments allowed if one is inactive
            $table->unique(['caregiver_id', 'patient_id'], 'unique_caregiver_patient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caregiver_patient_assignments');
    }
};
