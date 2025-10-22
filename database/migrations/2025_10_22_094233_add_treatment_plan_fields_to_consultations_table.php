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
            // Treatment Plan Fields
            $table->text('diagnosis')->nullable()->after('doctor_notes');
            $table->text('treatment_plan')->nullable()->after('diagnosis');
            $table->json('prescribed_medications')->nullable()->after('treatment_plan');
            $table->text('follow_up_instructions')->nullable()->after('prescribed_medications');
            $table->text('lifestyle_recommendations')->nullable()->after('follow_up_instructions');
            $table->json('referrals')->nullable()->after('lifestyle_recommendations');
            $table->date('next_appointment_date')->nullable()->after('referrals');
            $table->text('additional_notes')->nullable()->after('next_appointment_date');
            
            // Treatment Plan Status
            $table->boolean('treatment_plan_created')->default(false)->after('additional_notes');
            $table->timestamp('treatment_plan_created_at')->nullable()->after('treatment_plan_created');
            $table->boolean('treatment_plan_accessible')->default(false)->after('treatment_plan_created_at');
            $table->timestamp('treatment_plan_accessed_at')->nullable()->after('treatment_plan_accessible');
            
            // Payment Gating
            $table->boolean('payment_required_for_treatment')->default(true)->after('treatment_plan_accessed_at');
            $table->boolean('treatment_plan_unlocked')->default(false)->after('payment_required_for_treatment');
            $table->timestamp('treatment_plan_unlocked_at')->nullable()->after('treatment_plan_unlocked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn([
                'diagnosis',
                'treatment_plan',
                'prescribed_medications',
                'follow_up_instructions',
                'lifestyle_recommendations',
                'referrals',
                'next_appointment_date',
                'additional_notes',
                'treatment_plan_created',
                'treatment_plan_created_at',
                'treatment_plan_accessible',
                'treatment_plan_accessed_at',
                'payment_required_for_treatment',
                'treatment_plan_unlocked',
                'treatment_plan_unlocked_at',
            ]);
        });
    }
};