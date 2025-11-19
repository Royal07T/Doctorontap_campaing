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
        Schema::table('consultations', function (Blueprint $table) {
            // WhatsApp conversation tracking
            $table->timestamp('whatsapp_last_message_at')->nullable()->after('updated_at');
            $table->boolean('whatsapp_window_open')->default(false)->after('whatsapp_last_message_at');
            $table->timestamp('whatsapp_window_expires_at')->nullable()->after('whatsapp_window_open');
            
            // Track last incoming message for auto-reply context
            $table->text('whatsapp_last_message')->nullable()->after('whatsapp_window_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_last_message_at',
                'whatsapp_window_open',
                'whatsapp_window_expires_at',
                'whatsapp_last_message'
            ]);
        });
    }
};
