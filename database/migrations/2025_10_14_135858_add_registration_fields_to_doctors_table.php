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
        Schema::table('doctors', function (Blueprint $table) {
            // Split name into first_name and last_name
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            
            // Change consultation_fee to min and max range
            $table->decimal('min_consultation_fee', 10, 2)->nullable()->after('specialization');
            $table->decimal('max_consultation_fee', 10, 2)->nullable()->after('min_consultation_fee');
            
            // New fields
            $table->string('place_of_work')->nullable()->after('location');
            $table->enum('role', ['clinical', 'non-clinical'])->default('clinical')->after('place_of_work');
            $table->boolean('mdcn_license_current')->default(false)->after('role');
            $table->string('certificate_path')->nullable()->after('mdcn_license_current');
            
            // Approval fields
            $table->boolean('is_approved')->default(false)->after('is_available');
            $table->foreignId('approved_by')->nullable()->constrained('admin_users')->onDelete('set null')->after('is_approved');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'min_consultation_fee',
                'max_consultation_fee',
                'place_of_work',
                'role',
                'mdcn_license_current',
                'certificate_path',
                'is_approved',
                'approved_at'
            ]);
            
            $table->dropForeign(['approved_by']);
            $table->dropColumn('approved_by');
        });
    }
};
