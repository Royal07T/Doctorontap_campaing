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
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->boolean('email_sent')->default(false)->after('notes');
            $table->timestamp('email_sent_at')->nullable()->after('email_sent');
            $table->boolean('is_walk_in')->default(false)->after('email_sent_at')->comment('Walk-in patient at event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->dropColumn(['email_sent', 'email_sent_at', 'is_walk_in']);
        });
    }
};
