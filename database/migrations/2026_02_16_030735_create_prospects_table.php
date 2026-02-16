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
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('mobile_number'); // Required
            $table->text('location')->nullable();
            $table->enum('source', ['call', 'booth', 'referral', 'website', 'other'])->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['New', 'Contacted', 'Converted', 'Closed'])->default('New');
            $table->foreignId('created_by')->constrained('customer_cares')->onDelete('restrict');
            $table->boolean('silent_prospect')->default(true); // Internal flag
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('status');
            $table->index('mobile_number');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
