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
        Schema::create('improvement_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('io_number', 50)->unique(); // e.g., IO25001

            // Source tracking (polymorphic)
            $table->enum('source_type', ['internal_audit', 'external_audit', 'process_review', 'management_review', 'other']);
            $table->unsignedBigInteger('source_id')->nullable(); // Polymorphic ID

            // Specific foreign keys (optional based on source_type)
            $table->foreignId('audit_finding_id')->nullable()->constrained('audit_responses')->onDelete('set null');

            // Department tracking
            $table->foreignId('from_department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('to_department_id')->constrained('departments')->onDelete('cascade');

            // IO details
            $table->date('issued_date');
            $table->string('subject', 500);
            $table->text('observation_description'); // Observation description
            $table->text('improvement_suggestion')->nullable(); // Suggested improvement
            $table->text('clarification')->nullable(); // Quality team clarification

            // Status and priority
            $table->enum('status', ['draft', 'pending_approval', 'issued', 'in_progress', 'pending_review', 'rejected_to_be_edited', 'closed'])->default('draft');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');

            // User tracking
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('io_number');
            $table->index('source_type');
            $table->index('audit_finding_id');
            $table->index('from_department_id');
            $table->index('to_department_id');
            $table->index('status');
            $table->index('issued_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('improvement_opportunities');
    }
};
