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
        Schema::create('audit_plan_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');

            // Department-specific dates
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();

            // Department-specific status
            $table->enum('status', ['pending', 'in_progress', 'completed', 'deferred'])->default('pending');

            // Additional notes for this department's audit
            $table->text('notes')->nullable();

            $table->timestamps();

            // Prevent duplicate entries
            $table->unique(['audit_plan_id', 'department_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_plan_department');
    }
};
