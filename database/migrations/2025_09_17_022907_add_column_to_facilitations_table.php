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
        Schema::table('facilitations', function (Blueprint $table) {
            $table->json('receipt_documents')->nullable();
            $table->integer('receipt_count')->default(0);
            $table->string('receipt_file_size')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilitations', function (Blueprint $table) {
            //
        });
    }
};
