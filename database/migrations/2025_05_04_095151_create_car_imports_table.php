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
        Schema::create('car_imports', function (Blueprint $table) {
            $table->id();
            $table->string('bidder_name');
            $table->string('make');
            $table->string('model');
            $table->year('year');
            $table->string('vin');
            $table->string('engine_type');
            $table->string('body_type');
            $table->integer('mileage');
            $table->decimal('bid_amount', 10, 2);
            $table->date('bid_start_date');
            $table->date('bid_end_date');
            $table->decimal('deposit', 10, 2)->default();
            $table->integer('status')->default('0');
            $table->json('photos')->nullable(); // Store photos as JSON array
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_imports');
    }
};
