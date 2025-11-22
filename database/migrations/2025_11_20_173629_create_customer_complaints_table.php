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
        Schema::create('customer_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_number')->unique();
            $table->date('complaint_date');

            // Customer Information
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_company')->nullable();

            // Complaint Details
            $table->string('complaint_subject');
            $table->text('complaint_description');
            $table->enum('complaint_category', [
                'product_quality',
                'service_quality',
                'delivery',
                'documentation',
                'technical_support',
                'billing',
                'other'
            ])->default('other');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('severity', ['minor', 'major', 'critical'])->default('minor');

            // Assignment
            $table->foreignId('assigned_to_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Response & Resolution
            $table->text('initial_response')->nullable();
            $table->date('response_date')->nullable();
            $table->text('root_cause_analysis')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('resolution')->nullable();
            $table->date('resolved_date')->nullable();

            // Status & Workflow
            $table->enum('status', [
                'new',
                'acknowledged',
                'investigating',
                'resolved',
                'closed',
                'escalated'
            ])->default('new');

            // CAR Integration
            $table->boolean('car_required')->default(false);
            $table->foreignId('car_id')->nullable()->constrained('cars')->nullOnDelete();

            // Tracking
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();

            // Customer Satisfaction
            $table->integer('satisfaction_rating')->nullable(); // 1-5 scale
            $table->text('customer_feedback')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('complaint_date');
            $table->index('status');
            $table->index('priority');
            $table->index('assigned_to_department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_complaints');
    }
};
