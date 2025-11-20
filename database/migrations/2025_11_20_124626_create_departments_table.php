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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sector_id')->constrained()->onDelete('cascade');
            $table->string('name', 255);
            $table->string('name_ar', 255);
            $table->string('code', 50)->unique();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('sector_id');
            $table->index('code');
            $table->index('manager_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
