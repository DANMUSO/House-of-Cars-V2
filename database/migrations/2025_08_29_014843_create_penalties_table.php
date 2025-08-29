<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up()
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->string('agreement_type'); // 'hire_purchase' or 'gentleman'
            $table->unsignedBigInteger('agreement_id');
            $table->unsignedBigInteger('payment_schedule_id')->nullable();
            $table->decimal('expected_amount', 15, 2);
            $table->decimal('penalty_rate', 5, 2)->default(10.00); // 10% default
            $table->decimal('penalty_amount', 15, 2);
            $table->date('due_date');
            $table->integer('days_overdue');
            $table->enum('status', ['pending', 'paid', 'waived'])->default('pending');
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->date('date_paid')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('waived_by')->nullable();
            $table->timestamp('waived_at')->nullable();
            $table->text('waiver_reason')->nullable();
            $table->timestamps();
            
            // Add indexes
            $table->index(['agreement_type', 'agreement_id']);
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('penalties');
    }
};