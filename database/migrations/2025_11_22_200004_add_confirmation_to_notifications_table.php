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
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('read_at');
            $table->foreignId('confirmed_by')->nullable()->after('confirmed_at')->constrained('users')->onDelete('set null');
            $table->boolean('requires_confirmation')->default(false)->after('confirmed_by');

            $table->index('confirmed_at');
            $table->index('requires_confirmation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropIndex(['confirmed_at']);
            $table->dropIndex(['requires_confirmation']);
            $table->dropColumn(['confirmed_at', 'confirmed_by', 'requires_confirmation']);
        });
    }
};
