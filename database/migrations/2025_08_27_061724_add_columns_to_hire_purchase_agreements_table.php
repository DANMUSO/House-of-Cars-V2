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
        Schema::table('hire_purchase_agreements', function (Blueprint $table) {
              $table->string('phone_numberalt')->nullable();
              $table->string('emailalt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hire_purchase_agreements', function (Blueprint $table) {
            //
        });
    }
};
