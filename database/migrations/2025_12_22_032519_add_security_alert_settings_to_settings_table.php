<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert security alert settings
        DB::table('settings')->insert([
            [
                'key' => 'security_alerts_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Enable or disable security alert emails',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'security_alert_emails',
                'value' => json_encode(['admin@doctorontap.com']),
                'type' => 'json',
                'group' => 'security',
                'description' => 'List of email addresses to receive security alerts',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'security_alert_severities',
                'value' => json_encode(['critical', 'high']),
                'type' => 'json',
                'group' => 'security',
                'description' => 'Security alert severities that trigger email notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'security_alert_threshold_critical',
                'value' => '1',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Number of critical events per hour to trigger alert',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'security_alert_threshold_high',
                'value' => '5',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Number of high severity events per hour to trigger alert',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'security_alerts_enabled',
            'security_alert_emails',
            'security_alert_severities',
            'security_alert_threshold_critical',
            'security_alert_threshold_high',
        ])->delete();
    }
};
