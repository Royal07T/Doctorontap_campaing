<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Stores chat messages for consultation sessions
     */
    public function up(): void
    {
        Schema::create('consultation_chat_messages', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to consultation_sessions
            $table->foreignId('consultation_session_id')
                  ->constrained('consultation_sessions')
                  ->onDelete('cascade');
            
            // Message content
            $table->text('message')->comment('Message text content');
            $table->string('message_type')->default('text')
                  ->comment('Type: text, image, file, system');
            
            // Sender information
            $table->string('sender_type')->comment('doctor or patient');
            $table->unsignedBigInteger('sender_id')->nullable()
                  ->comment('Doctor ID or Patient ID');
            $table->string('sender_name')->comment('Display name of sender');
            
            // Vonage message ID (for tracking)
            $table->string('vonage_message_id')->nullable()->unique()
                  ->comment('Vonage Conversations API message ID');
            
            // File attachments (for images/files)
            $table->string('file_url')->nullable()
                  ->comment('URL to uploaded file/image');
            $table->string('file_name')->nullable()
                  ->comment('Original filename');
            $table->string('file_type')->nullable()
                  ->comment('MIME type of file');
            $table->unsignedBigInteger('file_size')->nullable()
                  ->comment('File size in bytes');
            
            // Message metadata
            $table->boolean('is_read')->default(false)
                  ->comment('Whether message has been read');
            $table->timestamp('read_at')->nullable();
            
            // Timestamps
            $table->timestamp('sent_at')->useCurrent()
                  ->comment('When message was sent');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('consultation_session_id');
            $table->index(['consultation_session_id', 'sent_at']);
            $table->index('sender_type');
            $table->index('vonage_message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_chat_messages');
    }
};
