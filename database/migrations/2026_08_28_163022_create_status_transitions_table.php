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
        Schema::create('status_transitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
        $table->uuid('tenant_id');
        $table->foreignUuid('loan_application_id')->constrained('loan_applications')->cascadeOnDelete();
        $table->string('from_status')->nullable();
        $table->string('to_status');
        $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
        $table->text('comment')->nullable();
        $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_transitions');
    }
};
