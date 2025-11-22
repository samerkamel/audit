<?php

namespace Tests\Unit\Services;

use App\Models\AuditPlan;
use App\Models\Department;
use App\Models\User;
use App\Services\PdfExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PdfExportService $pdfService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdfService = new PdfExportService();
    }

    public function test_generate_audit_plan_pdf(): void
    {
        $leadAuditor = User::factory()->create();
        $creator = User::factory()->create();
        $department = Department::factory()->create();

        $auditPlan = AuditPlan::create([
            'title' => 'Test Audit Plan',
            'description' => 'Test Description',
            'audit_type' => 'internal',
            'scope' => 'Test Scope',
            'objectives' => 'Test Objectives',
            'lead_auditor_id' => $leadAuditor->id,
            'created_by' => $creator->id,
            'status' => 'planned',
            'is_active' => true,
        ]);

        $auditPlan->departments()->attach($department->id, [
            'planned_start_date' => now()->addDays(7),
            'planned_end_date' => now()->addDays(14),
            'status' => 'pending',
        ]);

        $pdf = $this->pdfService->generateAuditPlanPdf($auditPlan);

        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);
    }

    public function test_generate_audit_schedule_pdf(): void
    {
        $leadAuditor = User::factory()->create();
        $creator = User::factory()->create();
        $department = Department::factory()->create();

        $auditPlan = AuditPlan::create([
            'title' => 'Test Audit Plan',
            'audit_type' => 'internal',
            'lead_auditor_id' => $leadAuditor->id,
            'created_by' => $creator->id,
            'status' => 'planned',
            'is_active' => true,
        ]);

        $auditPlan->departments()->attach($department->id, [
            'planned_start_date' => now()->addDays(7),
            'planned_end_date' => now()->addDays(14),
            'status' => 'pending',
        ]);

        $pdf = $this->pdfService->generateAuditSchedulePdf($auditPlan);

        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);
    }

    public function test_get_audit_plan_pdf_content(): void
    {
        $leadAuditor = User::factory()->create();
        $creator = User::factory()->create();

        $auditPlan = AuditPlan::create([
            'title' => 'Test Audit Plan',
            'audit_type' => 'internal',
            'lead_auditor_id' => $leadAuditor->id,
            'created_by' => $creator->id,
            'status' => 'planned',
            'is_active' => true,
        ]);

        $content = $this->pdfService->getAuditPlanPdfContent($auditPlan);

        $this->assertIsString($content);
        $this->assertStringStartsWith('%PDF', $content);
    }

    public function test_get_audit_plan_filename(): void
    {
        $leadAuditor = User::factory()->create();
        $creator = User::factory()->create();

        $auditPlan = AuditPlan::create([
            'title' => 'Test Audit Plan',
            'audit_type' => 'internal',
            'lead_auditor_id' => $leadAuditor->id,
            'created_by' => $creator->id,
            'status' => 'planned',
            'is_active' => true,
        ]);

        $filename = $this->pdfService->getAuditPlanFilename($auditPlan);

        $this->assertStringContainsString('audit_plan_', $filename);
        $this->assertStringContainsString($auditPlan->id, $filename);
        $this->assertStringEndsWith('.pdf', $filename);
    }
}
