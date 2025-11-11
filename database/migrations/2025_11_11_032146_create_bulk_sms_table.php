<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_sms', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->json('recipients'); // phone numbers array
            $table->string('target_group'); // leads, hire_purchase, gentleman
            $table->foreignId('sent_by')->constrained('users')->onDelete('cascade');
            $table->integer('total_sent')->default(0);
            $table->integer('total_failed')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_sms');
    }
};