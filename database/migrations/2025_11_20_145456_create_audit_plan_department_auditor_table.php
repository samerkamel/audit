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
        Schema::create('audit_plan_department_auditor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_department_id')->constrained('audit_plan_department')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['lead', 'member'])->default('member');
            $table->timestamps();

            // Prevent duplicate assignments
            $table->unique(['audit_plan_department_id', 'user_id'], 'unique_dept_auditor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_plan_department_auditor');
    }
};
