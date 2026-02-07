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
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Template name (e.g., "Appointment Reminder")
            $table->string('slug')->unique(); // URL-friendly name (e.g., "appointment-reminder")
            $table->text('content'); // SMS message content with placeholders
            $table->json('variables')->nullable(); // Available variables (e.g., {name}, {date}, {time})
            $table->text('description')->nullable(); // What this template is for
            $table->enum('category', ['marketing', 'transactional', 'reminder', 'promotional', 'notification'])->default('marketing');
            $table->boolean('is_active')->default(true); // Can be used by customer care
            $table->unsignedBigInteger('created_by')->nullable(); // Admin who created it
            $table->unsignedBigInteger('updated_by')->nullable(); // Admin who last updated it
            $table->integer('usage_count')->default(0); // How many times it's been used
            $table->timestamps();
            $table->softDeletes(); // Soft delete for audit trail
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('admin_users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('admin_users')->onDelete('set null');
            
            // Indexes for performance
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
