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
        Schema::create('external_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_number')->unique();

            // Audit Information
            $table->enum('audit_type', [
                'initial_certification',
                'surveillance',
                'recertification',
                'special',
                'follow_up'
            ])->default('surveillance');
            $table->string('certification_body'); // e.g., "BSI", "TUV", "SGS"
            $table->string('standard'); // e.g., "ISO 9001:2015", "ISO 14001:2015"
            $table->string('lead_auditor_name');
            $table->string('lead_auditor_email')->nullable();
            $table->string('lead_auditor_phone')->nullable();

            // Audit Schedule
            $table->date('scheduled_start_date');
            $table->date('scheduled_end_date');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();

            // Scope
            $table->json('audited_departments')->nullable(); // Array of department IDs
            $table->json('audited_processes')->nullable(); // Array of process names
            $table->text('scope_description')->nullable();

            // Results
            $table->enum('result', [
                'pending',
                'passed',
                'conditional',
                'failed'
            ])->default('pending');
            $table->integer('major_ncrs_count')->default(0);
            $table->integer('minor_ncrs_count')->default(0);
            $table->integer('observations_count')->default(0);
            $table->integer('opportunities_count')->default(0);

            // Findings & Reports
            $table->text('audit_summary')->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->string('audit_report_path')->nullable();
            $table->json('attachments')->nullable(); // Array of file paths

            // Follow-up
            $table->date('next_audit_date')->nullable();

            // Status & Workflow
            $table->enum('status', [
                'scheduled',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('scheduled');

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('coordinator_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('scheduled_start_date');
            $table->index('status');
            $table->index('audit_type');
            $table->index('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_audits');
    }
};
