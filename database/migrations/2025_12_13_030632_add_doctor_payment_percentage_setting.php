<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add default doctor payment percentage setting
        Setting::updateOrCreate(
            ['key' => 'doctor_payment_percentage'],
            [
                'value' => '70',
                'type' => 'decimal',
                'group' => 'pricing',
                'description' => 'Default percentage of consultation fees that doctors receive (platform gets the remainder)'
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::where('key', 'doctor_payment_percentage')->delete();
    }
};
