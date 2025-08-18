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
        Schema::create('fleet_acquisitions', function (Blueprint $table) {
            $table->id();
            
            // Vehicle Information
            $table->string('vehicle_make');
            $table->string('vehicle_model');
            $table->year('vehicle_year');
            $table->string('engine_capacity');
            $table->string('chassis_number')->unique();
            $table->string('engine_number')->unique();
            $table->string('registration_number')->nullable();
            $table->enum('vehicle_category', ['commercial', 'passenger', 'utility', 'special_purpose']);
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('market_value', 15, 2);
            $table->json('vehicle_photos')->nullable(); // Store multiple photo paths
            
            // Financial Details
            $table->decimal('down_payment', 15, 2);
            $table->decimal('monthly_installment', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->decimal('total_interest', 15, 2);
            $table->integer('loan_duration_months');
            $table->decimal('total_amount_payable', 15, 2);
            $table->date('first_payment_date');
            $table->decimal('insurance_premium', 15, 2)->nullable();
            
            // Legal & Compliance
            $table->string('hp_agreement_number')->unique();
            $table->enum('logbook_custody', ['financier', 'company']);
            $table->string('insurance_policy_number')->nullable();
            $table->string('insurance_company')->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->string('company_kra_pin');
            $table->string('business_permit_number')->nullable();
            
            // Vendor/Financier Information
            $table->string('financing_institution');
            $table->string('financier_contact_person')->nullable();
            $table->string('financier_phone')->nullable();
            $table->string('financier_email')->nullable();
            $table->string('financier_agreement_ref')->nullable();
            
            // Status and Tracking
            $table->enum('status', ['pending', 'approved', 'active', 'completed', 'defaulted'])->default('pending');
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('outstanding_balance', 15, 2);
            $table->integer('payments_made')->default(0);
            $table->date('completion_date')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_acquisitions');
    }
};
