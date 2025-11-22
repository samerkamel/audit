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
        Schema::create('auditor_procedure_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('procedure_id')->constrained()->cascadeOnDelete();
            $table->enum('recommendation_level', ['primary', 'secondary', 'backup'])->default('primary');
            $table->text('notes')->nullable();
            $table->boolean('is_certified')->default(false);
            $table->date('certification_date')->nullable();
            $table->date('certification_expiry')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'procedure_id']);
            $table->index('recommendation_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditor_procedure_recommendations');
    }
};
