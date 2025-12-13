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
            $table->foreignId('booking_id')->nullable()->after('patient_id')->constrained('bookings')->onDelete('set null');
            $table->boolean('is_multi_patient_booking')->default(false)->after('booking_id');
            
            $table->index('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropIndex(['booking_id']);
            $table->dropColumn(['booking_id', 'is_multi_patient_booking']);
        });
    }
};

