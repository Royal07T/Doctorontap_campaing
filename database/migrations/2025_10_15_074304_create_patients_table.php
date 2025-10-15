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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('gender');
            
            // Track who registered the patient
            $table->foreignId('canvasser_id')->nullable()->constrained('canvassers')->onDelete('set null');
            
            // Track if patient has consulted (paid for consultation)
            $table->boolean('has_consulted')->default(false);
            $table->decimal('total_amount_paid', 10, 2)->default(0);
            $table->timestamp('last_consultation_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
