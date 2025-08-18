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
        
        Schema::create('fleet_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fleet_acquisition_id')->constrained('fleet_acquisitions')->onDelete('cascade');
            
            // Payment Details
            $table->decimal('payment_amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['bank_transfer', 'cheque', 'cash', 'mobile_money']);
            $table->string('reference_number')->nullable();
            
            // Balance Tracking
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->integer('payment_number');
            
            // Additional Details
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('confirmed');
            $table->string('processed_by')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['fleet_acquisition_id', 'payment_date']);
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_payments');
    }
};
