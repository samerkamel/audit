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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('car_number', 50)->unique(); // e.g., C25001

            // Source tracking (polymorphic)
            $table->enum('source_type', ['internal_audit', 'external_audit', 'customer_complaint', 'process_performance', 'other']);
            $table->unsignedBigInteger('source_id')->nullable(); // Polymorphic ID

            // Specific foreign keys (optional based on source_type)
            $table->foreignId('audit_finding_id')->nullable()->constrained('audit_responses')->onDelete('set null');
            $table->unsignedBigInteger('customer_complaint_id')->nullable(); // Foreign key to be added in Phase 5 when customer_complaints table is created

            // Department tracking
            $table->foreignId('from_department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('to_department_id')->constrained('departments')->onDelete('cascade');

            // CAR details
            $table->date('issued_date');
            $table->string('subject', 500);
            $table->text('ncr_description'); // Non-Conformance Report description
            $table->text('clarification')->nullable(); // Quality team clarification

            // Status and priority
            $table->enum('status', ['draft', 'pending_approval', 'issued', 'in_progress', 'pending_review', 'rejected_to_be_edited', 'closed', 'late'])->default('draft');
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');

            // User tracking
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('car_number');
            $table->index('source_type');
            $table->index('audit_finding_id');
            $table->index('customer_complaint_id');
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
        Schema::dropIfExists('cars');
    }
};
