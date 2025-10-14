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
            $table->foreignId('canvasser_id')->nullable()->after('doctor_id')->constrained('canvassers')->onDelete('set null');
            $table->foreignId('nurse_id')->nullable()->after('canvasser_id')->constrained('nurses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['canvasser_id']);
            $table->dropForeign(['nurse_id']);
            $table->dropColumn(['canvasser_id', 'nurse_id']);
        });
    }
};
