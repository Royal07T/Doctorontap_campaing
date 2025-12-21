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
        Schema::table('booking_patients', function (Blueprint $table) {
            // Make fee columns nullable and remove default values
            // This allows multi-patient bookings to have blank fees until admin sets them
            $table->decimal('base_fee', 10, 2)->nullable()->default(null)->change();
            $table->decimal('adjusted_fee', 10, 2)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_patients', function (Blueprint $table) {
            // Revert to default 0.00
            $table->decimal('base_fee', 10, 2)->default(0.00)->change();
            $table->decimal('adjusted_fee', 10, 2)->default(0.00)->change();
        });
    }
};
