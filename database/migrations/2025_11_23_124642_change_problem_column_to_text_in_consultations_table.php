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
            // Change problem column from VARCHAR(255) to TEXT to accommodate longer descriptions
            $table->text('problem')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Revert back to string (VARCHAR 255) - note: data may be truncated if longer than 255 chars
            $table->string('problem')->change();
        });
    }
};
