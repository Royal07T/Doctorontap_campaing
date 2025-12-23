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
        // Add new consultation fee settings
        $settings = [
            [
                'key' => 'consultation_fee_pay_later',
                'value' => '5000',
                'description' => 'Consultation fee for Pay Later type (Consult Now, Pay Later)'
            ],
            [
                'key' => 'consultation_fee_pay_now',
                'value' => '4500',
                'description' => 'Consultation fee for Pay Now type (Pay Before Consultation) - Discounted for upfront payment'
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'description' => $setting['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'consultation_fee_pay_later',
            'consultation_fee_pay_now',
        ])->delete();
    }
};
