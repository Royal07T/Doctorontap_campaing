<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OPTIMIZATION: Add performance indexes for frequently queried columns
 * Improves query performance for dashboard statistics and filtering operations
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Index for status filtering (used in dashboard statistics)
            if (!$this->indexExists('consultations', 'consultations_status_index')) {
                $table->index('status', 'consultations_status_index');
            }
            
            // Composite index for status + payment_status (common filter combination)
            if (!$this->indexExists('consultations', 'consultations_status_payment_index')) {
                $table->index(['status', 'payment_status'], 'consultations_status_payment_index');
            }
            
            // Index for payment_status filtering
            if (!$this->indexExists('consultations', 'consultations_payment_status_index')) {
                $table->index('payment_status', 'consultations_payment_status_index');
            }
            
            // Composite index for doctor_id + status (doctor dashboard queries)
            if (!$this->indexExists('consultations', 'consultations_doctor_status_index')) {
                $table->index(['doctor_id', 'status'], 'consultations_doctor_status_index');
            }
            
            // Index for created_at date filtering (recent consultations, date ranges)
            if (!$this->indexExists('consultations', 'consultations_created_at_index')) {
                $table->index('created_at', 'consultations_created_at_index');
            }
            
            // Composite index for created_at + status (time-based status filtering)
            if (!$this->indexExists('consultations', 'consultations_created_status_index')) {
                $table->index(['created_at', 'status'], 'consultations_created_status_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropIndex('consultations_status_index');
            $table->dropIndex('consultations_status_payment_index');
            $table->dropIndex('consultations_payment_status_index');
            $table->dropIndex('consultations_doctor_status_index');
            $table->dropIndex('consultations_created_at_index');
            $table->dropIndex('consultations_created_status_index');
        });
    }

    /**
     * Check if an index exists on a table
     */
    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        
        $result = $connection->select(
            "SELECT COUNT(*) as count 
             FROM information_schema.statistics 
             WHERE table_schema = ? 
             AND table_name = ? 
             AND index_name = ?",
            [$databaseName, $table, $index]
        );
        
        return $result[0]->count > 0;
    }
};
