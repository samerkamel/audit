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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();

            // Certificate Information
            $table->string('standard'); // e.g., "ISO 9001:2015"
            $table->string('certification_body'); // e.g., "BSI", "TUV", "SGS"
            $table->enum('certificate_type', [
                'initial',
                'renewal',
                'transfer'
            ])->default('initial');

            // Validity
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->enum('status', [
                'valid',
                'expiring_soon',
                'expired',
                'suspended',
                'revoked'
            ])->default('valid');

            // Scope
            $table->text('scope_of_certification');
            $table->json('covered_sites')->nullable(); // Array of site names
            $table->json('covered_processes')->nullable(); // Array of process names

            // Documentation
            $table->string('certificate_file_path')->nullable();
            $table->json('attachments')->nullable(); // Array of file paths
            $table->text('notes')->nullable();

            // Tracking
            $table->foreignId('issued_for_audit_id')->nullable()->constrained('external_audits')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('issue_date');
            $table->index('expiry_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
