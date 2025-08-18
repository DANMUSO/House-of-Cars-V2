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
        Schema::create('loan_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value');
            $table->string('setting_type', 20)->default('string'); // string, number, boolean, json
            $table->string('description', 255)->nullable();
            $table->timestamps();
            
            // Index
            $table->index('setting_key');
            $table->index('setting_type');
        });
        
        // Insert default settings
        $this->insertDefaultSettings();
    }

    public function down()
    {
        Schema::dropIfExists('loan_settings');
    }

    private function insertDefaultSettings()
    {
        $settings = [
            [
                'setting_key' => 'min_deposit_percentage',
                'setting_value' => '30',
                'setting_type' => 'number',
                'description' => 'Minimum deposit percentage required',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'standard_rate_threshold',
                'setting_value' => '50',
                'setting_type' => 'number',
                'description' => 'Deposit percentage threshold for standard rates',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'standard_interest_rates',
                'setting_value' => '{"70": 1.5, "60": 2.0, "50": 2.5}',
                'setting_type' => 'json',
                'description' => 'Interest rates for standard deposits (monthly %)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'higher_interest_rates',
                'setting_value' => '{"40": 3.0, "35": 3.5, "30": 4.0}',
                'setting_type' => 'json',
                'description' => 'Interest rates for higher risk deposits (monthly %)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'max_loan_duration',
                'setting_value' => '72',
                'setting_type' => 'number',
                'description' => 'Maximum loan duration in months',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'grace_period_days',
                'setting_value' => '7',
                'setting_type' => 'number',
                'description' => 'Grace period before marking as overdue',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'penalty_rate',
                'setting_value' => '2.0',
                'setting_type' => 'number',
                'description' => 'Monthly penalty rate for overdue payments (%)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'company_name',
                'setting_value' => 'AutoFinance Solutions Ltd',
                'setting_type' => 'string',
                'description' => 'Company name for documents',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'contact_phone',
                'setting_value' => '+254700000000',
                'setting_type' => 'string',
                'description' => 'Company contact phone number',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'contact_email',
                'setting_value' => 'info@autofinance.com',
                'setting_type' => 'string',
                'description' => 'Company contact email',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('loan_settings')->insert($settings);
    }
};
