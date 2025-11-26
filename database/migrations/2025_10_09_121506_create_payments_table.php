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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('customer_email');
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('NGN');
            $table->string('status')->default('pending'); // pending, success, failed, cancelled, processing
            $table->string('payment_method')->nullable(); // bank_transfer, card, pay_with_bank, mobile_money
            $table->string('payment_reference')->nullable(); // Korapay payment reference
            $table->decimal('fee', 10, 2)->nullable();
            $table->text('checkout_url')->nullable();
            $table->json('metadata')->nullable();
            $table->text('korapay_response')->nullable();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
