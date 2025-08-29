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
        Schema::table('penalties', function (Blueprint $table) {
            // Add cumulative penalty tracking fields
            $table->decimal('cumulative_unpaid_amount', 15, 2)->default(0)->after('penalty_amount');
            $table->integer('penalty_sequence')->default(1)->after('cumulative_unpaid_amount');
            
            // Add indexes for better performance
            $table->index(['agreement_type', 'agreement_id', 'penalty_sequence'], 'penalties_agreement_sequence_idx');
            $table->index(['status', 'due_date'], 'penalties_status_due_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            //
        });
    }
};
