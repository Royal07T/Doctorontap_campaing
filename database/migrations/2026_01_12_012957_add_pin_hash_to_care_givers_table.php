<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds PIN hash column for secure PIN-based access control.
     * PINs are hashed using Laravel Hash (bcrypt), NOT encrypted.
     */
    public function up(): void
    {
        Schema::table('care_givers', function (Blueprint $table) {
            $table->string('pin_hash')->nullable()->after('password')->comment('Hashed PIN for additional security layer');
            $table->index('pin_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('care_givers', function (Blueprint $table) {
            $table->dropIndex(['pin_hash']);
            $table->dropColumn('pin_hash');
        });
    }
};
