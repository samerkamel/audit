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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., 'car_issued', 'car_due', 'audit_scheduled'
            $table->string('name'); // Human-readable name
            $table->string('category'); // 'car', 'audit', 'document', 'certificate', 'complaint'
            $table->text('description')->nullable(); // Description of when this notification is sent

            // Email template
            $table->string('email_subject');
            $table->text('email_body'); // Supports HTML and placeholders

            // Database/In-app notification
            $table->string('notification_title');
            $table->text('notification_message');
            $table->string('notification_icon')->default('tabler-bell');
            $table->string('notification_color')->default('primary'); // primary, success, warning, danger, info

            // Channels configuration
            $table->boolean('send_email')->default(true);
            $table->boolean('send_database')->default(true);
            $table->boolean('is_active')->default(true);

            // Available placeholders for this template (JSON)
            $table->json('available_placeholders')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
