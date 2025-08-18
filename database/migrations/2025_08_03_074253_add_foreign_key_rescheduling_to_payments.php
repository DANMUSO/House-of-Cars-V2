<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::table('hire_purchase_payments', function (Blueprint $table) {
            // Add foreign key constraint after loan_rescheduling_history table exists
            if (Schema::hasTable('loan_rescheduling_history') && 
                Schema::hasColumn('hire_purchase_payments', 'rescheduling_id')) {
                $table->foreign('rescheduling_id')
                      ->references('id')
                      ->on('loan_rescheduling_history')
                      ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('hire_purchase_payments', function (Blueprint $table) {
            $table->dropForeign(['rescheduling_id']);
        });
    }
};
