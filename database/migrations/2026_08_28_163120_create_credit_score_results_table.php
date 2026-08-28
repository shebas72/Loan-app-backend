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
        Schema::create('credit_score_results', function (Blueprint $table) {
             $table->uuid('id')->primary();
        $table->uuid('tenant_id');
        $table->foreignUuid('loan_application_id')->unique()->constrained('loan_applications')->cascadeOnDelete();
        $table->unsignedSmallInteger('score');
        $table->enum('decision', ['approve', 'refer', 'decline']);
        $table->json('factors')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_score_results');
    }
};
