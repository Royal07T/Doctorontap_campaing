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
        Schema::table('admin_users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'moderator', 'support'])
                  ->default('admin')
                  ->after('email');
            $table->json('permissions')->nullable()->after('role');
            $table->boolean('can_impersonate')->default(false)->after('permissions');
            $table->timestamp('last_impersonation_at')->nullable()->after('can_impersonate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn(['role', 'permissions', 'can_impersonate', 'last_impersonation_at']);
        });
    }
};
