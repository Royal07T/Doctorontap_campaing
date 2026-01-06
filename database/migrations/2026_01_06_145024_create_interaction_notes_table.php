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
        Schema::create('interaction_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_interaction_id')->constrained('customer_interactions')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('customer_cares')->onDelete('cascade');
            $table->text('note');
            $table->boolean('is_internal')->default(true); // Internal notes not visible to patients
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('customer_interaction_id');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interaction_notes');
    }
};
