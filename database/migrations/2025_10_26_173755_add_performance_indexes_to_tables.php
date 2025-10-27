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
        // Consultations table indexes - Most frequently queried
        Schema::table('consultations', function (Blueprint $table) {
            $table->index('reference', 'idx_consultations_reference');
            $table->index('email', 'idx_consultations_email');
            $table->index('status', 'idx_consultations_status');
            $table->index('payment_status', 'idx_consultations_payment_status');
            $table->index(['doctor_id', 'status'], 'idx_consultations_doctor_status');
            $table->index('created_at', 'idx_consultations_created_at');
        });

        // Doctors table indexes - For search and filtering
        Schema::table('doctors', function (Blueprint $table) {
            $table->index('is_available', 'idx_doctors_is_available');
            $table->index('is_approved', 'idx_doctors_is_approved');
            $table->index('first_name', 'idx_doctors_first_name');
            $table->index('last_name', 'idx_doctors_last_name');
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index('reference', 'idx_payments_reference');
            $table->index('status', 'idx_payments_status');
            $table->index('customer_email', 'idx_payments_customer_email');
        });

        // Reviews table indexes
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['reviewee_doctor_id', 'is_published'], 'idx_reviews_doctor_published');
            $table->index('rating', 'idx_reviews_rating');
        });

        // Nurses table indexes
        Schema::table('nurses', function (Blueprint $table) {
            $table->index('is_active', 'idx_nurses_is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropIndex('idx_consultations_reference');
            $table->dropIndex('idx_consultations_email');
            $table->dropIndex('idx_consultations_status');
            $table->dropIndex('idx_consultations_payment_status');
            $table->dropIndex('idx_consultations_doctor_status');
            $table->dropIndex('idx_consultations_created_at');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex('idx_doctors_is_available');
            $table->dropIndex('idx_doctors_is_approved');
            $table->dropIndex('idx_doctors_first_name');
            $table->dropIndex('idx_doctors_last_name');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_reference');
            $table->dropIndex('idx_payments_status');
            $table->dropIndex('idx_payments_customer_email');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_doctor_published');
            $table->dropIndex('idx_reviews_rating');
        });

        Schema::table('nurses', function (Blueprint $table) {
            $table->dropIndex('idx_nurses_is_active');
        });
    }
};
