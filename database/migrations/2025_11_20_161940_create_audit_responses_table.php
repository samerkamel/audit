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
        Schema::create('audit_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained('audit_plans')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('checklist_group_id')->constrained('check_list_groups')->onDelete('cascade');
            $table->foreignId('audit_question_id')->constrained('audit_questions')->onDelete('cascade');
            $table->foreignId('auditor_id')->constrained('users')->onDelete('cascade');
            $table->enum('response', ['complied', 'not_complied', 'not_applicable'])->nullable();
            $table->text('comments')->nullable();
            $table->string('evidence_file')->nullable();
            $table->timestamp('audited_at')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate responses for the same question in the same audit
            $table->unique(['audit_plan_id', 'department_id', 'audit_question_id'], 'audit_response_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_responses');
    }
};
