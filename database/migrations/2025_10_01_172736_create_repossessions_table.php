<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepossessionsTable extends Migration
{
    public function up()
    {
        Schema::create('repossessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id');
            $table->string('agreement_type'); // 'hire_purchase' or 'gentleman_agreement'
            $table->date('repossession_date');
            $table->decimal('remaining_balance', 15, 2);
            $table->decimal('total_penalties', 15, 2)->default(0);
            $table->decimal('repossession_expenses', 15, 2);
            $table->decimal('car_value', 15, 2); // Sum of above three
            $table->decimal('expected_sale_price', 15, 2)->nullable();
            $table->decimal('actual_sale_price', 15, 2)->nullable();
            $table->date('sale_date')->nullable();
            $table->enum('status', ['repossessed', 'pending_sale', 'sold', 'returned'])->default('repossessed');
            $table->text('repossession_reason')->nullable();
            $table->text('vehicle_condition')->nullable();
            $table->text('repossession_notes')->nullable();
            $table->string('storage_location')->nullable();
            $table->unsignedBigInteger('repossessed_by'); // User ID
            $table->unsignedBigInteger('sold_by')->nullable(); // User ID
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('repossessed_by')->references('id')->on('users');
            $table->foreign('sold_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repossessions');
    }
}