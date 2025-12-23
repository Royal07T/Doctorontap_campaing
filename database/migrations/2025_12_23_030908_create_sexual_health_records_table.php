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
        Schema::create('sexual_health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->date('record_date');
            
            // Libido level
            $table->enum('libido_level', ['low', 'normal', 'high'])->nullable();
            
            // Erectile health (discreet scale 1-10, optional)
            $table->integer('erectile_health_score')->nullable()->comment('Scale 1-10, optional');
            
            // Ejaculation issues (optional check)
            $table->boolean('ejaculation_issues')->default(false);
            $table->text('ejaculation_notes')->nullable();
            
            // STI check reminders
            $table->date('last_sti_test_date')->nullable();
            $table->date('next_sti_test_reminder')->nullable();
            $table->boolean('sti_test_due')->default(false);
            
            // Additional notes (private)
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['patient_id', 'record_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sexual_health_records');
    }
};
