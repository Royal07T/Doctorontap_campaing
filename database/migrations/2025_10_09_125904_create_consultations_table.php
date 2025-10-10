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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            
            // Patient Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('mobile');
            $table->integer('age');
            $table->string('gender');
            
            // Medical Information
            $table->string('problem');
            $table->string('severity');
            $table->json('emergency_symptoms')->nullable();
            
            // Consultation Details
            $table->string('consult_mode'); // voice, video, chat
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            
            // Status Tracking
            $table->string('status')->default('pending'); // pending, scheduled, completed, cancelled
            $table->string('payment_status')->default('unpaid'); // unpaid, pending, paid
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            
            // Payment Request
            $table->boolean('payment_request_sent')->default(false);
            $table->timestamp('payment_request_sent_at')->nullable();
            $table->timestamp('consultation_completed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
