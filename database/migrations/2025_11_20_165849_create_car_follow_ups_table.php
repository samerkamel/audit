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
        Schema::create('car_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');

            // Follow-up type and status
            $table->enum('follow_up_type', ['correction', 'corrective_action']);
            $table->enum('follow_up_status', ['accepted', 'not_accepted', 'pending'])->default('pending');

            // Follow-up details
            $table->text('follow_up_notes')->nullable();

            // User tracking
            $table->foreignId('followed_up_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('followed_up_at');

            $table->timestamps();

            // Indexes
            $table->index('car_id');
            $table->index('follow_up_type');
            $table->index('follow_up_status');
            $table->index('followed_up_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_follow_ups');
    }
};
