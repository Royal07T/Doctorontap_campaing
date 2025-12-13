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
        Schema::create('fee_adjustment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->onDelete('set null');
            
            // Who made the change
            $table->string('adjusted_by_type'); // doctor, admin, system
            $table->unsignedBigInteger('adjusted_by_id');
            
            // What changed
            $table->decimal('old_amount', 10, 2);
            $table->decimal('new_amount', 10, 2);
            $table->text('adjustment_reason');
            
            // Impact
            $table->decimal('total_invoice_before', 10, 2);
            $table->decimal('total_invoice_after', 10, 2);
            
            // Notifications triggered
            $table->boolean('notification_sent_to_payer')->default(false);
            $table->boolean('notification_sent_to_accountant')->default(false);
            
            $table->timestamp('created_at');
            
            // Indexes
            $table->index('booking_id');
            $table->index(['adjusted_by_type', 'adjusted_by_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_adjustment_logs');
    }
};

