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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('updated_at');
        });
        
        Schema::table('doctors', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('last_login_at');
        });
        
        Schema::table('canvassers', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('updated_at');
        });
        
        Schema::table('nurses', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('updated_at');
        });
        
        Schema::table('admin_users', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_activity_at');
        });
        
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('last_activity_at');
        });
        
        Schema::table('canvassers', function (Blueprint $table) {
            $table->dropColumn('last_activity_at');
        });
        
        Schema::table('nurses', function (Blueprint $table) {
            $table->dropColumn('last_activity_at');
        });
        
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('last_activity_at');
        });
    }
};