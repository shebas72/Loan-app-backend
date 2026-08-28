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
        Schema::create('documents', function (Blueprint $table) {
           $table->uuid('id')->primary();
        $table->uuid('tenant_id');
        $table->foreignUuid('loan_application_id')->constrained('loan_applications')->cascadeOnDelete();
        $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
        $table->enum('type', [
            'national_id',
            'proof_of_income',
            'bank_statement',
            'collateral_document',
            'other',
        ]);
        $table->string('original_filename');
        $table->string('file_path');
        $table->string('mime_type');
        $table->unsignedBigInteger('file_size'); // bytes
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
