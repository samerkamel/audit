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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Document identification
            $table->string('document_number')->unique();
            $table->string('title');
            $table->enum('category', [
                'quality_manual',
                'procedure',
                'work_instruction',
                'form',
                'record',
                'external_document'
            ]);

            // Version control
            $table->string('version', 20)->default('1.0');
            $table->integer('revision_number')->default(0);
            $table->date('effective_date')->nullable();
            $table->date('next_review_date')->nullable();

            // Document status
            $table->enum('status', [
                'draft',
                'pending_review',
                'pending_approval',
                'approved',
                'effective',
                'obsolete',
                'archived'
            ])->default('draft');

            // Content and storage
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->bigInteger('file_size')->nullable();

            // Ownership and approval
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('reviewed_date')->nullable();
            $table->date('approved_date')->nullable();

            // Related information
            $table->json('applicable_departments')->nullable();
            $table->json('related_documents')->nullable();
            $table->json('keywords')->nullable();

            // Change tracking
            $table->text('revision_notes')->nullable();
            $table->foreignId('supersedes_id')->nullable()->constrained('documents')->nullOnDelete();

            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('document_number');
            $table->index('category');
            $table->index('status');
            $table->index('effective_date');
            $table->index('next_review_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
