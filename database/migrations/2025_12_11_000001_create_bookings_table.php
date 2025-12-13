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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            
            // Payer/Guardian Information
            $table->string('payer_name');
            $table->string('payer_email');
            $table->string('payer_mobile');
            
            // Booking Details
            $table->string('consult_mode'); // voice, video, chat
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->foreignId('canvasser_id')->nullable()->constrained('canvassers')->onDelete('set null');
            $table->foreignId('nurse_id')->nullable()->constrained('nurses')->onDelete('set null');
            
            // Status
            $table->string('status')->default('pending'); // pending, scheduled, in_progress, completed, cancelled
            
            // Payment
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('total_adjusted_amount', 10, 2)->default(0.00);
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid
            
            // Timestamps
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('reference');
            $table->index(['doctor_id', 'status']);
            $table->index('payer_email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

