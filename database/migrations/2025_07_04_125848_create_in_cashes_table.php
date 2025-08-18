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
        Schema::create('in_cashes', function (Blueprint $table) {
            $table->id();
            $table->string('Client_Name');
            $table->string('Phone_No');
            $table->string('email');
            $table->string('KRA');
            $table->string('National_ID');
            $table->decimal('Amount', 15, 2);
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
        Schema::dropIfExists('in_cashes');
    }
};
