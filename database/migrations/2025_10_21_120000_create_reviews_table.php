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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            // Consultation reference
            $table->foreignId('consultation_id')->constrained('consultations')->onDelete('cascade');
            
            // Reviewer information
            $table->enum('reviewer_type', ['patient', 'doctor']); // Who is reviewing
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            
            // Review target (who is being reviewed)
            $table->enum('reviewee_type', ['doctor', 'patient', 'platform']); // Who is being reviewed
            $table->foreignId('reviewee_doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->foreignId('reviewee_patient_id')->nullable()->constrained('patients')->onDelete('set null');
            
            // Rating and feedback
            $table->unsignedTinyInteger('rating')->comment('1-5 stars');
            $table->text('comment')->nullable();
            
            // Additional metrics
            $table->boolean('would_recommend')->default(true);
            $table->json('tags')->nullable()->comment('e.g., ["professional", "punctual", "helpful"]');
            
            // Moderation
            $table->boolean('is_published')->default(true);
            $table->boolean('is_verified')->default(false)->comment('Verified by admin');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('admin_users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['reviewer_type', 'patient_id']);
            $table->index(['reviewer_type', 'doctor_id']);
            $table->index(['reviewee_type', 'reviewee_doctor_id']);
            $table->index(['reviewee_type', 'reviewee_patient_id']);
            $table->index('is_published');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

