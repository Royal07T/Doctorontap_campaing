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
            $table->boolean('booked_by_customer_service')->default(false)->after('customer_care_id');
            $table->foreignId('booked_by_agent_id')->nullable()->after('booked_by_customer_service')->constrained('customer_cares')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['booked_by_agent_id']);
            $table->dropColumn(['booked_by_customer_service', 'booked_by_agent_id']);
        });
    }
};
