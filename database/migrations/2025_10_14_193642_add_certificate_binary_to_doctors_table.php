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
            $table->longText('certificate_data')->nullable()->after('certificate_path')->comment('Binary file content stored as base64');
            $table->string('certificate_mime_type')->nullable()->after('certificate_data')->comment('MIME type of certificate file');
            $table->string('certificate_original_name')->nullable()->after('certificate_mime_type')->comment('Original filename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['certificate_data', 'certificate_mime_type', 'certificate_original_name']);
        });
    }
};
