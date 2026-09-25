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
        DB::statement("ALTER TABLE loan_applications MODIFY status ENUM(
        'draft', 'submitted', 'under_review', 'approved', 'rejected', 'appealed', 'disbursed'
    ) NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE loan_applications MODIFY status ENUM(
        'draft', 'submitted', 'under_review', 'approved', 'rejected', 'disbursed'
    ) NOT NULL DEFAULT 'draft'");
    }
};
