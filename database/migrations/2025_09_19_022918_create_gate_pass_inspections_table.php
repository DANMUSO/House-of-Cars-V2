<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
    {
        Schema::create('gate_pass_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('gate_pass_id')->index(); // Reference to the gate pass
            $table->unsignedBigInteger('submitted_by')->nullable(); // User who submitted
            
            // Inspection items - Present checkboxes
            $table->boolean('spare_wheel_present')->default(false);
            $table->boolean('wheel_spanner_present')->default(false);
            $table->boolean('jack_present')->default(false);
            $table->boolean('life_saver_present')->default(false);
            $table->boolean('first_aid_kit_present')->default(false);
            
            // Inspection items - Absent checkboxes
            $table->boolean('spare_wheel_absent')->default(false);
            $table->boolean('wheel_spanner_absent')->default(false);
            $table->boolean('jack_absent')->default(false);
            $table->boolean('life_saver_absent')->default(false);
            $table->boolean('first_aid_kit_absent')->default(false);
            
            // Comments
            $table->text('comments')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Foreign key constraint (assuming you have a users table)
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('set null');
            
            // Unique constraint to prevent duplicate inspections for the same gate pass
            $table->unique(['gate_pass_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('gate_pass_inspections');
    }
};
