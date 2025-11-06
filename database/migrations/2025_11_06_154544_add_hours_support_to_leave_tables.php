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
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->enum('leave_duration_type', ['days', 'hours'])->default('days')->after('leave_type');
            $table->decimal('total_hours', 5, 2)->nullable()->after('total_days');
            $table->time('start_time')->nullable()->after('start_date');
            $table->time('end_time')->nullable()->after('end_date');
        });

        Schema::table('leave_days', function (Blueprint $table) {
            $table->decimal('total_hours', 6, 2)->default(0)->after('total_days');
            $table->decimal('used_hours', 6, 2)->default(0)->after('used_days');
            $table->decimal('remaining_hours', 6, 2)->default(0)->after('remaining_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn(['leave_duration_type', 'total_hours', 'start_time', 'end_time']);
        });

        Schema::table('leave_days', function (Blueprint $table) {
            $table->dropColumn(['total_hours', 'used_hours', 'remaining_hours']);
        });
    }
};