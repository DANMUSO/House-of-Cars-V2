<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenaltyPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penalty_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penalty_id');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mpesa', 'cheque', 'card']);
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('penalty_id')->references('id')->on('penalties')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users');
            $table->foreign('verified_by')->references('id')->on('users')->nullable();
            
            // Indexes
            $table->index('penalty_id');
            $table->index('payment_date');
            $table->index('recorded_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penalty_payments');
    }
}