<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Stores inbound messages from Vonage (SMS, WhatsApp, etc.)
     */
    public function up(): void
    {
        Schema::create('inbound_messages', function (Blueprint $table) {
            $table->id();
            
            // Message identification
            $table->string('message_uuid')->nullable()->unique()
                  ->comment('Vonage message UUID (Messages API)');
            $table->string('message_id')->nullable()
                  ->comment('Vonage message ID (Legacy API)');
            
            // Channel information
            $table->string('channel')->default('sms')
                  ->comment('Channel: sms, whatsapp, mms, etc.');
            $table->string('message_type')->default('text')
                  ->comment('Type: text, image, video, audio, file, location, etc.');
            
            // Sender and recipient
            $table->string('from_number')->index()
                  ->comment('Sender phone number');
            $table->string('to_number')->index()
                  ->comment('Recipient phone number (your number)');
            
            // Message content
            $table->text('message_text')->nullable()
                  ->comment('Text content of the message');
            $table->string('media_url')->nullable()
                  ->comment('URL to media file (image, video, audio, file)');
            $table->string('media_type')->nullable()
                  ->comment('MIME type of media');
            $table->string('media_caption')->nullable()
                  ->comment('Caption for media');
            $table->string('media_name')->nullable()
                  ->comment('Filename for media');
            
            // Location data (if message type is location)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->string('location_address')->nullable();
            
            // Contact data (if message type is contact)
            $table->json('contact_data')->nullable()
                  ->comment('Contact card data (name, phone, etc.)');
            
            // Status and metadata
            $table->string('status')->default('received')
                  ->comment('Status: received, processed, replied, failed');
            $table->timestamp('received_at')->useCurrent()
                  ->comment('When message was received');
            $table->timestamp('processed_at')->nullable()
                  ->comment('When message was processed');
            
            // Raw webhook data
            $table->json('raw_data')->nullable()
                  ->comment('Complete raw webhook payload');
            
            // Linking to consultations/patients (optional)
            $table->foreignId('consultation_id')->nullable()
                  ->constrained('consultations')
                  ->onDelete('set null')
                  ->comment('Linked consultation if applicable');
            $table->foreignId('patient_id')->nullable()
                  ->constrained('patients')
                  ->onDelete('set null')
                  ->comment('Linked patient if applicable');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['from_number', 'received_at']);
            $table->index(['channel', 'message_type']);
            $table->index('status');
            $table->index('consultation_id');
            $table->index('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_messages');
    }
};
