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
        Schema::create('hire_purchases', function (Blueprint $table) {
            $table->id();
            $table->integer('imported_id');
            $table->integer('customer_id');
            $table->integer('status')->default(0);
            $table->string('Client_Name');
            $table->string('Phone_No');
            $table->string('email');
            $table->string('KRA');
            $table->string('National_ID');
            $table->decimal('Amount', 15, 2);
            $table->decimal('deposit', 15, 2);
            $table->integer('duration');
            $table->integer('paid_percentage');
            $table->string('car_type'); // 'import' or 'customer'
            $table->unsignedBigInteger('car_id'); // actual ID from carsImport or customerVehicle
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hire_purchases');
    }
};
