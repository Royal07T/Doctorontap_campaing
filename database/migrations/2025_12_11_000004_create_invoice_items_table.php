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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('consultation_id')->nullable()->constrained('consultations')->onDelete('set null');
            
            // Item details
            $table->string('description', 500);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('adjustment', 10, 2)->default(0.00);
            $table->text('adjustment_reason')->nullable();
            $table->decimal('total_price', 10, 2);
            
            // Metadata
            $table->string('item_type')->default('consultation'); // consultation, medication, lab_test, etc.
            $table->integer('order_index')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_id');
            $table->index('patient_id');
            $table->index('consultation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};

