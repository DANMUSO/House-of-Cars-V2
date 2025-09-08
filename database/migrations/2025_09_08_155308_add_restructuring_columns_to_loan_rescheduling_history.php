<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This adds restructuring-specific columns to existing loan_rescheduling_history table
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_rescheduling_history', function (Blueprint $table) {
            // Add restructuring fee tracking columns
            $table->decimal('restructuring_fee', 15, 2)->nullable()->after('lump_sum_amount')
                  ->comment('Fee charged for loan restructuring (typically 3% of outstanding balance). NULL for lump sum rescheduling.');
            
            $table->decimal('restructuring_fee_rate', 5, 2)->nullable()->after('restructuring_fee')
                  ->comment('Fee rate percentage applied (e.g., 3.00 for 3%). NULL for lump sum rescheduling.');
            
            // Add type indicator to distinguish restructuring from lump sum rescheduling
            $table->enum('operation_type', ['lump_sum_rescheduling', 'loan_restructuring'])
                  ->default('lump_sum_rescheduling')->after('restructuring_fee_rate')
                  ->comment('Type of operation: lump_sum_rescheduling (existing) or loan_restructuring (new)');
            
            // Add optional breakdown columns for restructuring (NULL for existing lump sum records)
            $table->decimal('due_payments_component', 15, 2)->nullable()->after('operation_type')
                  ->comment('Due payments component of outstanding balance (for restructuring only)');
            
            $table->decimal('principal_component', 15, 2)->nullable()->after('due_payments_component')
                  ->comment('Principal balance component (for restructuring only)');
            
            $table->decimal('penalties_component', 15, 2)->nullable()->after('principal_component')
                  ->comment('Penalties component (for restructuring only)');
            
            // Optional: JSON field for additional metadata (won't affect existing queries)
            $table->json('additional_metadata')->nullable()->after('penalties_component')
                  ->comment('Optional JSON field for storing additional restructuring details');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_rescheduling_history', function (Blueprint $table) {
            $table->dropColumn([
                'restructuring_fee',
                'restructuring_fee_rate',
                'operation_type',
                'due_payments_component',
                'principal_component',
                'penalties_component',
                'additional_metadata'
            ]);
        });
    }
};