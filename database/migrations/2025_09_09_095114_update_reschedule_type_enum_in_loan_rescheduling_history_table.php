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
        Schema::table('loan_rescheduling_history', function (Blueprint $table) {
            $table->enum('reschedule_type', [
                'lump_sum',
                'reduce_duration',
                'increase_duration'
            ])->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_rescheduling_history', function (Blueprint $table) {
            // Roll back to previous definition (assuming it was NOT NULL with 'lump_sum' only)
            $table->enum('reschedule_type', [
                'lump_sum'
            ])->default('lump_sum')->change();
        });
    }
};

