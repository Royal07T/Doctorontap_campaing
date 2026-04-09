<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the leads table for capturing interest from
     * prospective users who haven't signed up yet.
     * Used by automated follow-up (Day 1 WhatsApp, Day 3 Email, Day 7 SMS).
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->nullable()->comment('website, referral, social, campaign, etc.');
            $table->enum('followup_stage', ['new', 'day1', 'day3', 'day7', 'converted', 'lost'])->default('new');
            $table->dateTime('last_contacted_at')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'converted', 'lost', 'unresponsive'])->default('active');
            $table->string('interest_type')->nullable()->comment('caregiver, patient, family, etc.');
            $table->foreignId('assigned_to')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('followup_stage');
            $table->index('status');
            $table->index('last_contacted_at');
            $table->index('source');
            $table->index(['email']);
            $table->index(['phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
