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
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id');
            
            // Schedule Details
            $table->integer('installment_number');
            $table->date('due_date');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            
            // Payment Status
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue'])->default('pending');
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->date('date_paid')->nullable();
            $table->integer('days_overdue')->default(0);
            
            $table->timestamps();
            
            // Indexes - Fixed for MySQL key length limit
            $table->index(['agreement_id', 'installment_number']);
            $table->index(['due_date', 'status']);
            $table->index(['status', 'days_overdue']);
            $table->index('agreement_id');
            $table->index('due_date');
            
            // Foreign Keys
            $table->foreign('agreement_id')->references('id')->on('hire_purchase_agreements')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_schedules');
    }
};
