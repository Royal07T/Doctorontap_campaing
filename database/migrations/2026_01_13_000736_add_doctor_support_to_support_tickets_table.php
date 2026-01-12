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
        Schema::table('support_tickets', function (Blueprint $table) {
            // Drop existing foreign key constraint first
            $table->dropForeign(['user_id']);
            
            // Make user_id nullable to support both patients and doctors
            $table->foreignId('user_id')->nullable()->change();
            
            // Re-add foreign key constraint (nullable)
            $table->foreign('user_id')->references('id')->on('patients')->onDelete('cascade');
        });
        
        Schema::table('support_tickets', function (Blueprint $table) {
            // Add user_type to distinguish between patient and doctor
            $table->enum('user_type', ['patient', 'doctor'])->default('patient')->after('user_id');
            
            // Add doctor_id for doctor tickets
            $table->foreignId('doctor_id')->nullable()->after('user_type')->constrained('doctors')->onDelete('cascade');
            
            // Update indexes
            $table->index(['user_type', 'user_id', 'status']);
            $table->index(['user_type', 'doctor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            // Drop new columns and indexes
            $table->dropIndex(['user_type', 'user_id', 'status']);
            $table->dropIndex(['user_type', 'doctor_id', 'status']);
            $table->dropForeign(['doctor_id']);
            $table->dropColumn(['user_type', 'doctor_id']);
        });
        
        Schema::table('support_tickets', function (Blueprint $table) {
            // Drop and restore user_id constraint
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('patients')->onDelete('cascade');
        });
    }
};
