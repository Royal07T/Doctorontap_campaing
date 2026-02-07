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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Template name
            $table->string('slug')->unique(); // URL-friendly name
            $table->string('subject'); // Email subject line with variable support
            $table->text('content'); // HTML email content with placeholders
            $table->text('plain_text_content')->nullable(); // Plain text version
            $table->json('variables')->nullable(); // Available variables (e.g., {name}, {date})
            $table->text('description')->nullable(); // What this template is for
            $table->enum('category', ['marketing', 'transactional', 'notification', 'reminder', 'promotional', 'newsletter'])->default('marketing');
            $table->boolean('is_active')->default(true); // Can be used by customer care
            $table->string('from_name')->nullable(); // Custom sender name
            $table->string('from_email')->nullable(); // Custom sender email
            $table->string('reply_to')->nullable(); // Reply-to email
            $table->json('attachments')->nullable(); // Default attachments if any
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
        Schema::dropIfExists('email_templates');
    }
};
