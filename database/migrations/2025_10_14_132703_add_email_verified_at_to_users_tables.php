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
        // Add email_verified_at to doctors table
        Schema::table('doctors', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        // Add email_verified_at to canvassers table
        Schema::table('canvassers', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        // Add email_verified_at to nurses table
        Schema::table('nurses', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });

        Schema::table('canvassers', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });

        Schema::table('nurses', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });
    }
};
