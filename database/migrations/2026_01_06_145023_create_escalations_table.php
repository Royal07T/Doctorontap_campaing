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
        Schema::create('escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->nullable()->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('customer_interaction_id')->nullable()->constrained('customer_interactions')->onDelete('cascade');
            $table->foreignId('escalated_by')->constrained('customer_cares')->onDelete('cascade');
            $table->enum('escalated_to_type', ['admin', 'doctor'])->default('admin');
            $table->foreignId('escalated_to_id')->nullable(); // Can be admin_user_id or doctor_id
            $table->text('reason');
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'closed'])->default('pending');
            $table->text('outcome')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['escalated_by', 'status']);
            $table->index(['escalated_to_type', 'escalated_to_id']);
            $table->index('support_ticket_id');
            $table->index('customer_interaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escalations');
    }
};
