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
        Schema::table('doctors', function (Blueprint $table) {
            // Add availability schedule fields
            $table->json('availability_schedule')->nullable()->after('days_of_availability');
            $table->time('availability_start_time')->nullable()->after('availability_schedule');
            $table->time('availability_end_time')->nullable()->after('availability_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['availability_schedule', 'availability_start_time', 'availability_end_time']);
        });
    }
};
