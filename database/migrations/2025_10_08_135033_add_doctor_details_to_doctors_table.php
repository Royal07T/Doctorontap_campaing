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
            $table->string('phone')->nullable()->after('name');
            $table->string('email')->nullable()->after('phone');
            $table->string('gender')->nullable()->after('email');
            $table->decimal('consultation_fee', 10, 2)->nullable()->after('specialization');
            $table->string('location')->nullable()->after('consultation_fee');
            $table->string('experience')->nullable()->after('location');
            $table->string('languages')->nullable()->after('experience');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email', 'gender', 'consultation_fee', 'location', 'experience', 'languages']);
        });
    }
};
