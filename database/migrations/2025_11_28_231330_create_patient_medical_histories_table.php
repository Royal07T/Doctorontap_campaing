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
        Schema::create('patient_medical_histories', function (Blueprint $table) {
            $table->id();
            
            // Patient Link
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('cascade');
            $table->string('patient_email')->index(); // For lookup even if no patient record
            $table->string('patient_name');
            $table->string('patient_mobile');
            
            // Consultation Link (source of this history)
            $table->foreignId('consultation_id')->constrained('consultations')->onDelete('cascade');
            $table->string('consultation_reference')->index();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            
            // Medical History Fields (Cumulative)
            $table->text('presenting_complaint')->nullable();
            $table->text('history_of_complaint')->nullable();
            $table->text('past_medical_history')->nullable();
            $table->text('family_history')->nullable();
            $table->text('drug_history')->nullable();
            $table->text('social_history')->nullable();
            $table->text('allergies')->nullable();
            
            // Diagnosis & Treatment (From each consultation)
            $table->text('diagnosis')->nullable();
            $table->text('investigation')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->json('prescribed_medications')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->text('lifestyle_recommendations')->nullable();
            $table->json('referrals')->nullable();
            $table->date('next_appointment_date')->nullable();
            $table->text('additional_notes')->nullable();
            
            // Vital Signs (if captured)
            $table->string('blood_pressure')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->integer('heart_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();
            $table->integer('oxygen_saturation')->nullable();
            
            // Metadata
            $table->date('consultation_date');
            $table->string('severity')->nullable();
            $table->boolean('is_latest')->default(true); // Flag for most recent record
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['patient_email', 'consultation_date']);
            $table->index(['patient_id', 'is_latest']);
            $table->index('consultation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_medical_histories');
    }
};
