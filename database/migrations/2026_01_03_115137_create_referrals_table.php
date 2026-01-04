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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            
            // Original consultation being referred
            $table->foreignId('consultation_id')->constrained('consultations')->onDelete('cascade');
            
            // Doctors involved
            $table->foreignId('referring_doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('referred_to_doctor_id')->constrained('doctors')->onDelete('cascade');
            
            // Referral details
            $table->text('reason')->nullable(); // Reason for referral
            $table->text('notes')->nullable(); // Additional notes
            
            // New consultation created for referred doctor (if created)
            $table->foreignId('new_consultation_id')->nullable()->constrained('consultations')->onDelete('set null');
            
            // Status: pending, accepted, completed, declined
            $table->enum('status', ['pending', 'accepted', 'completed', 'declined'])->default('pending');
            
            // Timestamps
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('consultation_id');
            $table->index('referring_doctor_id');
            $table->index('referred_to_doctor_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
