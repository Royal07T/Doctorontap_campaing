<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add default setting for additional child charge percentage
        // Only insert if it doesn't already exist
        $existing = DB::table('settings')->where('key', 'additional_child_discount_percentage')->first();
        
        if (!$existing) {
            DB::table('settings')->insert([
                'key' => 'additional_child_discount_percentage',
                'value' => '60',
                'type' => 'decimal',
                'group' => 'pricing',
                'description' => 'Additional charge percentage for additional children in multi-patient bookings (added to base fee)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'additional_child_discount_percentage')->delete();
    }
};
