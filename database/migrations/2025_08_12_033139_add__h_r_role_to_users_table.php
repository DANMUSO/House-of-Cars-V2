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
         // Method 1: Using raw SQL (Recommended for enum modifications)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'Managing-Director', 
            'Accountant', 
            'Showroom-Manager', 
            'Salesperson', 
            'Support-Staff', 
            'Client',
            'HR'
        ) DEFAULT 'HR'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
