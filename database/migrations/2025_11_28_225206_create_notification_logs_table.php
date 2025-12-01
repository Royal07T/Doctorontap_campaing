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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            
            // Related Consultation
            $table->foreignId('consultation_id')->constrained('consultations')->onDelete('cascade');
            $table->string('consultation_reference')->index();
            
            // Notification Details
            $table->string('type'); // email, sms, whatsapp
            $table->string('category'); // treatment_plan, payment_request, confirmation, status_change
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            
            // Recipient
            $table->string('recipient'); // email address or phone number
            $table->string('recipient_name')->nullable();
            
            // Delivery Status
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'bounced'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            // External Provider Info
            $table->string('provider')->nullable(); // termii, smtp, etc
            $table->string('provider_message_id')->nullable();
            $table->text('provider_response')->nullable();
            
            // Error Tracking
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable(); // Additional data like attachments, etc
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['consultation_id', 'type', 'category']);
            $table->index(['status', 'created_at']);
            $table->index('sent_at');
        });
        
        // Add notification tracking fields to consultations table
        Schema::table('consultations', function (Blueprint $table) {
            // Treatment Plan Email Tracking
            $table->boolean('treatment_plan_email_sent')->default(false)->after('treatment_plan_unlocked_at');
            $table->timestamp('treatment_plan_email_sent_at')->nullable()->after('treatment_plan_email_sent');
            $table->enum('treatment_plan_email_status', ['pending', 'sent', 'delivered', 'failed'])->default('pending')->after('treatment_plan_email_sent_at');
            
            // Treatment Plan SMS Tracking
            $table->boolean('treatment_plan_sms_sent')->default(false)->after('treatment_plan_email_status');
            $table->timestamp('treatment_plan_sms_sent_at')->nullable()->after('treatment_plan_sms_sent');
            $table->enum('treatment_plan_sms_status', ['pending', 'sent', 'delivered', 'failed'])->default('pending')->after('treatment_plan_sms_sent_at');
            
            // Last notification attempt tracking
            $table->timestamp('last_notification_attempt')->nullable()->after('treatment_plan_sms_status');
            $table->integer('notification_failure_count')->default(0)->after('last_notification_attempt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn([
                'treatment_plan_email_sent',
                'treatment_plan_email_sent_at',
                'treatment_plan_email_status',
                'treatment_plan_sms_sent',
                'treatment_plan_sms_sent_at',
                'treatment_plan_sms_status',
                'last_notification_attempt',
                'notification_failure_count',
            ]);
        });
        
        Schema::dropIfExists('notification_logs');
    }
};
