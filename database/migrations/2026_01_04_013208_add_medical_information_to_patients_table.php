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
        Schema::table('patients', function (Blueprint $table) {
            // Blood Group and Genotype
            $table->string('blood_group')->nullable()->after('gender');
            $table->string('genotype')->nullable()->after('blood_group');
            
            // Medical Information
            $table->text('allergies')->nullable()->after('genotype');
            $table->text('chronic_conditions')->nullable()->after('allergies');
            $table->text('current_medications')->nullable()->after('chronic_conditions');
            $table->text('surgical_history')->nullable()->after('current_medications');
            $table->text('family_medical_history')->nullable()->after('surgical_history');
            $table->text('emergency_contact_name')->nullable()->after('family_medical_history');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            
            // Additional Medical Details
            $table->string('height')->nullable()->after('emergency_contact_relationship'); // in cm
            $table->string('weight')->nullable()->after('height'); // in kg
            $table->text('medical_notes')->nullable()->after('weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'blood_group',
                'genotype',
                'allergies',
                'chronic_conditions',
                'current_medications',
                'surgical_history',
                'family_medical_history',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
                'height',
                'weight',
                'medical_notes',
            ]);
        });
    }
};
