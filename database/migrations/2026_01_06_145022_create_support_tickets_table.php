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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('customer_cares')->onDelete('set null');
            $table->enum('category', ['billing', 'appointment', 'technical', 'medical'])->default('technical');
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['open', 'pending', 'resolved', 'escalated'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('customer_cares')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'status']);
            $table->index(['agent_id', 'status']);
            $table->index(['category', 'status']);
            $table->index('ticket_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
