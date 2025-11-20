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
        Schema::create('checklist_group_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_group_id')->constrained('check_list_groups')->onDelete('cascade');
            $table->foreignId('audit_question_id')->constrained('audit_questions')->onDelete('cascade');
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->unique(['checklist_group_id', 'audit_question_id'], 'group_question_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_group_question');
    }
};
