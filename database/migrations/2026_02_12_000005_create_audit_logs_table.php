<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the audit_logs table for HIPAA-compliant access tracking.
     * Records who viewed/modified which patient data and when.
     * Complements the existing Auditable trait (which logs to file channel).
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type', 50)->nullable()->comment('Guard/role: admin, doctor, caregiver, nurse, patient, family');
            $table->string('user_email')->nullable();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->string('action', 100)->comment('viewed, created, updated, deleted, exported, accessed');
            $table->string('resource_type')->nullable()->comment('Model class or resource name');
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable()->comment('Additional context: changed fields, etc.');
            $table->timestamp('created_at')->useCurrent();

            // Indexes for fast querying
            $table->index(['user_id', 'user_type']);
            $table->index('patient_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['resource_type', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
