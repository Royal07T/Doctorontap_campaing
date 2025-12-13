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
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('guardian_id')->nullable()->after('id')->constrained('patients')->onDelete('set null');
            $table->date('date_of_birth')->nullable()->after('age');
            $table->boolean('is_minor')->default(false)->after('date_of_birth');
            
            $table->index('guardian_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['guardian_id']);
            $table->dropIndex(['guardian_id']);
            $table->dropColumn(['guardian_id', 'date_of_birth', 'is_minor']);
        });
    }
};

