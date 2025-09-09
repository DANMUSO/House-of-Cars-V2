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
        Schema::table('payment_schedules', function (Blueprint $table) {
             // Add restructuring_type (nullable, only set when restructured)
            $table->enum('restructuring_type', ['reduce_duration', 'increase_duration'])
                  ->nullable()
                  ->after('schedule_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            //
        });
    }
};
