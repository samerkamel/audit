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
        Schema::create('improvement_opportunity_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('improvement_opportunity_id');
            $table->foreign('improvement_opportunity_id', 'io_responses_io_id_foreign')
                ->references('id')
                ->on('improvement_opportunities')
                ->onDelete('cascade');

            // Response details
            $table->text('proposed_action'); // What improvement action will be taken
            $table->text('implementation_plan')->nullable(); // How it will be implemented
            $table->date('target_date'); // Target completion date
            $table->date('actual_date')->nullable(); // Actual completion date
            $table->text('outcome')->nullable(); // Results of the improvement
            $table->json('attachments')->nullable(); // Supporting documents

            // Status
            $table->enum('response_status', ['pending', 'submitted', 'accepted', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();

            // User tracking
            $table->foreignId('responded_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // Indexes with short names to avoid MySQL length limits
            $table->index('improvement_opportunity_id', 'io_responses_io_id_idx');
            $table->index('response_status', 'io_responses_status_idx');
            $table->index('target_date', 'io_responses_target_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('improvement_opportunity_responses', function (Blueprint $table) {
            $table->dropForeign('io_responses_io_id_foreign');
        });
        Schema::dropIfExists('improvement_opportunity_responses');
    }
};
