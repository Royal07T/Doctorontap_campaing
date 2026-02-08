<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add second opinion capabilities to doctors table
        Schema::table('doctors', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('doctors', 'can_provide_second_opinion')) {
                $table->boolean('can_provide_second_opinion')->default(true)->after('is_available');
            }
            if (!Schema::hasColumn('doctors', 'is_international')) {
                $table->boolean('is_international')->default(false)->after('can_provide_second_opinion');
            }
            if (!Schema::hasColumn('doctors', 'country_of_practice')) {
                $table->string('country_of_practice')->nullable()->after('is_international');
            }
            if (!Schema::hasColumn('doctors', 'license_restrictions')) {
                $table->text('license_restrictions')->nullable()->after('country_of_practice');
            }
        });

        // Add consultation type and related fields to consultations table
        Schema::table('consultations', function (Blueprint $table) {
            if (!Schema::hasColumn('consultations', 'service_type')) {
                $table->enum('service_type', ['full_consultation', 'second_opinion'])->default('full_consultation')->after('consultation_mode');
            }
            if (!Schema::hasColumn('consultations', 'can_escalate_to_full')) {
                $table->boolean('can_escalate_to_full')->default(false)->after('service_type');
            }
            if (!Schema::hasColumn('consultations', 'escalated_from_consultation_id')) {
                $table->foreignId('escalated_from_consultation_id')->nullable()->constrained('consultations')->onDelete('set null')->after('can_escalate_to_full');
            }
            if (!Schema::hasColumn('consultations', 'escalated_at')) {
                $table->timestamp('escalated_at')->nullable()->after('escalated_from_consultation_id');
            }
            if (!Schema::hasColumn('consultations', 'second_opinion_notes')) {
                $table->text('second_opinion_notes')->nullable()->after('escalated_at');
            }
            if (!Schema::hasColumn('consultations', 'second_opinion_documents')) {
                $table->json('second_opinion_documents')->nullable()->after('second_opinion_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            if (Schema::hasColumn('doctors', 'can_provide_second_opinion')) {
                $table->dropColumn('can_provide_second_opinion');
            }
            if (Schema::hasColumn('doctors', 'is_international')) {
                $table->dropColumn('is_international');
            }
            if (Schema::hasColumn('doctors', 'country_of_practice')) {
                $table->dropColumn('country_of_practice');
            }
            if (Schema::hasColumn('doctors', 'license_restrictions')) {
                $table->dropColumn('license_restrictions');
            }
        });

        Schema::table('consultations', function (Blueprint $table) {
            // Drop foreign key first
            if (Schema::hasColumn('consultations', 'escalated_from_consultation_id')) {
                $table->dropForeign(['escalated_from_consultation_id']);
            }
            
            if (Schema::hasColumn('consultations', 'service_type')) {
                $table->dropColumn('service_type');
            }
            if (Schema::hasColumn('consultations', 'can_escalate_to_full')) {
                $table->dropColumn('can_escalate_to_full');
            }
            if (Schema::hasColumn('consultations', 'escalated_from_consultation_id')) {
                $table->dropColumn('escalated_from_consultation_id');
            }
            if (Schema::hasColumn('consultations', 'escalated_at')) {
                $table->dropColumn('escalated_at');
            }
            if (Schema::hasColumn('consultations', 'second_opinion_notes')) {
                $table->dropColumn('second_opinion_notes');
            }
            if (Schema::hasColumn('consultations', 'second_opinion_documents')) {
                $table->dropColumn('second_opinion_documents');
            }
        });
    }
};
