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
        Schema::create('doctor_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('account_type')->nullable(); // savings, current, etc.
            $table->string('bank_code')->nullable();
            $table->string('swift_code')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->boolean('is_default')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Ensure only one default bank account per doctor
            $table->unique(['doctor_id', 'is_default'], 'unique_default_bank_per_doctor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_bank_accounts');
    }
};
