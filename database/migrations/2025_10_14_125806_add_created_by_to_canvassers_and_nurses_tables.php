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
        Schema::table('canvassers', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('is_active')->constrained('admin_users')->onDelete('set null');
        });

        Schema::table('nurses', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('is_active')->constrained('admin_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('canvassers', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });

        Schema::table('nurses', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
