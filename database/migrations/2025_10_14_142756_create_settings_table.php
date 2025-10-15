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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, number, boolean, json
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'default_consultation_fee',
                'value' => '5000',
                'type' => 'number',
                'group' => 'pricing',
                'description' => 'Default consultation fee for all doctors (in Naira)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'use_default_fee_for_all',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'pricing',
                'description' => 'Force all doctors to use the default consultation fee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
