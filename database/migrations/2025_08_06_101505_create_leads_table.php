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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('car_model');
            $table->string('client_name');
            $table->string('client_phone');
            $table->string('client_email')->nullable();
            $table->enum('purchase_type', ['Cash', 'Finance']);
            $table->decimal('client_budget', 10, 2);
            $table->boolean('follow_up_required')->default(false);
            $table->enum('status', ['Active', 'Closed', 'Unsuccessful'])->default('Active');
            $table->unsignedBigInteger('salesperson_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('salesperson_id')->references('id')->on('users');
            $table->index(['status', 'salesperson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
