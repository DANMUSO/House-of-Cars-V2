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
        Schema::create('car_logbooks', function (Blueprint $table) {
            $table->id();
            
            // Car relationship - either imported or trade-in
            $table->unsignedBigInteger('customer_id')->default(0); // For trade-in cars
            $table->unsignedBigInteger('imported_id')->default(0); // For imported cars
            
            // Logbook details
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('document_type')->default('logbook'); // logbook, registration, insurance, etc.
            $table->json('documents')->nullable(); // Array of document paths in S3
            $table->timestamp('document_date')->nullable(); // Date of the document
            $table->string('issued_by')->nullable(); // Authority that issued the document
            $table->string('reference_number')->nullable(); // Document reference/serial number
            
            // Status and metadata
            $table->enum('status', ['active', 'archived', 'expired'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamp('expiry_date')->nullable(); // For documents that expire
            
            // File metadata
            $table->integer('file_count')->default(0);
            $table->string('file_size')->nullable(); // Human readable file size
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['customer_id', 'status']);
            $table->index(['imported_id', 'status']);
            $table->index('document_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_logbooks');
    }
};