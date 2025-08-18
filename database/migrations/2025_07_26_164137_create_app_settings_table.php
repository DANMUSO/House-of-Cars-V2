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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('description')->nullable();
            $table->enum('type', ['string', 'integer', 'float', 'boolean', 'json'])->default('string');
            $table->timestamps();
        });

        // Insert default settings
        DB::table('app_settings')->insert([
            [
                'key' => 'base_interest_rate',
                'value' => '15.0',
                'description' => 'Base interest rate percentage',
                'type' => 'float',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'minimum_deposit_percentage',
                'value' => '30',
                'description' => 'Minimum deposit percentage required',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_loan_duration_months',
                'value' => '72',
                'description' => 'Maximum loan duration in months',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('app_settings');
    }
};
