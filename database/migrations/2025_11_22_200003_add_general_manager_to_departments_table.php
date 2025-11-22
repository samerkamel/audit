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
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('general_manager_id')
                ->nullable()
                ->after('manager_id')
                ->constrained('users')
                ->onDelete('set null');

            $table->index('general_manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['general_manager_id']);
            $table->dropIndex(['general_manager_id']);
            $table->dropColumn('general_manager_id');
        });
    }
};
