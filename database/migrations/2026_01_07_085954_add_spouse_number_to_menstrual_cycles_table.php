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
        Schema::table('menstrual_cycles', function (Blueprint $table) {
            $table->string('spouse_number')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menstrual_cycles', function (Blueprint $table) {
            $table->dropColumn('spouse_number');
        });
    }
};
