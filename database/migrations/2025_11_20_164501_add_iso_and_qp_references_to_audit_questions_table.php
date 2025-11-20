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
        Schema::table('audit_questions', function (Blueprint $table) {
            // Rename question to question_text for consistency
            $table->renameColumn('question', 'question_text');

            // Add ISO and quality procedure reference fields
            $table->string('iso_reference')->nullable()->after('question_text');
            $table->string('quality_procedure_reference')->nullable()->after('iso_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_questions', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn(['iso_reference', 'quality_procedure_reference']);

            // Rename back to question
            $table->renameColumn('question_text', 'question');
        });
    }
};
