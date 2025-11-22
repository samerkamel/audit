<?php

namespace Tests\Feature;

use App\Models\AuditPlan;
use App\Models\Car;
use App\Models\Certificate;
use App\Models\CustomerComplaint;
use App\Models\Department;
use App\Models\Document;
use App\Models\ExternalAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
    }

    /**
     * Test dashboard displays successfully.
     */
    public function test_dashboard_displays_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard-analytics'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
    }

    /**
     * Test dashboard requires authentication.
     */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('dashboard-analytics'));

        $response->assertRedirect();
    }

    /**
     * Test dashboard has statistics.
     */
    public function test_dashboard_has_statistics(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard-analytics'));

        $response->assertStatus(200);
        $response->assertViewHas('stats');

        $stats = $response->viewData('stats');

        $this->assertArrayHasKey('total_audit_plans', $stats);
        $this->assertArrayHasKey('total_cars', $stats);
        $this->assertArrayHasKey('total_complaints', $stats);
        $this->assertArrayHasKey('total_certificates', $stats);
        $this->assertArrayHasKey('total_documents', $stats);
    }

    /**
     * Test dashboard statistics count audit plans.
     */
    public function test_dashboard_counts_audit_plans(): void
    {
        AuditPlan::create([
            'title' => 'Test Plan 1',
            'audit_type' => 'internal',
            'status' => 'in_progress',
            'lead_auditor_id' => $this->user->id,
            'created_by' => $this->user->id,
            'is_active' => true,
        ]);
        AuditPlan::create([
            'title' => 'Test Plan 2',
            'audit_type' => 'internal',
            'status' => 'completed',
            'lead_auditor_id' => $this->user->id,
            'created_by' => $this->user->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard-analytics'));

        $stats = $response->viewData('stats');

        $this->assertEquals(2, $stats['total_audit_plans']);
        $this->assertEquals(1, $stats['active_audit_plans']);
        $this->assertEquals(1, $stats['completed_audits']);
    }

    /**
     * Test dashboard statistics count CARs.
     */
    public function test_dashboard_counts_cars(): void
    {
        $department = Department::factory()->create();

        Car::create([
            'car_number' => 'C25001',
            'subject' => 'Test CAR 1',
            'ncr_description' => 'Test',
            'status' => 'issued',
            'priority' => 'high',
            'source_type' => 'internal_audit',
            'from_department_id' => $department->id,
            'to_department_id' => $department->id,
            'issued_date' => now(),
            'issued_by' => $this->user->id,
        ]);
        Car::create([
            'car_number' => 'C25002',
            'subject' => 'Test CAR 2',
            'ncr_description' => 'Test',
            'status' => 'closed',
            'priority' => 'medium',
            'source_type' => 'internal_audit',
            'from_department_id' => $department->id,
            'to_department_id' => $department->id,
            'issued_date' => now(),
            'issued_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard-analytics'));

        $stats = $response->viewData('stats');

        $this->assertEquals(2, $stats['total_cars']);
        $this->assertEquals(1, $stats['open_cars']);
        $this->assertEquals(1, $stats['closed_cars']);
    }

    /**
     * Test dashboard has recent activities.
     */
    public function test_dashboard_has_recent_activities(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard-analytics'));

        $response->assertStatus(200);
        $response->assertViewHas('recentActivities');
    }

    /**
     * Test dashboard has chart data.
     */
    public function test_dashboard_has_chart_data(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard-analytics'));

        $response->assertStatus(200);
        $response->assertViewHas('auditTrends');
        $response->assertViewHas('carStatusDistribution');
        $response->assertViewHas('complaintPriorityDistribution');
        $response->assertViewHas('documentStatusDistribution');
    }

    /**
     * Test dashboard shows expiring certificates.
     */
    public function test_dashboard_shows_expiring_certificates(): void
    {
        Certificate::create([
            'certificate_number' => 'CERT-2025-001',
            'standard' => 'ISO 9001:2015',
            'certification_body' => 'BSI',
            'issue_date' => now()->subYear(),
            'expiry_date' => now()->addMonths(2),
            'status' => 'valid',
            'scope_of_certification' => 'Manufacturing',
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard-analytics'));

        $response->assertStatus(200);
        $response->assertViewHas('expiringCertificates');

        $expiringCerts = $response->viewData('expiringCertificates');
        $this->assertCount(1, $expiringCerts);
    }

    /**
     * Test dashboard shows upcoming external audits.
     */
    public function test_dashboard_shows_upcoming_external_audits(): void
    {
        ExternalAudit::create([
            'audit_number' => ExternalAudit::generateAuditNumber(),
            'audit_type' => 'surveillance',
            'certification_body' => 'BSI',
            'standard' => 'ISO 9001:2015',
            'lead_auditor_name' => 'John Doe',
            'scheduled_start_date' => now()->addMonth(),
            'scheduled_end_date' => now()->addMonth()->addDays(2),
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard-analytics'));

        $response->assertStatus(200);
        $response->assertViewHas('upcomingExternalAudits');

        $upcomingAudits = $response->viewData('upcomingExternalAudits');
        $this->assertCount(1, $upcomingAudits);
    }

    /**
     * Test dashboard shows documents needing review.
     */
    public function test_dashboard_shows_documents_needing_review(): void
    {
        Document::create([
            'document_number' => Document::generateDocumentNumber('procedure'),
            'title' => 'Test Document',
            'category' => 'procedure',
            'status' => 'effective',
            'next_review_date' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard-analytics'));

        $response->assertStatus(200);
        $response->assertViewHas('documentsNeedingReview');

        $docsNeedingReview = $response->viewData('documentsNeedingReview');
        $this->assertCount(1, $docsNeedingReview);
    }

    /**
     * Test dashboard shows department performance.
     */
    public function test_dashboard_shows_department_performance(): void
    {
        Department::factory()->create();

        $response = $this->actingAs($this->user)->get(route('dashboard-analytics'));

        $response->assertStatus(200);
        $response->assertViewHas('departmentPerformance');
    }
}
