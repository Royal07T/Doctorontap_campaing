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
        Schema::create('doctor_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('bank_account_id')->nullable()->constrained('doctor_bank_accounts')->onDelete('set null');
            $table->decimal('total_consultations_amount', 10, 2)->default(0);
            $table->integer('total_consultations_count')->default(0);
            $table->integer('paid_consultations_count')->default(0);
            $table->integer('unpaid_consultations_count')->default(0);
            $table->decimal('doctor_percentage', 5, 2)->default(70.00); // Default 70% to doctor
            $table->decimal('platform_percentage', 5, 2)->default(30.00); // Default 30% platform fee
            $table->decimal('doctor_amount', 10, 2); // Amount to pay doctor
            $table->decimal('platform_fee', 10, 2); // Platform fee
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->string('payment_method')->nullable(); // bank_transfer, cash, mobile_money, etc.
            $table->string('transaction_reference')->nullable();
            $table->text('payment_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('consultation_ids')->nullable(); // Array of consultation IDs included in this payment
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index('doctor_id');
            $table->index('status');
            $table->index(['period_from', 'period_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_payments');
    }
};
