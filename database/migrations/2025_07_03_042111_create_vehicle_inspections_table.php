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
        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('rh_front_wing');
            $table->string('rh_right_wing');
            $table->string('lh_front_wing');
            $table->string('lh_right_wing');
            $table->string('bonnet');
            $table->string('rh_front_door');
            $table->string('rh_rear_door');
            $table->string('lh_front_door');
            $table->string('lh_rear_door');
            $table->string('front_bumper');
            $table->string('rear_bumper');
            $table->string('head_lights');
            $table->string('bumper_lights');
            $table->string('corner_lights');
            $table->string('rear_lights');
        
            $table->string('radio_speakers');
            $table->string('seat_belt');
            $table->string('door_handles');
        
            $table->string('head_rest');
            $table->string('floor_carpets');
            $table->string('rubber_mats');
            $table->string('cigar_lighter');
            $table->string('boot_mats');
        
            $table->string('jack');
            $table->string('spare_wheel');
            $table->string('compressor');
            $table->string('wheel_spanner');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspections');
    }
};
