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
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            $table->integer('head_rest_number')->default(0)->after('head_rest');
            $table->integer('floor_carpets_number')->default(0)->after('floor_carpets');
            $table->integer('rubber_mats_number')->default(0)->after('rubber_mats');
            $table->integer('cigar_lighter_number')->default(0)->after('cigar_lighter');
            $table->integer('boot_mats_number')->default(0)->after('boot_mats');
            $table->string('handle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            //
        });
    }
};
