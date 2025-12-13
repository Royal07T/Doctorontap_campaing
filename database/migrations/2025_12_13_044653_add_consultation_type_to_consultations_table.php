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
        Schema::table('consultations', function (Blueprint $table) {
            $table->enum('consultation_type', ['pay_now', 'pay_later'])
                  ->default('pay_later')
                  ->after('consult_mode')
                  ->comment('Type of consultation: pay_now (payment before), pay_later (payment after)');
            
            $table->boolean('requires_payment_first')->default(false)->after('consultation_type');
            $table->timestamp('payment_completed_at')->nullable()->after('requires_payment_first');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['consultation_type', 'requires_payment_first', 'payment_completed_at']);
        });
    }
};
