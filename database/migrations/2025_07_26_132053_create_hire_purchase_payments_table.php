<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('hire_purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id');
            
            // Payment Details
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mpesa', 'cheque', 'card']);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            
            // Payment Classification
            $table->enum('payment_type', ['regular', 'late', 'early', 'penalty', 'partial', 'final'])->default('regular');
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->integer('payment_number'); // which payment in sequence
            
            // Tracking
            $table->unsignedBigInteger('recorded_by');
            $table->timestamp('recorded_at')->useCurrent();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_lump_sum')->default(false); // Removed ->after('is_verified')
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            
            // Balance after payment
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            
            $table->timestamps();
            
            // Indexes - Fixed for MySQL key length limit
            $table->index(['agreement_id', 'payment_date']);
            $table->index(['payment_method', 'payment_date']);
            $table->index(['payment_type', 'created_at']);
            $table->index('recorded_by');
            $table->index('payment_number');
            
            // Foreign Keys
            $table->foreign('agreement_id')->references('id')->on('hire_purchase_agreements')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hire_purchase_payments');
    }
};