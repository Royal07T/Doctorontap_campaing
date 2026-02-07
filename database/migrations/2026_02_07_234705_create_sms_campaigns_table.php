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
        Schema::create('sms_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_name'); // Name of the campaign
            $table->unsignedBigInteger('template_id')->nullable(); // Template used
            $table->unsignedBigInteger('sent_by'); // Customer care who sent it
            $table->text('message_content'); // Final message sent (after variable replacement)
            $table->text('recipient_phones'); // JSON array of phone numbers
            $table->integer('total_recipients')->default(0); // Total recipients
            $table->integer('successful_sends')->default(0); // Successfully sent
            $table->integer('failed_sends')->default(0); // Failed
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('send_results')->nullable(); // Detailed results for each recipient
            $table->decimal('cost', 10, 2)->nullable(); // Cost of campaign
            $table->timestamp('scheduled_at')->nullable(); // For scheduled campaigns
            $table->timestamp('completed_at')->nullable(); // When campaign completed
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('template_id')->references('id')->on('sms_templates')->onDelete('set null');
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
        Schema::dropIfExists('sms_campaigns');
    }
};
