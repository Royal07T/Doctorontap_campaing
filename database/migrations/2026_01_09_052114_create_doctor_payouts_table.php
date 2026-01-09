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
        Schema::create('doctor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->json('consultation_ids'); // Array of consultation IDs included in this payout
            $table->string('payout_reference')->unique(); // e.g., DR-PAYOUT-XXXX
            $table->decimal('total_consultations_amount', 10, 2)->default(0); // Total amount from all consultations
            $table->integer('total_consultations_count')->default(0); // Number of consultations
            $table->decimal('doctor_percentage', 5, 2)->default(70.00); // Default 70% to doctor
            $table->decimal('platform_percentage', 5, 2)->default(30.00); // Default 30% platform fee
            $table->decimal('amount', 10, 2); // Amount to pay doctor (total × doctor_percentage)
            $table->decimal('platform_fee', 10, 2); // Platform fee (total × platform_percentage)
            $table->string('currency', 3)->default('NGN');
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->json('korapay_response')->nullable();
            $table->string('korapay_reference')->nullable();
            $table->date('period_from')->nullable(); // Period start date
            $table->date('period_to')->nullable(); // Period end date
            $table->text('metadata')->nullable(); // Additional metadata
            $table->timestamps();

            // Indexes for better performance
            $table->index('doctor_id');
            $table->index('status');
            $table->index('payout_reference');
            $table->index('created_at');
            $table->index(['period_from', 'period_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_payouts');
    }
};
