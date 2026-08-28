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
        Schema::create('loan_applications', function (Blueprint $table) {
          $table->uuid('id')->primary();
        $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
        $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
        $table->decimal('amount', 12, 2);
        $table->string('purpose');
        $table->enum('status', [
            'draft',
            'submitted',
            'under_review',
            'approved',
            'rejected',
            'disbursed',
        ])->default('draft');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
