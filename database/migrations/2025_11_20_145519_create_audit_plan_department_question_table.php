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
        Schema::create('audit_plan_department_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_department_id')->constrained('audit_plan_department')->onDelete('cascade');
            $table->foreignId('audit_question_id')->constrained('audit_questions')->onDelete('cascade');

            // Answer fields
            $table->text('answer')->nullable(); // The answer provided
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'not_applicable'])->default('not_started');
            $table->text('notes')->nullable(); // Additional notes or comments
            $table->text('evidence')->nullable(); // Reference to evidence/documentation
            $table->foreignId('answered_by')->nullable()->constrained('users')->onDelete('set null'); // Who answered
            $table->timestamp('answered_at')->nullable(); // When it was answered
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // Who reviewed
            $table->timestamp('reviewed_at')->nullable(); // When it was reviewed

            $table->timestamps();

            // Prevent duplicate questions
            $table->unique(['audit_plan_department_id', 'audit_question_id'], 'unique_dept_question');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_plan_department_question');
    }
};
