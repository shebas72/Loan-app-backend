<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    // 1. Widen first: old and new values must coexist while we convert rows
    DB::statement("ALTER TABLE users MODIFY role ENUM(
        'applicant','loan_officer','underwriter','branch_manager','bank_admin','admin'
    ) NOT NULL DEFAULT 'applicant'");

    // 2. Convert existing rows
    DB::table('users')
        ->whereIn('role', ['underwriter', 'branch_manager'])
        ->update(['role' => 'bank_admin']);

    // 3. Narrow to the final set
    DB::statement("ALTER TABLE users MODIFY role ENUM(
        'applicant','loan_officer','bank_admin','admin'
    ) NOT NULL DEFAULT 'applicant'");
}

public function down(): void
{
    DB::statement("ALTER TABLE users MODIFY role ENUM(
        'applicant','loan_officer','underwriter','branch_manager','bank_admin','admin'
    ) NOT NULL DEFAULT 'applicant'");

    DB::table('users')->where('role', 'bank_admin')->update(['role' => 'underwriter']);

    DB::statement("ALTER TABLE users MODIFY role ENUM(
        'applicant','loan_officer','underwriter','branch_manager','admin'
    ) NOT NULL DEFAULT 'applicant'");
}
};
