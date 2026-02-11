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
        Schema::create('pusher_beams_opens', function (Blueprint $table) {
            $table->id();
            $table->string('publish_id')->index();
            $table->string('user_id')->index();
            $table->string('instance_id')->nullable()->index();
            $table->string('event_type')->default('v1.UserNotificationOpen');
            $table->json('raw_data')->nullable();
            $table->timestamps();
            
            $table->index(['publish_id', 'user_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pusher_beams_opens');
    }
};
