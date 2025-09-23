<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
            'Yard-Supervisor'
        ) DEFAULT 'HR'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: remove Yard-Supervisor and restore Driver + Cleaner
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'Managing-Director',
            'General-Manager',
            'Accountant',
            'Showroom-Manager',
            'Salesperson',
            'Support-Staff',
            'Client',
            'HR',
            'Driver',
            'Cleaner'
        ) DEFAULT 'HR'");
    }
};
