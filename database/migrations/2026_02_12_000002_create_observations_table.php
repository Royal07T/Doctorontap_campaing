<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the observations table for qualitative health data (mood, mobility, pain).
     * behavior_notes is encrypted at the application level for HIPAA compliance.
     */
    public function up(): void
    {
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('caregiver_id')->constrained('care_givers')->onDelete('cascade');
            $table->string('emoji_code', 50)->nullable()->comment('Emoji identifier for mood, e.g. happy, sad, anxious');
            $table->text('mobility_notes')->nullable();
            $table->unsignedTinyInteger('pain_level')->nullable()->comment('0-10 scale');
            $table->text('behavior_notes')->nullable()->comment('Encrypted at application level');
            $table->text('general_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['patient_id', 'created_at']);
            $table->index('caregiver_id');
            $table->index('emoji_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
