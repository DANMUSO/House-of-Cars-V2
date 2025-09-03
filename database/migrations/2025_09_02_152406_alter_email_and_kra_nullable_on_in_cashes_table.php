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
        Schema::table('in_cashes', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('KRA')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('in_cashes', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->string('KRA')->nullable(false)->change();
        });
    }
};
