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
            // Medical Format Fields - Proper Clinical Documentation
            $table->text('presenting_complaint')->nullable()->after('problem');
            $table->text('history_of_complaint')->nullable()->after('presenting_complaint');
            $table->text('past_medical_history')->nullable()->after('history_of_complaint');
            $table->text('family_history')->nullable()->after('past_medical_history');
            $table->text('drug_history')->nullable()->after('family_history');
            $table->text('social_history')->nullable()->after('drug_history');
            $table->text('investigation')->nullable()->after('treatment_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn([
                'presenting_complaint',
                'history_of_complaint',
                'past_medical_history',
                'family_history',
                'drug_history',
                'social_history',
                'investigation'
            ]);
        });
    }
};
