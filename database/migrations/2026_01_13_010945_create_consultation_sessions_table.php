<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores Vonage session information for in-app consultations.
     * Each consultation can have multiple sessions (for rescheduling, etc.)
     */
    public function up(): void
    {
        Schema::create('consultation_sessions', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to consultations
            $table->foreignId('consultation_id')
                  ->constrained('consultations')
                  ->onDelete('cascade');
            
            // Vonage session identifiers
            $table->string('vonage_session_id')->unique()->nullable()
                  ->comment('Vonage Video/Conversation session ID');
            
            // Encrypted tokens (never store plain text)
            $table->text('vonage_token_doctor')->nullable()
                  ->comment('Encrypted Vonage token for doctor (JWT)');
            $table->text('vonage_token_patient')->nullable()
                  ->comment('Encrypted Vonage token for patient (JWT)');
            
            // Session metadata
            $table->enum('mode', ['voice', 'video', 'chat'])
                  ->comment('Consultation mode for this session');
            
            $table->enum('status', ['pending', 'active', 'ended', 'failed', 'cancelled'])
                  ->default('pending')
                  ->comment('Session status');
            
            // Token expiration tracking
            $table->timestamp('token_expires_at')->nullable()
                  ->comment('When the Vonage tokens expire');
            
            // Session timing
            $table->timestamp('session_started_at')->nullable();
            $table->timestamp('session_ended_at')->nullable();
            
            // Error tracking
            $table->text('error_message')->nullable()
                  ->comment('Error message if session failed');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('consultation_id');
            $table->index('vonage_session_id');
            $table->index('status');
            $table->index(['consultation_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_sessions');
    }
};
