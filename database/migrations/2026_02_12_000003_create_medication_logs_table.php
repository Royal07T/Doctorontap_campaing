<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the medication_logs table to track medicine
     * administration and compliance per patient.
     */
    public function up(): void
    {
        Schema::create('medication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('caregiver_id')->constrained('care_givers')->onDelete('cascade');
            $table->string('medication_name');
            $table->string('dosage')->nullable();
            $table->dateTime('scheduled_time');
            $table->dateTime('administered_at')->nullable();
            $table->enum('status', ['pending', 'given', 'missed', 'skipped', 'refused'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['patient_id', 'scheduled_time']);
            $table->index(['caregiver_id', 'status']);
            $table->index('status');
            $table->index('scheduled_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_logs');
    }
};
