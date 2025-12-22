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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Bank name (e.g., "Access Bank", "Zenith Bank")
            $table->string('code', 10)->unique(); // Bank code (e.g., "044", "057")
            $table->string('slug')->unique(); // URL-friendly identifier
            $table->boolean('is_active')->default(true); // Whether bank is currently active
            $table->integer('sort_order')->default(0); // For custom ordering
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
