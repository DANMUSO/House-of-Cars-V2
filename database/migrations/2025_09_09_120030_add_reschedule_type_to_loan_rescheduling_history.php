<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'reduce_installment' to the existing ENUM values
        DB::statement("ALTER TABLE loan_rescheduling_history 
                      MODIFY COLUMN reschedule_type 
                      ENUM('lump_sum', 'reduce_duration', 'increase_duration', 'reduce_installment') 
                      COLLATE utf8mb4_unicode_ci DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'reduce_installment' from ENUM values
        DB::statement("ALTER TABLE loan_rescheduling_history 
                      MODIFY COLUMN reschedule_type 
                      ENUM('lump_sum', 'reduce_duration', 'increase_duration') 
                      COLLATE utf8mb4_unicode_ci DEFAULT NULL");
    }
};