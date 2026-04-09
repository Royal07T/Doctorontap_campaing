<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('care_plan_id')->constrained('care_plans')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('care_givers')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('meals')->nullable()->comment('Array of meal objects: {name, time, items[], calories, notes}');
            $table->json('restrictions')->nullable()->comment('Array of dietary restrictions');
            $table->json('supplements')->nullable()->comment('Array of supplements');
            $table->integer('target_calories')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active')->comment('active, completed, paused');
            $table->text('dietician_notes')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diet_plans');
    }
};
