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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('password')->constrained()->onDelete('set null');
            $table->foreignId('sector_id')->nullable()->after('department_id')->constrained()->onDelete('set null');
            $table->string('phone', 50)->nullable()->after('sector_id');
            $table->string('mobile', 50)->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('mobile');
            $table->enum('language', ['en', 'ar'])->default('en')->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->softDeletes()->after('updated_at');

            // Indexes
            $table->index('department_id');
            $table->index('sector_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['sector_id']);
            $table->dropColumn([
                'department_id',
                'sector_id',
                'phone',
                'mobile',
                'is_active',
                'language',
                'last_login_at',
                'deleted_at'
            ]);
        });
    }
};
