<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_room_archives', function (Blueprint $table) {
            $table->id();

            $table->foreignId('video_room_id')
                ->constrained('video_rooms')
                ->cascadeOnDelete();

            $table->string('vonage_archive_id')->unique();
            $table->string('status')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->unsignedInteger('duration')->nullable();

            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('download_url')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['video_room_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_room_archives');
    }
};
