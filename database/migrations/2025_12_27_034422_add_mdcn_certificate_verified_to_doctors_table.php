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
            $table->boolean('mdcn_certificate_verified')->default(false)->after('certificate_original_name')->comment('Admin verification flag for MDCN certificate');
            $table->timestamp('mdcn_certificate_verified_at')->nullable()->after('mdcn_certificate_verified');
            $table->foreignId('mdcn_certificate_verified_by')->nullable()->after('mdcn_certificate_verified_at')->constrained('admin_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['mdcn_certificate_verified_by']);
            $table->dropColumn(['mdcn_certificate_verified', 'mdcn_certificate_verified_at', 'mdcn_certificate_verified_by']);
        });
    }
};
