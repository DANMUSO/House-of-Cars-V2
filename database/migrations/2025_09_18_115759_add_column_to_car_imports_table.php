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
        Schema::table('car_imports', function (Blueprint $table) {
            $table->string('colour')->nullable();
            $table->string('engine_no')->nullable();
            $table->string('engine_capacity')->nullable();
            $table->string('transmission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_imports', function (Blueprint $table) {
            //
        });
    }
};
