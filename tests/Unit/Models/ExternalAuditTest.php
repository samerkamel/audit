<?php

namespace Tests\Unit\Models;

use App\Models\ExternalAudit;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalAuditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a valid ExternalAudit with all required fields.
     */
    private function createValidAudit(array $attributes = []): ExternalAudit
    {
        $defaults = [
            'audit_number' => $attributes['audit_number'] ?? ExternalAudit::generateAuditNumber(),
            'audit_type' => 'surveillance',
            'certification_body' => 'BSI',
            'standard' => 'ISO 9001:2015',
            'lead_auditor_name' => 'John Doe',
            'scheduled_start_date' => now()->addDays(7),
            'scheduled_end_date' => now()->addDays(9),
            'status' => 'scheduled',
            'result' => 'pending',
        ];

        return ExternalAudit::create(array_merge($defaults, $attributes));
    }

    /**
     * Create a valid Certificate with all required fields.
     */
    private function createValidCertificate(array $attributes = []): Certificate
    {
        $defaults = [
            'certificate_number' => 'CERT-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'standard' => 'ISO 9001:2015',
            'certification_body' => 'BSI',
            'issue_date' => now(),
            'expiry_date' => now()->addYears(3),
            'status' => 'valid',
            'scope_of_certification' => 'Manufacturing and Quality Control',
        ];

        return Certificate::create(array_merge($defaults, $attributes));
    }

    /**
     * Test audit number generation produces correct format (EXT-YY-####)
     */
    public function test_generate_audit_number_returns_correct_format(): void
    {
        $auditNumber = ExternalAudit::generateAuditNumber();

        $this->assertMatchesRegularExpression('/^EXT-\d{2}-\d{4}$/', $auditNumber);
        $this->assertStringStartsWith('EXT-' . date('y') . '-', $auditNumber);
    }

    /**
     * Test audit number generation increments correctly
     */
    public function test_generate_audit_number_increments_sequentially(): void
    {
        $audit1 = $this->createValidAudit();
        $auditNumber2 = ExternalAudit::generateAuditNumber();

        $num1 = (int) substr($audit1->audit_number, -4);
        $num2 = (int) substr($auditNumber2, -4);

        $this->assertEquals($num1 + 1, $num2);
    }

    /**
     * Test audit number generation handles soft-deleted records
     */
    public function test_generate_audit_number_includes_soft_deleted(): void
    {
        $audit = $this->createValidAudit();
        $deletedNumber = $audit->audit_number;
        $audit->delete();

        $newNumber = ExternalAudit::generateAuditNumber();

        $this->assertNotEquals($deletedNumber, $newNumber);

        $deletedNum = (int) substr($deletedNumber, -4);
        $newNum = (int) substr($newNumber, -4);
        $this->assertGreaterThan($deletedNum, $newNum);
    }

    /**
     * Test status color accessor returns correct values
     */
    public function test_status_color_attribute_returns_correct_colors(): void
    {
        $statusColors = [
            'scheduled' => 'info',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'secondary',
        ];

        foreach ($statusColors as $status => $expectedColor) {
            $audit = new ExternalAudit(['status' => $status]);
            $this->assertEquals($expectedColor, $audit->status_color, "Failed for status: {$status}");
        }
    }

    /**
     * Test result color accessor returns correct values
     */
    public function test_result_color_attribute_returns_correct_colors(): void
    {
        $resultColors = [
            'passed' => 'success',
            'conditional' => 'warning',
            'failed' => 'danger',
            'pending' => 'info',
        ];

        foreach ($resultColors as $result => $expectedColor) {
            $audit = new ExternalAudit(['result' => $result]);
            $this->assertEquals($expectedColor, $audit->result_color, "Failed for result: {$result}");
        }
    }

    /**
     * Test audit type label accessor
     */
    public function test_audit_type_label_attribute(): void
    {
        $auditTypeLabels = [
            'initial_certification' => 'Initial Certification',
            'surveillance' => 'Surveillance Audit',
            'recertification' => 'Recertification Audit',
            'special' => 'Special Audit',
            'follow_up' => 'Follow-up Audit',
        ];

        foreach ($auditTypeLabels as $type => $expectedLabel) {
            $audit = new ExternalAudit(['audit_type' => $type]);
            $this->assertEquals($expectedLabel, $audit->audit_type_label, "Failed for type: {$type}");
        }
    }

    /**
     * Test total findings calculation
     */
    public function test_total_findings_attribute(): void
    {
        $audit = new ExternalAudit([
            'major_ncrs_count' => 2,
            'minor_ncrs_count' => 5,
            'observations_count' => 3,
        ]);

        $this->assertEquals(10, $audit->total_findings);
    }

    /**
     * Test isOverdue method with scheduled audit past end date
     */
    public function test_is_overdue_returns_true_when_past_end_date(): void
    {
        $audit = new ExternalAudit([
            'status' => 'scheduled',
            'scheduled_end_date' => now()->subDays(1),
        ]);

        $this->assertTrue($audit->isOverdue());
    }

    /**
     * Test isOverdue returns false for completed audit
     */
    public function test_is_overdue_returns_false_when_completed(): void
    {
        $audit = new ExternalAudit([
            'status' => 'completed',
            'scheduled_end_date' => now()->subDays(10),
        ]);

        $this->assertFalse($audit->isOverdue());
    }

    /**
     * Test isUpcoming method
     */
    public function test_is_upcoming_returns_true_when_within_30_days(): void
    {
        $audit = new ExternalAudit([
            'status' => 'scheduled',
            'scheduled_start_date' => now()->addDays(15),
        ]);

        $this->assertTrue($audit->isUpcoming());
    }

    /**
     * Test isUpcoming returns false when not scheduled
     */
    public function test_is_upcoming_returns_false_when_not_scheduled(): void
    {
        $audit = new ExternalAudit([
            'status' => 'in_progress',
            'scheduled_start_date' => now()->addDays(15),
        ]);

        $this->assertFalse($audit->isUpcoming());
    }

    /**
     * Test canStart method
     */
    public function test_can_start_returns_true_when_scheduled(): void
    {
        $audit = new ExternalAudit(['status' => 'scheduled']);
        $this->assertTrue($audit->canStart());
    }

    /**
     * Test canStart returns false when not scheduled
     */
    public function test_can_start_returns_false_when_not_scheduled(): void
    {
        $audit = new ExternalAudit(['status' => 'in_progress']);
        $this->assertFalse($audit->canStart());
    }

    /**
     * Test canComplete method
     */
    public function test_can_complete_returns_true_when_in_progress(): void
    {
        $audit = new ExternalAudit(['status' => 'in_progress']);
        $this->assertTrue($audit->canComplete());
    }

    /**
     * Test canComplete returns false when not in progress
     */
    public function test_can_complete_returns_false_when_not_in_progress(): void
    {
        $audit = new ExternalAudit(['status' => 'scheduled']);
        $this->assertFalse($audit->canComplete());
    }

    /**
     * Test canGenerateCertificate method
     */
    public function test_can_generate_certificate_when_passed_without_existing_certificate(): void
    {
        $audit = $this->createValidAudit([
            'scheduled_start_date' => now()->subDays(7),
            'scheduled_end_date' => now()->subDays(5),
            'status' => 'completed',
            'result' => 'passed',
        ]);

        $this->assertTrue($audit->canGenerateCertificate());
    }

    /**
     * Test canGenerateCertificate returns false when certificate exists
     */
    public function test_can_generate_certificate_false_when_certificate_exists(): void
    {
        $audit = $this->createValidAudit([
            'scheduled_start_date' => now()->subDays(7),
            'scheduled_end_date' => now()->subDays(5),
            'status' => 'completed',
            'result' => 'passed',
        ]);

        $this->createValidCertificate(['issued_for_audit_id' => $audit->id]);

        $this->assertFalse($audit->canGenerateCertificate());
    }

    /**
     * Test duration in days calculation with actual dates
     */
    public function test_duration_in_days_with_actual_dates(): void
    {
        $audit = new ExternalAudit([
            'scheduled_start_date' => now(),
            'scheduled_end_date' => now()->addDays(5),
            'actual_start_date' => now()->subDays(2),
            'actual_end_date' => now(),
        ]);

        $this->assertEquals(3, $audit->duration_in_days);
    }

    /**
     * Test duration in days calculation with scheduled dates
     */
    public function test_duration_in_days_with_scheduled_dates(): void
    {
        $audit = new ExternalAudit([
            'scheduled_start_date' => now(),
            'scheduled_end_date' => now()->addDays(4),
        ]);

        $this->assertEquals(5, $audit->duration_in_days);
    }

    /**
     * Test coordinator relationship
     */
    public function test_coordinator_relationship(): void
    {
        $user = User::factory()->create();
        $audit = $this->createValidAudit(['coordinator_id' => $user->id]);

        $this->assertInstanceOf(User::class, $audit->coordinator);
        $this->assertEquals($user->id, $audit->coordinator->id);
    }

    /**
     * Test certificate relationship
     */
    public function test_certificate_relationship(): void
    {
        $audit = $this->createValidAudit([
            'scheduled_start_date' => now()->subDays(7),
            'scheduled_end_date' => now()->subDays(5),
            'status' => 'completed',
            'result' => 'passed',
        ]);

        $certificate = $this->createValidCertificate(['issued_for_audit_id' => $audit->id]);

        $this->assertInstanceOf(Certificate::class, $audit->certificate);
        $this->assertEquals($certificate->id, $audit->certificate->id);
    }

    /**
     * Test array casts for JSON fields
     */
    public function test_json_fields_are_cast_to_arrays(): void
    {
        $audit = $this->createValidAudit([
            'audited_departments' => ['Production', 'Quality'],
            'audited_processes' => ['Manufacturing', 'Inspection'],
            'attachments' => ['file1.pdf', 'file2.pdf'],
        ]);

        $audit = $audit->fresh();

        $this->assertIsArray($audit->audited_departments);
        $this->assertIsArray($audit->audited_processes);
        $this->assertIsArray($audit->attachments);
        $this->assertCount(2, $audit->audited_departments);
    }

    /**
     * Test soft deletes
     */
    public function test_uses_soft_deletes(): void
    {
        $audit = $this->createValidAudit();
        $auditId = $audit->id;
        $audit->delete();

        $this->assertNull(ExternalAudit::find($auditId));
        $this->assertNotNull(ExternalAudit::withTrashed()->find($auditId));
    }

    /**
     * Test unknown status returns default color
     */
    public function test_unknown_status_returns_secondary_color(): void
    {
        $audit = new ExternalAudit(['status' => 'unknown']);
        $this->assertEquals('secondary', $audit->status_color);
    }

    /**
     * Test unknown result returns default color
     */
    public function test_unknown_result_returns_secondary_color(): void
    {
        $audit = new ExternalAudit(['result' => 'unknown']);
        $this->assertEquals('secondary', $audit->result_color);
    }
}
