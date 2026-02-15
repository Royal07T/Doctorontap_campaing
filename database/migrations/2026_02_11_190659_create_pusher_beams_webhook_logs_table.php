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
        Schema::create('pusher_beams_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->nullable()->index();
            $table->string('event_id')->nullable()->unique();
            $table->string('instance_id')->nullable()->index();
            $table->string('publish_id')->nullable()->index();
            $table->string('user_id')->nullable()->index();
            $table->json('raw_data')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['event_type', 'created_at']);
            $table->index(['publish_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pusher_beams_webhook_logs');
    }
};
