<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Updates SMS and Email campaigns to reference communication_templates instead of
     * sms_templates and email_templates. Note: Historical campaigns may still have
     * old template IDs, but new campaigns will use communication_templates.
     */
    public function up(): void
    {
        // Drop SMS Campaigns foreign key constraint if it exists
        $smsConstraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'sms_campaigns' 
            AND COLUMN_NAME = 'template_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($smsConstraints as $constraint) {
            Schema::table('sms_campaigns', function (Blueprint $table) use ($constraint) {
                $table->dropForeign($constraint->CONSTRAINT_NAME);
            });
        }

        // Drop Email Campaigns foreign key constraint if it exists
        $emailConstraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'email_campaigns' 
            AND COLUMN_NAME = 'template_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($emailConstraints as $constraint) {
            Schema::table('email_campaigns', function (Blueprint $table) use ($constraint) {
                $table->dropForeign($constraint->CONSTRAINT_NAME);
            });
        }

        // Note: We don't change the column type or add new foreign key here
        // because existing campaigns may reference old templates.
        // The application code now uses CommunicationTemplate, and new campaigns
        // will reference communication_templates.id
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore SMS Campaigns foreign key
        Schema::table('sms_campaigns', function (Blueprint $table) {
            $table->foreign('template_id')->references('id')->on('sms_templates')->onDelete('set null');
        });

        // Restore Email Campaigns foreign key
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->foreign('template_id')->references('id')->on('email_templates')->onDelete('set null');
        });
    }
};
