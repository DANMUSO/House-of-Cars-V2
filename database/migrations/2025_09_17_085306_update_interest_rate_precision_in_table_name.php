<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('hire_purchase_agreements', function (Blueprint $table) {
         $table->decimal('interest_rate', 10, 5)->change();
        });
    }

    public function down()
    {
        Schema::table('hire_purchase_agreements', function (Blueprint $table) {
            $table->decimal('interest_rate', 5, 2)->change();
        });
    }
};
