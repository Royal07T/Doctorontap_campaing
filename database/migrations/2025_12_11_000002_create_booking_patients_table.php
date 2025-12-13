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
        Schema::create('booking_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('consultation_id')->nullable()->constrained('consultations')->onDelete('set null');
            
            // Relationship to payer
            $table->string('relationship_to_payer')->nullable(); // self, child, parent, spouse, other
            
            // Pricing
            $table->decimal('base_fee', 10, 2)->default(0.00);
            $table->decimal('adjusted_fee', 10, 2)->default(0.00);
            $table->text('fee_adjustment_reason')->nullable();
            $table->foreignId('fee_adjusted_by')->nullable()->constrained('doctors')->onDelete('set null');
            $table->timestamp('fee_adjusted_at')->nullable();
            
            // Status per patient
            $table->string('consultation_status')->default('pending');
            
            // Order within booking
            $table->integer('order_index')->default(0);
            
            $table->timestamps();
            
            // Indexes and constraints
            $table->unique(['booking_id', 'patient_id'], 'unique_booking_patient');
            $table->index('booking_id');
            $table->index('patient_id');
            $table->index('consultation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_patients');
    }
};

