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
        // Add soft deletes to doctors table
        Schema::table('doctors', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to consultations table
        Schema::table('consultations', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to canvassers table
        Schema::table('canvassers', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to nurses table
        Schema::table('nurses', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to patients table
        Schema::table('patients', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to vital_signs table
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to admin_users table
        Schema::table('admin_users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove soft deletes from doctors table
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from consultations table
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from canvassers table
        Schema::table('canvassers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from nurses table
        Schema::table('nurses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from patients table
        Schema::table('patients', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from vital_signs table
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from admin_users table
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
