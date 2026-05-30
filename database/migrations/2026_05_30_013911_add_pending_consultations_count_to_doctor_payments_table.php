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
        Schema::table('doctor_payments', function (Blueprint $table) {
            $table->integer('pending_consultations_count')->default(0)->after('unpaid_consultations_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctor_payments', function (Blueprint $table) {
            $table->dropColumn('pending_consultations_count');
        });
    }
};
