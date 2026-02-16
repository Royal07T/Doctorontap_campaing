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
        Schema::create('communication_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('channel', ['sms', 'email', 'whatsapp']);
            $table->string('subject')->nullable(); // For email
            $table->text('body');
            $table->json('variables')->nullable(); // Store placeholder variables like {{first_name}}
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->constrained('admin_users')->onDelete('restrict'); // Admin or Super Admin can create
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('channel');
            $table->index('active');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_templates');
    }
};
