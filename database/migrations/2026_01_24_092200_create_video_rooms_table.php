<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_rooms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');

            $table->foreignId('consultation_id')->nullable()->constrained('consultations')->nullOnDelete();

            // Enforce one non-ended room per consultation across DB engines.
            // When room is ended, this column is set to NULL.
            $table->foreignId('active_consultation_id')->nullable()->constrained('consultations')->nullOnDelete();
            $table->unique('active_consultation_id');

            $table->string('vonage_session_id')->unique();
            $table->enum('status', ['pending', 'active', 'ended'])->default('pending');

            // Stores users.id (doctor/patient/etc). For doctor/patient you can use their user_id.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->unsignedInteger('participant_count')->nullable();

            $table->timestamps();

            $table->index('consultation_id');
            $table->index(['consultation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_rooms');
    }
};
