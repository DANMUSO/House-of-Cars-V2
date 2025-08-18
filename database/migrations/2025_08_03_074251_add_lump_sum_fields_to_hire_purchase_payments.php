<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hire_purchase_payments', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('hire_purchase_payments', 'is_lump_sum')) {
                $table->boolean('is_lump_sum')->default(false)->after('payment_type');
            }
            
            if (!Schema::hasColumn('hire_purchase_payments', 'rescheduling_id')) {
                $table->unsignedBigInteger('rescheduling_id')->nullable()->after('is_lump_sum');
            }
            
            // Add indexes
            $table->index(['agreement_id', 'is_lump_sum']);
            $table->index('rescheduling_id');
        });
    }

    public function down()
    {
        Schema::table('hire_purchase_payments', function (Blueprint $table) {
            $table->dropIndex(['agreement_id', 'is_lump_sum']);
            $table->dropIndex(['rescheduling_id']);
            $table->dropColumn(['is_lump_sum', 'rescheduling_id']);
        });
    }
};
