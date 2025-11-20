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
        Schema::create('audit_questions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Question code/reference
            $table->text('question'); // The actual question text
            $table->enum('category', ['compliance', 'operational', 'financial', 'it', 'quality', 'security'])->default('operational');
            $table->text('description')->nullable(); // Additional context or guidance
            $table->enum('answer_type', ['yes_no', 'text', 'rating', 'multiple_choice'])->default('yes_no');
            $table->text('answer_options')->nullable(); // For multiple choice questions (JSON)
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_questions');
    }
};
