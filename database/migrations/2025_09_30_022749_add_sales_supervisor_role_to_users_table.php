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
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'Managing-Director',
            'General-Manager',
            'Accountant',
            'Showroom-Manager',
            'Salesperson',
            'Support-Staff',
            'Client',
            'HR',
            'Yard-Supervisor',
            'Sales-Supervisor'
        ) DEFAULT 'HR'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: remove Sales-Supervisor
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'Managing-Director',
            'General-Manager',
            'Accountant',
            'Showroom-Manager',
            'Salesperson',
            'Support-Staff',
            'Client',
            'HR',
            'Yard-Supervisor'
        ) DEFAULT 'HR'");
    }
};
