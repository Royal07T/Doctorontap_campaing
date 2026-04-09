<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physio_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('care_plan_id')->constrained('care_plans')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('care_givers')->cascadeOnDelete();
            $table->string('session_type')->comment('assessment, exercise, massage, review');
            $table->dateTime('scheduled_at');
            $table->dateTime('completed_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->json('exercises')->nullable()->comment('Array of exercise objects: {name, sets, reps, duration, notes}');
            $table->text('findings')->nullable();
            $table->text('treatment_notes')->nullable();
            $table->string('pain_level_before')->nullable()->comment('1-10 scale');
            $table->string('pain_level_after')->nullable()->comment('1-10 scale');
            $table->string('mobility_score')->nullable()->comment('poor, fair, good, excellent');
            $table->string('status')->default('scheduled')->comment('scheduled, in_progress, completed, cancelled');
            $table->text('next_session_plan')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physio_sessions');
    }
};
