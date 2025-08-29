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
        Schema::table('penalties', function (Blueprint $table) {
            // Add installment_number column after payment_schedule_id
            $table->integer('installment_number')->after('payment_schedule_id');
            
            // Add index for better performance
            $table->index('installment_number', 'penalties_installment_number_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropIndex('penalties_installment_number_idx');
            $table->dropColumn('installment_number');
        });
    }
};
