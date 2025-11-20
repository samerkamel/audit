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
        Schema::create('car_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');

            // Root cause analysis
            $table->text('root_cause');

            // Short-term action (Correction)
            $table->text('correction');
            $table->date('correction_target_date');
            $table->date('correction_actual_date')->nullable();

            // Long-term action (Corrective Action)
            $table->text('corrective_action');
            $table->date('corrective_action_target_date');
            $table->date('corrective_action_actual_date')->nullable();

            // Attachments (JSON array of file paths)
            $table->json('attachments')->nullable();

            // Response workflow
            $table->enum('response_status', ['pending', 'submitted', 'accepted', 'rejected'])->default('pending');

            // User tracking
            $table->foreignId('responded_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            // Rejection handling
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('car_id');
            $table->index('response_status');
            $table->index('responded_by');
            $table->index('reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_responses');
    }
};
