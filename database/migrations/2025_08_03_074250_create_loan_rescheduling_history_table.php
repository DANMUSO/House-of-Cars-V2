<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loan_rescheduling_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id');
            $table->unsignedBigInteger('payment_id')->nullable(); // Associated lump sum payment
            $table->enum('reschedule_type', ['reduce_duration', 'reduce_installment']);
            $table->decimal('lump_sum_amount', 15, 2);
            $table->decimal('outstanding_before', 15, 2);
            $table->decimal('outstanding_after', 15, 2);
            
            // Previous loan terms
            $table->integer('previous_duration_months');
            $table->decimal('previous_monthly_payment', 15, 2);
            $table->date('previous_completion_date')->nullable();
            
            // New loan terms
            $table->integer('new_duration_months');
            $table->decimal('new_monthly_payment', 15, 2);
            $table->date('new_completion_date')->nullable();
            
            // Savings/benefits
            $table->integer('duration_change_months')->nullable();
            $table->decimal('payment_change_amount', 15, 2)->nullable();
            $table->decimal('total_interest_savings', 15, 2)->nullable();
            
            // Rescheduling details
            $table->date('rescheduling_date');
            $table->unsignedBigInteger('processed_by');
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'superseded'])->default('active');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['agreement_id', 'status']);
            $table->index('rescheduling_date');
            $table->index('processed_by');
            
            // Foreign key constraints
            $table->foreign('agreement_id')->references('id')->on('hire_purchase_agreements')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_rescheduling_history');
    }
};
