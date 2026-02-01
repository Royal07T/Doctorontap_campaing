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
        Schema::create('patient_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['sms', 'whatsapp', 'voice', 'video', 'email']);
            $table->enum('direction', ['inbound', 'outbound']);
            $table->text('content');
            $table->string('template')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'initiated', 'active', 'completed', 'ringing']);
            $table->string('message_id')->nullable();
            $table->string('call_uuid')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->text('error')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['patient_id', 'type']);
            $table->index(['status', 'created_at']);
            $table->index(['type', 'direction']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_communications');
    }
};
