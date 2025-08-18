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
        Schema::create('gentlemanagreements', function (Blueprint $table) {
            $table->id();
            
            
            // Client Information
            $table->string('client_name', 100);
            $table->string('phone_number', 20);
            $table->string('email', 100);
            $table->string('national_id', 20)->unique();
            $table->string('kra_pin', 20)->nullable();
            $table->text('address')->nullable();
            
            // Vehicle Information - Updated for your system
            $table->string('car_type', 20); // 'import' or 'customer'
            $table->unsignedBigInteger('car_id'); // References either imported_id or customer_id
            $table->unsignedBigInteger('imported_id')->nullable(); // For CarImport relationship
            $table->unsignedBigInteger('customer_id')->nullable(); // For CustomerVehicle relationship
       
            // Financial Details
            $table->decimal('vehicle_price', 15, 2);
            $table->decimal('deposit_amount', 15, 2);
            $table->decimal('loan_amount', 15, 2);
            $table->integer('duration_months');
            $table->decimal('monthly_payment', 15, 2);
            $table->decimal('total_amount', 15, 2);
            
            // Payment Tracking
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('outstanding_balance', 15, 2);
            $table->decimal('payment_progress', 5, 2)->default(0); // percentage
            $table->integer('payments_made')->default(0);
            $table->integer('payments_remaining');
            
            // Agreement Details
            $table->date('agreement_date');
            $table->date('first_due_date');
            $table->date('last_payment_date')->nullable();
            $table->date('expected_completion_date');
            
            // Status and Flags
            $table->enum('status', ['pending', 'approved', 'active', 'completed', 'defaulted', 'terminated'])->default('pending');
            $table->boolean('is_overdue')->default(false);
            $table->integer('overdue_days')->default(0);
            $table->text('notes')->nullable();
            
            // Approval Information
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index('client_name');
            $table->index('phone_number');
            $table->index(['car_type', 'car_id']);
            $table->index('imported_id');
            $table->index('customer_id');
            $table->index(['is_overdue', 'overdue_days']);
            $table->index('email');
            $table->index('national_id');
            
            // Foreign Keys (add these if the tables exist)
            // $table->foreign('imported_id')->references('id')->on('car_imports')->onDelete('set null');
            // $table->foreign('customer_id')->references('id')->on('customer_vehicles')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gentlemanagreements');
    }
};
