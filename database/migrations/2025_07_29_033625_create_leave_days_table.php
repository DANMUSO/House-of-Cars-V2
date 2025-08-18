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
        Schema::create('leave_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('leave_type'); // Annual Leave, Sick Leave, etc.
            $table->integer('total_days');
            $table->integer('used_days')->default(0);
            $table->integer('remaining_days');
            $table->year('year');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Ensure one record per user per leave type per year
            $table->unique(['user_id', 'leave_type', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_days');
    }
};
