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
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_name'); // Name of the campaign
            $table->unsignedBigInteger('template_id')->nullable(); // Template used
            $table->unsignedBigInteger('sent_by'); // Customer care who sent it
            $table->string('subject'); // Email subject (after variable replacement)
            $table->text('message_content'); // Final HTML message sent
            $table->text('plain_text_content')->nullable(); // Plain text version
            $table->json('recipient_emails'); // JSON array of email addresses
            $table->integer('total_recipients')->default(0); // Total recipients
            $table->integer('successful_sends')->default(0); // Successfully sent
            $table->integer('failed_sends')->default(0); // Failed
            $table->integer('opened_count')->default(0); // Emails opened (if tracking enabled)
            $table->integer('clicked_count')->default(0); // Links clicked (if tracking enabled)
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('send_results')->nullable(); // Detailed results for each recipient
            $table->string('from_name')->nullable(); // Sender name used
            $table->string('from_email')->nullable(); // Sender email used
            $table->timestamp('scheduled_at')->nullable(); // For scheduled campaigns
            $table->timestamp('completed_at')->nullable(); // When campaign completed
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('template_id')->references('id')->on('email_templates')->onDelete('set null');
            $table->foreign('sent_by')->references('id')->on('customer_cares')->onDelete('cascade');
            
            // Indexes
            $table->index('status');
            $table->index('sent_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};
