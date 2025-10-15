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
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('nurse_id')->nullable()->constrained('nurses')->onDelete('set null');
            
            // Vital signs measurements
            $table->string('blood_pressure')->nullable()->comment('e.g., 120/80');
            $table->decimal('oxygen_saturation', 5, 2)->nullable()->comment('SpO2 percentage');
            $table->decimal('temperature', 5, 2)->nullable()->comment('in Celsius');
            $table->decimal('blood_sugar', 6, 2)->nullable()->comment('mg/dL');
            $table->decimal('height', 5, 2)->nullable()->comment('in cm');
            $table->decimal('weight', 5, 2)->nullable()->comment('in kg');
            $table->integer('heart_rate')->nullable()->comment('beats per minute');
            $table->integer('respiratory_rate')->nullable()->comment('breaths per minute');
            
            // Additional notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
