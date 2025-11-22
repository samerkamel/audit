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
        // Reminder settings - configurable intervals
        Schema::create('reminder_settings', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // 'audit_plan', 'external_audit', 'car', 'document', 'certificate'
            $table->string('event_type'); // 'start_date', 'due_date', 'expiry_date', 'review_date'
            $table->string('name'); // Human-readable name
            $table->text('description')->nullable();
            $table->json('intervals'); // Array of intervals in hours, e.g., [72, 24, 1] for 3 days, 1 day, 1 hour
            $table->boolean('is_active')->default(true);
            $table->boolean('send_email')->default(true);
            $table->boolean('send_database')->default(true);
            $table->string('notification_template_code')->nullable(); // Link to notification template
            $table->timestamps();

            $table->unique(['entity_type', 'event_type']);
        });

        // Track sent reminders to avoid duplicates
        Schema::create('sent_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // 'audit_plan', 'external_audit', etc.
            $table->unsignedBigInteger('entity_id'); // The ID of the entity
            $table->string('event_type'); // 'start_date', 'due_date', etc.
            $table->integer('interval_hours'); // The interval this reminder was for
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who was notified
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id', 'event_type', 'interval_hours', 'user_id'], 'sent_reminders_unique');
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_reminders');
        Schema::dropIfExists('reminder_settings');
    }
};
