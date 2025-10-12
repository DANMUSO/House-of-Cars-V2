<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id');
            $table->string('agreement_type'); // 'hire_purchase', 'gentleman_agreement'
            $table->string('phone_number');
            $table->text('message');
            $table->enum('status', ['sent', 'delivered', 'pending', 'failed', 'scheduled'])->default('delivered');
            $table->enum('type', ['manual', 'automated', 'reminder', 'notification'])->default('manual');
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['agreement_id', 'agreement_type']);
            $table->index('status');
            $table->index('sent_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_logs');
    }
};