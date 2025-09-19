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
        Schema::table('gate_pass_inspections', function (Blueprint $table) {
        $table->dropUnique(['gate_pass_id']); // Remove unique constraint
        $table->integer('version')->default(1);
        $table->boolean('is_latest')->default(true);
        $table->index(['gate_pass_id', 'is_latest']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gate_pass_inspections', function (Blueprint $table) {
            //
        });
    }
};
