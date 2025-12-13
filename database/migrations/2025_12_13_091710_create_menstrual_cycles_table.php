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
        Schema::create('menstrual_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('cycle_length')->nullable()->comment('Length of cycle in days');
            $table->integer('period_length')->nullable()->comment('Length of period in days');
            $table->text('notes')->nullable();
            $table->json('symptoms')->nullable()->comment('Symptoms during period');
            $table->string('flow_intensity')->nullable()->comment('light, moderate, heavy');
            $table->timestamps();
            
            $table->index(['patient_id', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menstrual_cycles');
    }
};
