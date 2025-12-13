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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            
            // Customer info
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            
            // Amounts
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('total_adjustments', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            
            // Status
            $table->string('status')->default('draft'); // draft, pending, paid, partially_paid, cancelled
            
            // Payment integration
            $table->string('payment_provider')->nullable(); // korapay, manual, etc.
            $table->string('payment_reference')->nullable();
            
            // Metadata
            $table->string('currency', 3)->default('NGN');
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('booking_id');
            $table->index('reference');
            $table->index('status');
            $table->index('customer_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

