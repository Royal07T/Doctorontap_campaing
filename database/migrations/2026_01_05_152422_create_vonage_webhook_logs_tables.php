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
        // Table for inbound SMS messages
        Schema::create('sms_inbound_logs', function (Blueprint $table) {
            $table->id();
            $table->string('from', 20)->nullable()->index();
            $table->string('to', 20)->nullable()->index();
            $table->text('message')->nullable();
            $table->string('message_id', 100)->nullable()->index();
            $table->timestamp('timestamp')->nullable();
            $table->string('type', 50)->default('text');
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });

        // Table for SMS delivery status updates
        Schema::create('sms_status_logs', function (Blueprint $table) {
            $table->id();
            $table->string('message_id', 100)->nullable()->index();
            $table->string('status', 50)->nullable()->index();
            $table->string('error_code', 50)->nullable();
            $table->string('network', 50)->nullable();
            $table->decimal('price', 10, 4)->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_inbound_logs');
        Schema::dropIfExists('sms_status_logs');
    }
};
