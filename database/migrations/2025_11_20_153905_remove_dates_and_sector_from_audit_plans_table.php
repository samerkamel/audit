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
        Schema::table('audit_plans', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['sector_id']);

            // Drop columns
            $table->dropColumn(['planned_start_date', 'planned_end_date', 'sector_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_plans', function (Blueprint $table) {
            // Re-add columns
            $table->foreignId('sector_id')->nullable()->constrained()->onDelete('set null');
            $table->date('planned_start_date');
            $table->date('planned_end_date');
        });
    }
};
