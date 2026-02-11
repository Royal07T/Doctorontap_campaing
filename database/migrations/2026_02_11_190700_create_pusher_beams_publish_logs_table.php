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
        Schema::create('pusher_beams_publish_logs', function (Blueprint $table) {
            $table->id();
            $table->string('publish_id')->unique()->index();
            $table->string('instance_id')->nullable()->index();
            $table->string('event_type')->default('v1.PublishToUsersAttempt');
            $table->json('users_delivered')->nullable();
            $table->json('users_no_devices')->nullable();
            $table->json('users_gateway_failed')->nullable();
            $table->unsignedInteger('users_delivered_count')->default(0);
            $table->unsignedInteger('users_no_devices_count')->default(0);
            $table->unsignedInteger('users_failed_count')->default(0);
            $table->json('raw_data')->nullable();
            $table->timestamps();
            
            $table->index(['instance_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pusher_beams_publish_logs');
    }
};
